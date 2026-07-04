<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class DistanceCalculationService
{
    protected GoogleMapsService $googleMapsService;
    protected BarikoiService $barikoiService;

    public function __construct(GoogleMapsService $googleMapsService, BarikoiService $barikoiService)
    {
        $this->googleMapsService = $googleMapsService;
        $this->barikoiService = $barikoiService;
    }

    /**
     * Get the active map provider from admin settings
     */
    protected function getActiveProvider(): string
    {
        $setting = \App\Models\AdminSettings::query()
            ->where('slug', 'map_provider')
            ->first();

        $provider = $setting?->value ?? 'google';

        \Log::info('[DistanceCalculationService] Active provider', [
            'provider' => $provider,
        ]);

        // Validate provider value
        if (!in_array($provider, ['google', 'barikoi'])) {
            Log::warning('Invalid map provider configured, defaulting to Google', ['provider' => $provider]);
            return 'google';
        }

        return $provider;
    }

    /**
     * Calculate distance between two addresses using the active provider
     */
    public function calculateDistance(string $origin, string $destination): ?array
    {
        $provider = $this->getActiveProvider();

        Log::info('[DistanceCalculationService] Calculating distance', [
            'provider' => $provider,
            'origin' => $origin,
            'destination' => $destination,
        ]);

        return match ($provider) {
            'barikoi' => $this->barikoiService->calculateDistance($origin, $destination),
            default => $this->googleMapsService->calculateDistance($origin, $destination),
        };
    }

    /**
     * Calculate distance with caching using the active provider
     */
    public function calculateDistanceWithCache(string $origin, string $destination, int $cacheTtl = 86400): ?array
    {
        $provider = $this->getActiveProvider();

        Log::info('[DistanceCalculationService] Calculating distance with cache', [
            'provider' => $provider,
            'origin' => $origin,
            'destination' => $destination,
            'cache_ttl' => $cacheTtl,
        ]);

        return match ($provider) {
            'barikoi' => $this->barikoiService->calculateDistanceWithCache($origin, $destination, null, $cacheTtl),
            default => $this->googleMapsService->calculateDistanceWithCache($origin, $destination, null, $cacheTtl),
        };
    }

    /**
     * Validate if an address string can be geocoded
     */
    public function validateAddress(string $address): bool
    {
        $provider = $this->getActiveProvider();

        return match ($provider) {
            'barikoi' => $this->barikoiService->validateAddress($address),
            default => $this->googleMapsService->validateAddress($address),
        };
    }

    /**
     * Format address for the active provider
     */
    public function formatAddress(string $address): string
    {
        $provider = $this->getActiveProvider();

        return match ($provider) {
            'barikoi' => $this->barikoiService->formatAddress($address),
            default => $this->googleMapsService->formatAddress($address),
        };
    }

    /**
     * Get the active provider name (for logging/debugging)
     */
    public function getProviderName(): string
    {
        return $this->getActiveProvider();
    }
}
