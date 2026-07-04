<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleMapsService
{
    protected string $baseUrl = 'https://maps.googleapis.com/maps/api/distancematrix/json';

    /**
     * Calculate distance between two addresses using Google Maps Distance Matrix API
     */
    public function calculateDistance(string $origin, string $destination, ?string $apiKey = null): ?array
    {
        if (!$apiKey) {
            $apiKey = $this->getApiKey();
        }

        \Log::info('[GoogleMapsService] calculateDistance called', [
            'origin' => $origin,
            'destination' => $destination,
            'has_api_key' => !empty($apiKey),
        ]);

        if (!$apiKey) {
            Log::warning('Google Maps API key not configured');
            return null;
        }

        try {
            $response = Http::get($this->baseUrl, [
                'origins' => $origin,
                'destinations' => $destination,
                'key' => $apiKey,
                'units' => 'metric', // Return distance in kilometers
            ]);

            \Log::info('[GoogleMapsService] API response status', [
                'status' => $response->status(),
                'successful' => $response->successful(),
            ]);

            if (!$response->successful()) {
                Log::error('Google Maps API request failed', ['status' => $response->status()]);
                return null;
            }

            $data = $response->json();

            \Log::info('[GoogleMapsService] API response data', [
                'status' => $data['status'] ?? 'unknown',
                'has_rows' => isset($data['rows']) && !empty($data['rows']),
            ]);

            if ($data['status'] !== 'OK') {
                Log::error('Google Maps API error', ['status' => $data['status'] ?? 'unknown', 'error_message' => $data['error_message'] ?? '']);
                return null;
            }

            if (empty($data['rows'][0]['elements'][0]['distance'])) {
                Log::warning('No distance data found in Google Maps response');
                return null;
            }

            $element = $data['rows'][0]['elements'][0];

            if ($element['status'] !== 'OK') {
                Log::warning('Google Maps distance calculation failed', ['status' => $element['status']]);
                return null;
            }

            $result = [
                'distance_km' => $element['distance']['value'] / 1000, // Convert meters to kilometers
                'distance_text' => $element['distance']['text'],
                'duration_seconds' => $element['duration']['value'],
                'duration_text' => $element['duration']['text'],
            ];

            \Log::info('[GoogleMapsService] Distance calculated successfully', $result);

            return $result;
        } catch (\Throwable $e) {
            Log::error('Google Maps API exception', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return null;
        }
    }

    /**
     * Calculate distance with caching to avoid excessive API calls
     */
    public function calculateDistanceWithCache(string $origin, string $destination, ?string $apiKey = null, int $cacheTtl = 86400): ?array
    {
        $cacheKey = 'google_maps_distance_' . md5($origin . '|' . $destination);

        return Cache::remember($cacheKey, $cacheTtl, function () use ($origin, $destination, $apiKey) {
            return $this->calculateDistance($origin, $destination, $apiKey);
        });
    }

    /**
     * Get Google Maps API key from admin settings
     */
    protected function getApiKey(): ?string
    {
        // Get from admin settings (platform-level configuration)
        $setting = \App\Models\AdminSettings::query()
            ->where('slug', 'google_maps_api_key')
            ->first();

        $apiKey = $setting?->value ?? null;

        \Log::info('[GoogleMapsService] getApiKey called', [
            'setting_found' => $setting !== null,
            'has_api_key' => !empty($apiKey),
            'key_length' => $apiKey ? strlen($apiKey) : 0,
        ]);

        if (!$apiKey) {
            Log::warning('Google Maps API key not found in admin settings. Please configure it in the admin panel.');
        }

        return $apiKey;
    }

    /**
     * Validate if an address string can be geocoded
     */
    public function validateAddress(string $address): bool
    {
        // Basic validation - address should not be empty
        return !empty(trim($address));
    }

    /**
     * Format address for Google Maps API
     */
    public function formatAddress(string $address): string
    {
        // Remove extra whitespace and special characters that might cause issues
        return preg_replace('/\s+/', ' ', trim($address));
    }
}
