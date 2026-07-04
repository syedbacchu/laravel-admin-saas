<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Models\Subscription;
use App\Models\Plan;
use App\Models\PlanFeatureValue;
use App\Models\Feature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/**
 * Feature Access Control Tests
 * Comprehensive testing suite for feature-based access control
 */
class FeatureAccessTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected Tenant $tenant;
    protected User $tenantUser;
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

        // Create tenant user
        $this->tenantUser = User::factory()->create([
            'email' => 'admin@testtransport.com',
        ]);

        // Create test plan with features
        $plan = Plan::factory()->create([
            'name' => 'Test Premium Plan',
            'slug' => 'test-premium',
        ]);

        // Create subscription
        $this->subscription = Subscription::factory()->create([
            'tenant_id' => $this->tenant->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addMonth(),
        ]);
    }

    /**
     * Test basic feature access with active subscription
     */
    public function test_allows_access_when_feature_is_enabled(): void
    {
        // Enable trip monitoring feature for tenant's plan
        $tripFeature = Feature::where('key', 'trip.monitoring')->first();
        PlanFeatureValue::factory()->create([
            'plan_id' => $this->subscription->plan_id,
            'feature_id' => $tripFeature->id,
            'value_bool' => true,
        ]);

        // Act as tenant user and access trips endpoint
        $response = $this->actingAs($this->tenantUser)
            ->getJson("/api/tenant/{$this->tenant->company_username}/trips");

        // Should return 200 OK
        $response->assertStatus(200);
    }

    /**
     * Test feature access denial when feature is disabled
     */
    public function test_denies_access_when_feature_is_disabled(): void
    {
        // Disable fuel intelligence feature for tenant's plan
        $fuelFeature = Feature::where('key', 'fuel.intelligence')->first();
        PlanFeatureValue::factory()->create([
            'plan_id' => $this->subscription->plan_id,
            'feature_id' => $fuelFeature->id,
            'value_bool' => false,
        ]);

        // Act as tenant user and try to access fuel ledger
        $response = $this->actingAs($this->tenantUser)
            ->getJson("/api/tenant/{$this->tenant->company_username}/fuel-ledger");

        // Should return 403 Forbidden
        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'message' => 'Feature access denied for current package',
        ]);
    }

    /**
     * Test vehicle tier feature access
     */
    public function test_respects_vehicle_tier_limits(): void
    {
        // Enable 5-10 vehicle tier
        $vehicleFeature = Feature::where('key', 'vehicle.manage_5_10')->first();
        PlanFeatureValue::factory()->create([
            'plan_id' => $this->subscription->plan_id,
            'feature_id' => $vehicleFeature->id,
            'value_bool' => true,
        ]);

        // Access vehicles endpoint
        $response = $this->actingAs($this->tenantUser)
            ->getJson("/api/tenant/{$this->tenant->company_username}/vehicles");

        // Should allow access
        $response->assertStatus(200);
    }

    /**
     * Test multiple vehicle tier features (OR logic)
     */
    public function test_allows_access_with_any_vehicle_tier(): void
    {
        // Enable multiple vehicle tiers
        $vehicleFeatures = Feature::whereIn('key', [
            'vehicle.manage_1_5',
            'vehicle.manage_5_10',
        ])->get();

        foreach ($vehicleFeatures as $feature) {
            PlanFeatureValue::factory()->create([
                'plan_id' => $this->subscription->plan_id,
                'feature_id' => $feature->id,
                'value_bool' => true,
            ]);
        }

        // Access vehicles endpoint
        $response = $this->actingAs($this->tenantUser)
            ->getJson("/api/tenant/{$this->tenant->company_username}/vehicles");

        // Should allow access (any tier grants access)
        $response->assertStatus(200);
    }

    /**
     * Test feature access with inactive subscription
     */
    public function test_denies_access_with_expired_subscription(): void
    {
        // Expire subscription
        $this->subscription->update([
            'status' => 'expired',
            'ends_at' => now()->subDay(),
        ]);

        // Enable trip monitoring feature
        $tripFeature = Feature::where('key', 'trip.monitoring')->first();
        PlanFeatureValue::factory()->create([
            'plan_id' => $this->subscription->plan_id,
            'feature_id' => $tripFeature->id,
            'value_bool' => true,
        ]);

        // Try to access trips endpoint
        $response = $this->actingAs($this->tenantUser)
            ->getJson("/api/tenant/{$this->tenant->company_username}/trips");

        // Should return 402 Payment Required (subscription expired)
        $response->assertStatus(402);
    }

    /**
     * Test feature check endpoint
     */
    public function test_feature_check_endpoint_returns_correct_status(): void
    {
        // Enable feature
        $feature = Feature::where('key', 'trip.monitoring')->first();
        PlanFeatureValue::factory()->create([
            'plan_id' => $this->subscription->plan_id,
            'feature_id' => $feature->id,
            'value_bool' => true,
        ]);

        // Check feature access
        $response = $this->actingAs($this->tenantUser)
            ->getJson("/api/tenant/{$this->tenant->company_username}/feature-check/trip.monitoring");

        // Should return success
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'feature_key' => 'trip.monitoring',
                    'feature_value' => true,
                ],
            ]);
    }

    /**
     * Test subscription details include features
     */
    public function test_subscription_details_includes_feature_map(): void
    {
        // Enable features
        $tripFeature = Feature::where('key', 'trip.monitoring')->first();
        PlanFeatureValue::factory()->create([
            'plan_id' => $this->subscription->plan_id,
            'feature_id' => $tripFeature->id,
            'value_bool' => true,
        ]);

        // Get subscription details
        $response = $this->actingAs($this->tenantUser)
            ->getJson("/api/tenant/{$this->tenant->company_username}/account/subscription-details");

        // Should include features
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'features' => [
                        'trip.monitoring',
                    ],
                ],
            ]);
    }

    /**
     * Test numeric feature values (limits)
     */
    public function test_handles_numeric_feature_values(): void
    {
        // Create a numeric feature (staff user limit)
        $staffFeature = Feature::factory()->create([
            'key' => 'staff.multi_user_access',
            'value_type' => 'integer',
        ]);

        PlanFeatureValue::factory()->create([
            'plan_id' => $this->subscription->plan_id,
            'feature_id' => $staffFeature->id,
            'value_int' => 10,
        ]);

        // Get subscription details
        $response = $this->actingAs($this->tenantUser)
            ->getJson("/api/tenant/{$this->tenant->company_username}/account/subscription-details");

        // Should include numeric value
        $response->assertStatus(200)
            ->assertJsonPath('data.features.staff.multi_user_access', 10);
    }

    /**
     * Test feature cache invalidation after subscription change
     */
    public function test_invalidates_feature_cache_after_subscription_update(): void
    {
        // Enable feature
        $feature = Feature::where('key', 'trip.monitoring')->first();
        PlanFeatureValue::factory()->create([
            'plan_id' => $this->subscription->plan_id,
            'feature_id' => $feature->id,
            'value_bool' => true,
        ]);

        // First access
        $response1 = $this->actingAs($this->tenantUser)
            ->getJson("/api/tenant/{$this->tenant->company_username}/trips");
        $response1->assertStatus(200);

        // Disable feature
        PlanFeatureValue::where('plan_id', $this->subscription->plan_id)
            ->where('feature_id', $feature->id)
            ->update(['value_bool' => false]);

        // Clear cache manually for testing
        $this->artisan('cache:clear');

        // Second access - should be denied
        $response2 = $this->actingAs($this->tenantUser)
            ->getJson("/api/tenant/{$this->tenant->company_username}/trips");
        $response2->assertStatus(403);
    }

    /**
     * Test unauthorized access attempts
     */
    public function test_prevents_unauthorized_access(): void
    {
        $otherUser = User::factory()->create();

        // Try to access another tenant's data
        $response = $this->actingAs($otherUser)
            ->getJson("/api/tenant/{$this->tenant->company_username}/trips");

        // Should return 403 or 404
        $response->assertStatus($response->status() >= 400 && $response->status() <= 404);
    }

    /**
     * Test feature access with trial subscription
     */
    public function test_allows_feature_access_during_trial(): void
    {
        // Create trial subscription
        $trialSubscription = Subscription::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => 'trialing',
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(14),
        ]);

        // Enable all features for trial
        $features = Feature::whereIn('key', [
            'trip.monitoring',
            'fuel.management',
            'payroll.salary_commission',
        ])->get();

        foreach ($features as $feature) {
            PlanFeatureValue::factory()->create([
                'plan_id' => $trialSubscription->plan_id,
                'feature_id' => $feature->id,
                'value_bool' => true,
            ]);
        }

        // Access multiple features
        $response = $this->actingAs($this->tenantUser)
            ->getJson("/api/tenant/{$this->tenant->company_username}/trips");

        // Should allow access
        $response->assertStatus(200);
    }

    /**
     * Test multiple feature protection
     */
    public function test_respects_multiple_feature_requirements(): void
    {
        // This test checks if features that require multiple other features work
        // For example: Advanced reports might require both basic reports AND analytics

        // Enable basic reports but not advanced
        $basicReportsFeature = Feature::where('key', 'reports.basic')->first();
        PlanFeatureValue::factory()->create([
            'plan_id' => $this->subscription->plan_id,
            'feature_id' => $basicReportsFeature->id,
            'value_bool' => true,
        ]);

        $advancedReportsFeature = Feature::where('key', 'reports.advanced_analytics')->first();
        PlanFeatureValue::factory()->create([
            'plan_id' => $this->subscription->plan_id,
            'feature_id' => $advancedReportsFeature->id,
            'value_bool' => false,
        ]);

        // Should allow basic reports
        $response = $this->actingAs($this->tenantUser)
            ->getJson("/api/tenant/{$this->tenant->company_username}/reports/monthly-profit-loss");

        $response->assertStatus(200);
    }
}
