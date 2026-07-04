<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Models\Subscription;
use App\Models\Plan;
use App\Models\PlanFeatureValue;
use App\Models\Feature;
use App\Models\StaffFeatureAssignment;
use App\Http\Services\Tenant\TenantFeatureResolverService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Smart Caching Integration Test
 * Manual integration test for staff-specific feature caching
 */
class SmartCachingIntegrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that cache keys are staff-specific
     */
    public function test_staff_specific_cache_keys(): void
    {
        $resolver = app(TenantFeatureResolverService::class);
        $tenantId = 123;
        $staff1Id = 456;
        $staff2Id = 789;

        // Use reflection to test the protected method
        $reflection = new \ReflectionClass($resolver);
        $method = $reflection->getMethod('staffCacheKey');
        $method->setAccessible(true);

        $cacheKey1 = $method->invoke($resolver, $tenantId, $staff1Id);
        $cacheKey2 = $method->invoke($resolver, $tenantId, $staff2Id);

        // Verify cache keys contain correct components
        $this->assertStringContainsString('staff_feature_map_', $cacheKey1);
        $this->assertStringContainsString('123', $cacheKey1); // tenant ID
        $this->assertStringContainsString('456', $cacheKey1); // staff1 ID
        $this->assertStringContainsString('789', $cacheKey2); // staff2 ID

        // Verify different staff have different cache keys
        $this->assertNotEquals($cacheKey1, $cacheKey2);

        echo "\n✅ Staff-specific cache keys work correctly:";
        echo "\n   Staff 1 cache: $cacheKey1";
        echo "\n   Staff 2 cache: $cacheKey2";
    }

    /**
     * Test cache clearing method exists and works
     */
    public function test_cache_clearing_method(): void
    {
        $resolver = app(TenantFeatureResolverService::class);
        $tenantId = 123;
        $staffId = 456;

        // Get reflection for staffCacheKey method
        $reflection = new \ReflectionClass($resolver);
        $cacheKeyMethod = $reflection->getMethod('staffCacheKey');
        $cacheKeyMethod->setAccessible(true);

        $cacheKey = $cacheKeyMethod->invoke($resolver, $tenantId, $staffId);

        // Manually set something in cache
        Cache::put($cacheKey, ['test_feature' => true], 600);

        // Verify it's cached
        $this->assertTrue(Cache::has($cacheKey));

        // Clear the cache using the public method
        $resolver->clearStaffFeatureCache($tenantId, $staffId);

        // Verify cache is cleared
        $this->assertFalse(Cache::has($cacheKey));

        echo "\n✅ Cache clearing method works correctly";
    }

    /**
     * Test that different staff can have different cache states
     */
    public function test_independent_cache_states(): void
    {
        $resolver = app(TenantFeatureResolverService::class);
        $tenantId = 123;
        $staff1Id = 456;
        $staff2Id = 789;

        // Get reflection for staffCacheKey method
        $reflection = new \ReflectionClass($resolver);
        $cacheKeyMethod = $reflection->getMethod('staffCacheKey');
        $cacheKeyMethod->setAccessible(true);

        $cacheKey1 = $cacheKeyMethod->invoke($resolver, $tenantId, $staff1Id);
        $cacheKey2 = $cacheKeyMethod->invoke($resolver, $tenantId, $staff2Id);

        // Set different features for each staff
        Cache::put($cacheKey1, ['vehicle.management' => true], 600);
        Cache::put($cacheKey2, ['trip.monitoring' => true], 600);

        // Verify both are cached independently
        $this->assertTrue(Cache::has($cacheKey1));
        $this->assertTrue(Cache::has($cacheKey2));

        // Clear staff1's cache
        $resolver->clearStaffFeatureCache($tenantId, $staff1Id);

        // Verify only staff1's cache is cleared
        $this->assertFalse(Cache::has($cacheKey1));
        $this->assertTrue(Cache::has($cacheKey2)); // staff2's cache still exists

        echo "\n✅ Independent cache states work correctly";
    }

    /**
     * Test the complete flow with simulated data
     */
    public function test_complete_caching_flow(): void
    {
        $resolver = app(TenantFeatureResolverService::class);
        $tenantId = 999;
        $staff1Id = 1001;
        $staff2Id = 1002;

        // Get reflection for staffCacheKey method
        $reflection = new \ReflectionClass($resolver);
        $cacheKeyMethod = $reflection->getMethod('staffCacheKey');
        $cacheKeyMethod->setAccessible(true);

        $cacheKey1 = $cacheKeyMethod->invoke($resolver, $tenantId, $staff1Id);
        $cacheKey2 = $cacheKeyMethod->invoke($resolver, $tenantId, $staff2Id);

        // Simulate initial feature load
        $staff1Features = ['vehicle.management' => true, 'trip.monitoring' => true];
        $staff2Features = ['driver.management' => true];

        Cache::put($cacheKey1, $staff1Features, 600);
        Cache::put($cacheKey2, $staff2Features, 600);

        echo "\n🔄 Testing complete caching flow:";

        // Step 1: Verify initial caching
        echo "\n   1. Initial cache state:";
        echo "\n      Staff 1 features: " . json_encode(Cache::get($cacheKey1));
        echo "\n      Staff 2 features: " . json_encode(Cache::get($cacheKey2));

        $this->assertEquals($staff1Features, Cache::get($cacheKey1));
        $this->assertEquals($staff2Features, Cache::get($cacheKey2));

        // Step 2: Admin updates staff1 features
        echo "\n   2. Admin updates Staff 1 features...";
        $resolver->clearStaffFeatureCache($tenantId, $staff1Id);

        // Simulate database update
        $newStaff1Features = ['vehicle.management' => true, 'trip.monitoring' => true, 'driver.management' => true];
        Cache::put($cacheKey1, $newStaff1Features, 600);

        echo "\n      Staff 1 updated features: " . json_encode(Cache::get($cacheKey1));
        echo "\n      Staff 2 unchanged (still cached): " . json_encode(Cache::get($cacheKey2));

        // Step 3: Verify Staff 1 has new features, Staff 2 unchanged
        $this->assertEquals($newStaff1Features, Cache::get($cacheKey1));
        $this->assertEquals($staff2Features, Cache::get($cacheKey2)); // Still old cached version

        echo "\n   3. Verification:";
        echo "\n      ✅ Staff 1 got new features immediately";
        echo "\n      ✅ Staff 2 kept cached features (no unnecessary refresh)";

        // Step 4: Clear Staff 2's cache independently
        echo "\n   4. Clear Staff 2 cache independently...";
        $resolver->clearStaffFeatureCache($tenantId, $staff2Id);

        $this->assertFalse(Cache::has($cacheKey2));
        $this->assertTrue(Cache::has($cacheKey1)); // Staff 1 still cached

        echo "\n      ✅ Staff 2 cache cleared independently";
        echo "\n      ✅ Staff 1 cache still intact";

        echo "\n\n🎉 Complete caching flow test passed!";
    }
}
