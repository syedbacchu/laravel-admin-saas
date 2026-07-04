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
 * Staff Feature Caching Tests
 * Test smart caching implementation for staff-specific feature access
 */
class StaffFeatureCachingTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $owner;
    protected User $staff1;
    protected User $staff2;
    protected Subscription $subscription;

    /**
     * Setup test environment
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Create test tenant
        $this->tenant = Tenant::factory()->create([
            'company_name' => 'Test Transport Co',
            'company_username' => 'testtransport',
            'status' => 'active',
        ]);

        // Create owner user
        $this->owner = User::factory()->create([
            'email' => 'owner@testtransport.com',
        ]);

        $this->tenant->owner_user_id = $this->owner->id;
        $this->tenant->save();

        // Create staff users
        $this->staff1 = User::factory()->create([
            'email' => 'staff1@testtransport.com',
            'user_type' => 'staff',
            'parent_id' => $this->owner->id,
        ]);

        $this->staff2 = User::factory()->create([
            'email' => 'staff2@testtransport.com',
            'user_type' => 'staff',
            'parent_id' => $this->owner->id,
        ]);

        // Create test plan and subscription
        $plan = Plan::factory()->create([
            'name' => 'Test Plan',
            'slug' => 'test-plan',
        ]);

        // Create features
        $feature1 = Feature::factory()->create([
            'key' => 'vehicle.management',
            'name' => 'Vehicle Management',
            'value_type' => 'boolean',
        ]);

        $feature2 = Feature::factory()->create([
            'key' => 'trip.monitoring',
            'name' => 'Trip Monitoring',
            'value_type' => 'boolean',
        ]);

        $feature3 = Feature::factory()->create([
            'key' => 'driver.management',
            'name' => 'Driver Management',
            'value_type' => 'boolean',
        ]);

        // Assign features to plan
        PlanFeatureValue::factory()->create([
            'plan_id' => $plan->id,
            'feature_id' => $feature1->id,
            'value_bool' => true,
        ]);

        PlanFeatureValue::factory()->create([
            'plan_id' => $plan->id,
            'feature_id' => $feature2->id,
            'value_bool' => true,
        ]);

        PlanFeatureValue::factory()->create([
            'plan_id' => $plan->id,
            'feature_id' => $feature3->id,
            'value_bool' => true,
        ]);

        // Create subscription
        $this->subscription = Subscription::factory()->create([
            'tenant_id' => $this->tenant->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => now()->addMonths(1),
        ]);
    }

    /**
     * Test staff-specific cache key generation
     */
    public function test_staff_specific_cache_key_generation(): void
    {
        $resolver = app(TenantFeatureResolverService::class);

        // Use reflection to test the protected method
        $reflection = new \ReflectionClass($resolver);
        $method = $reflection->getMethod('staffCacheKey');
        $method->setAccessible(true);

        $cacheKey1 = $method->invoke($resolver, $this->tenant->id, $this->staff1->id);
        $cacheKey2 = $method->invoke($resolver, $this->tenant->id, $this->staff2->id);

        // Verify cache keys are staff-specific
        $this->assertStringContainsString('staff_feature_map_', $cacheKey1);
        $this->assertStringContainsString((string)$this->tenant->id, $cacheKey1);
        $this->assertStringContainsString((string)$this->staff1->id, $cacheKey1);

        // Verify different staff have different cache keys
        $this->assertNotEquals($cacheKey1, $cacheKey2);
    }

    /**
     * Test that staff features are cached independently
     */
    public function test_staff_features_cached_independently(): void
    {
        // Assign different features to each staff
        StaffFeatureAssignment::factory()->create([
            'staff_id' => $this->staff1->id,
            'feature_key' => 'vehicle.management',
            'is_accessible' => true,
        ]);

        StaffFeatureAssignment::factory()->create([
            'staff_id' => $this->staff2->id,
            'feature_key' => 'trip.monitoring',
            'is_accessible' => true,
        ]);

        $resolver = app(TenantFeatureResolverService::class);

        // Get features for staff1
        $features1 = $resolver->getFeatureMapForStaff($this->tenant->id, $this->staff1->id);

        // Get features for staff2
        $features2 = $resolver->getFeatureMapForStaff($this->tenant->id, $this->staff2->id);

        // Verify each staff has their own features
        $this->assertArrayHasKey('vehicle.management', $features1);
        $this->assertArrayNotHasKey('trip.monitoring', $features1);

        $this->assertArrayHasKey('trip.monitoring', $features2);
        $this->assertArrayNotHasKey('vehicle.management', $features2);

        // Verify cache is working by checking cache keys
        $reflection = new \ReflectionClass($resolver);
        $method = $reflection->getMethod('staffCacheKey');
        $method->setAccessible(true);

        $cacheKey1 = $method->invoke($resolver, $this->tenant->id, $this->staff1->id);
        $cacheKey2 = $method->invoke($resolver, $this->tenant->id, $this->staff2->id);

        $this->assertTrue(Cache::has($cacheKey1));
        $this->assertTrue(Cache::has($cacheKey2));
    }

    /**
     * Test that clearing staff cache doesn't affect other staff
     */
    public function test_clearing_staff_cache_doesnt_affect_other_staff(): void
    {
        // Assign features to both staff
        StaffFeatureAssignment::factory()->create([
            'staff_id' => $this->staff1->id,
            'feature_key' => 'vehicle.management',
            'is_accessible' => true,
        ]);

        StaffFeatureAssignment::factory()->create([
            'staff_id' => $this->staff2->id,
            'feature_key' => 'trip.monitoring',
            'is_accessible' => true,
        ]);

        $resolver = app(TenantFeatureResolverService::class);

        // Load features for both staff (this will cache them)
        $features1_initial = $resolver->getFeatureMapForStaff($this->tenant->id, $this->staff1->id);
        $features2_initial = $resolver->getFeatureMapForStaff($this->tenant->id, $this->staff2->id);

        // Clear staff1's cache
        $resolver->clearStaffFeatureCache($this->tenant->id, $this->staff1->id);

        // Update staff1's features
        StaffFeatureAssignment::where('staff_id', $this->staff1->id)->delete();
        StaffFeatureAssignment::factory()->create([
            'staff_id' => $this->staff1->id,
            'feature_key' => 'driver.management',
            'is_accessible' => true,
        ]);

        // Get fresh features for staff1 (should have new features)
        $features1_new = $resolver->getFeatureMapForStaff($this->tenant->id, $this->staff1->id);

        // Get features for staff2 (should still be cached and unchanged)
        $features2_still_cached = $resolver->getFeatureMapForStaff($this->tenant->id, $this->staff2->id);

        // Verify staff1's features changed
        $this->assertArrayHasKey('driver.management', $features1_new);
        $this->assertArrayNotHasKey('vehicle.management', $features1_new);

        // Verify staff2's features are still the same (from cache)
        $this->assertEquals($features2_initial, $features2_still_cached);
        $this->assertArrayHasKey('trip.monitoring', $features2_still_cached);
    }

    /**
     * Test cache performance with multiple staff
     */
    public function test_cache_performance_with_multiple_staff(): void
    {
        // Create multiple staff users
        $staffCount = 10;
        $staffUsers = [];
        for ($i = 1; $i <= $staffCount; $i++) {
            $staffUsers[] = User::factory()->create([
                'email' => "staff{$i}@testtransport.com",
                'user_type' => 'staff',
                'parent_id' => $this->owner->id,
            ]);
        }

        // Assign features to all staff
        foreach ($staffUsers as $staff) {
            StaffFeatureAssignment::factory()->create([
                'staff_id' => $staff->id,
                'feature_key' => 'vehicle.management',
                'is_accessible' => true,
            ]);
        }

        $resolver = app(TenantFeatureResolverService::class);

        // Measure time to load features for all staff (first time - cache miss)
        $startTime = microtime(true);
        foreach ($staffUsers as $staff) {
            $resolver->getFeatureMapForStaff($this->tenant->id, $staff->id);
        }
        $firstLoadTime = microtime(true) - $startTime;

        // Measure time to load features for all staff (second time - cache hit)
        $startTime = microtime(true);
        foreach ($staffUsers as $staff) {
            $resolver->getFeatureMapForStaff($this->tenant->id, $staff->id);
        }
        $secondLoadTime = microtime(true) - $startTime;

        // Cache hits should be significantly faster
        $this->assertLessThan($firstLoadTime, $secondLoadTime);

        // Verify all staff have independent cache keys
        $reflection = new \ReflectionClass($resolver);
        $method = $reflection->getMethod('staffCacheKey');
        $method->setAccessible(true);

        $cacheKeys = [];
        foreach ($staffUsers as $staff) {
            $cacheKeys[] = $method->invoke($resolver, $this->tenant->id, $staff->id);
        }

        // All cache keys should be unique
        $this->assertEquals(count($cacheKeys), count(array_unique($cacheKeys)));
    }

    /**
     * Test that staff cache is cleared when features are updated
     */
    public function test_staff_cache_cleared_on_feature_update(): void
    {
        // Assign initial features to staff
        StaffFeatureAssignment::factory()->create([
            'staff_id' => $this->staff1->id,
            'feature_key' => 'vehicle.management',
            'is_accessible' => true,
        ]);

        $resolver = app(TenantFeatureResolverService::class);

        // Load and cache staff features
        $features_initial = $resolver->getFeatureMapForStaff($this->tenant->id, $this->staff1->id);
        $this->assertArrayHasKey('vehicle.management', $features_initial);

        // Clear cache
        $resolver->clearStaffFeatureCache($this->tenant->id, $this->staff1->id);

        // Update features in database
        StaffFeatureAssignment::where('staff_id', $this->staff1->id)->delete();
        StaffFeatureAssignment::factory()->create([
            'staff_id' => $this->staff1->id,
            'feature_key' => 'trip.monitoring',
            'is_accessible' => true,
        ]);

        // Load fresh features
        $features_updated = $resolver->getFeatureMapForStaff($this->tenant->id, $this->staff1->id);

        // Verify cache was cleared and new features loaded
        $this->assertArrayNotHasKey('vehicle.management', $features_updated);
        $this->assertArrayHasKey('trip.monitoring', $features_updated);
    }
}
