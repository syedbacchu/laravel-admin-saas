<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BarikoiService
{
    protected string $baseUrl = 'https://barikoi.xyz/v2/api';
    protected string $geocodeUrl = 'https://barikoi.xyz/v2/api/search';
    protected string $distanceUrl = 'https://barikoi.xyz/v1/api/distance/matrix';

    /**
     * Calculate distance between two addresses using Barikoi API
     * First geocodes addresses to coordinates, then calculates distance
     */
    public function calculateDistance(string $origin, string $destination, ?string $apiKey = null): ?array
    {
        if (!$apiKey) {
            $apiKey = $this->getApiKey();
        }

        \Log::info('[BarikoiService] calculateDistance called', [
            'origin' => $origin,
            'destination' => $destination,
            'has_api_key' => !empty($apiKey),
        ]);

        if (!$apiKey) {
            Log::warning('Barikoi API key not configured');
            return null;
        }

        try {
            // Step 1: Geocode origin address
            $originCoords = $this->geocodeAddress($origin, $apiKey);
            if (!$originCoords) {
                Log::warning('Failed to geocode origin address', ['address' => $origin]);
                return null;
            }

            // Step 2: Geocode destination address
            $destinationCoords = $this->geocodeAddress($destination, $apiKey);
            if (!$destinationCoords) {
                Log::warning('Failed to geocode destination address', ['address' => $destination]);
                return null;
            }

            \Log::info('[BarikoiService] Addresses geocoded successfully', [
                'origin_coords' => $originCoords,
                'destination_coords' => $destinationCoords,
            ]);

            // Step 3: Calculate distance between coordinates
            return $this->calculateDistanceByCoords(
                $originCoords['latitude'],
                $originCoords['longitude'],
                $destinationCoords['latitude'],
                $destinationCoords['longitude'],
                $apiKey
            );
        } catch (\Throwable $e) {
            Log::error('Barikoi API exception', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return null;
        }
    }

    /**
     * Calculate distance with caching to avoid excessive API calls
     */
    public function calculateDistanceWithCache(string $origin, string $destination, ?string $apiKey = null, int $cacheTtl = 86400): ?array
    {
        $cacheKey = 'barikoi_distance_' . md5($origin . '|' . $destination);

        return Cache::remember($cacheKey, $cacheTtl, function () use ($origin, $destination, $apiKey) {
            return $this->calculateDistance($origin, $destination, $apiKey);
        });
    }

    /**
     * Geocode an address to get coordinates using Barikoi Geocoding API
     */
    protected function geocodeAddress(string $address, string $apiKey): ?array
    {
        try {
            $geocodeUrl = 'https://barikoi.xyz/v2/api/search/autocomplete/geocode';

            \Log::info('[BarikoiService] Geocoding address', [
                'address' => $address,
                'url' => $geocodeUrl,
            ]);

            $response = Http::get($geocodeUrl, [
                'q' => $address,
                'api_key' => $apiKey,
            ]);

            \Log::info('[BarikoiService] Geocoding API response', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            if (!$response->successful()) {
                Log::error('Barikoi geocoding API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            $data = $response->json();

            if (!isset($data['status']) || $data['status'] !== 'ok') {
                Log::warning('Barikoi geocoding API error', [
                    'response' => $data,
                    'status_field' => $data['status'] ?? 'missing',
                ]);
                return null;
            }

            if (empty($data['places'][0])) {
                Log::warning('No results found for address', ['address' => $address, 'response' => $data]);
                return null;
            }

            $place = $data['places'][0];

            return [
                'latitude' => (float) $place['latitude'],
                'longitude' => (float) $place['longitude'],
                'address' => $place['address'] ?? $address,
            ];
        } catch (\Throwable $e) {
            Log::error('Barikoi geocoding exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Calculate distance between two coordinates using Barikoi Distance Matrix API
     */
    protected function calculateDistanceByCoords(float $originLat, float $originLng, float $destLat, float $destLng, string $apiKey): ?array
    {
        try {
            // Try distance matrix API first
            $distanceUrl = 'https://barikoi.xyz/v1/api/route/direction';

            \Log::info('[BarikoiService] Calculating distance between coordinates', [
                'origin' => "{$originLat},{$originLng}",
                'destination' => "{$destLat},{$destLng}",
                'url' => $distanceUrl,
            ]);

            $response = Http::get($distanceUrl, [
                'api_key' => $apiKey,
                'origin' => "{$originLng},{$originLat}", // Barikoi uses longitude,latitude format
                'destination' => "{$destLng},{$destLat}",
            ]);

            \Log::info('[BarikoiService] Distance API response', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            if (!$response->successful()) {
                Log::error('Barikoi distance API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            $data = $response->json();

            \Log::info('[BarikoiService] Distance API response data', ['data' => $data]);

            // Check if response contains distance data
            if (isset($data['routes'][0]['distance'])) {
                $distanceMeters = (float) $data['routes'][0]['distance'];
                $distanceKm = $distanceMeters / 1000;
                $durationSeconds = $data['routes'][0]['duration'] ?? null;

                \Log::info('[BarikoiService] Distance calculated successfully', [
                    'distance_km' => $distanceKm,
                ]);

                return [
                    'distance_km' => $distanceKm,
                    'distance_text' => round($distanceKm, 2) . ' km',
                    'duration_seconds' => $durationSeconds,
                    'duration_text' => $durationSeconds ? $this->formatDuration($durationSeconds) : null,
                ];
            }

            Log::warning('No distance data found in Barikoi response', ['response' => $data]);
            return null;
        } catch (\Throwable $e) {
            Log::error('Barikoi distance calculation exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Get Barikoi API key from admin settings
     */
    protected function getApiKey(): ?string
    {
        $setting = \App\Models\AdminSettings::query()
            ->where('slug', 'barikoi_api_key')
            ->first();

        $apiKey = $setting?->value ?? null;

        \Log::info('[BarikoiService] getApiKey called', [
            'setting_found' => $setting !== null,
            'has_api_key' => !empty($apiKey),
            'key_length' => $apiKey ? strlen($apiKey) : 0,
        ]);

        if (!$apiKey) {
            Log::warning('Barikoi API key not found in admin settings. Please configure it in the admin panel.');
        }

        return $apiKey;
    }

    /**
     * Format duration in seconds to human-readable text
     */
    protected function formatDuration(int $seconds): string
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);

        if ($hours > 0) {
            return "{$hours}h {$minutes}m";
        }

        return "{$minutes}m";
    }

    /**
     * Validate if an address string can be geocoded
     */
    public function validateAddress(string $address): bool
    {
        return !empty(trim($address));
    }

    /**
     * Format address for Barikoi API
     */
    public function formatAddress(string $address): string
    {
        return preg_replace('/\s+/', ' ', trim($address));
    }
}
