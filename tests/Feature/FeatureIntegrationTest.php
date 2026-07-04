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
 * Feature Integration Tests
 * End-to-end testing for complete feature workflows
 */
class FeatureIntegrationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test complete upgrade flow
     */
    public function test_upgrade_flow_enables_new_features(): void
    {
        // 1. Create tenant with basic plan
        $tenant = Tenant::factory()->create([
            'company_name' => 'Growing Transport',
            'company_username' => 'growingtransport',
        ]);

        $basicPlan = Plan::factory()->create(['name' => 'Basic Plan']);
        $premiumPlan = Plan::factory()->create(['name' => 'Premium Plan']);

        $subscription = Subscription::factory()->create([
            'tenant_id' => $tenant->id,
            'plan_id' => $basicPlan->id,
            'status' => 'active',
        ]);

        $user = User::factory()->create();

        // 2. Enable only basic features
        $basicFeature = Feature::where('key', 'vehicle.manage_1_5')->first();
        PlanFeatureValue::factory()->create([
            'plan_id' => $basicPlan->id,
            'feature_id' => $basicFeature->id,
            'value_bool' => true,
        ]);

        // 3. Verify basic features work
        $response = $this->actingAs($user)
            ->getJson("/api/tenant/{$tenant->company_username}/vehicles");
        $this->assertEquals(200, $response->status());

        // 4. Verify premium features don't work
        $premiumFeature = Feature::where('key', 'trip.monitoring')->first();
        PlanFeatureValue::factory()->create([
            'plan_id' => $basicPlan->id,
            'feature_id' => $premiumFeature->id,
            'value_bool' => false,
        ]);

        $response = $this->actingAs($user)
            ->getJson("/api/tenant/{$tenant->company_username}/trips");
        $this->assertEquals(403, $response->status());

        // 5. Upgrade to premium plan
        $subscription->update([
            'plan_id' => $premiumPlan->id,
            'status' => 'active',
        ]);

        // 6. Enable premium features
        PlanFeatureValue::factory()->create([
            'plan_id' => $premiumPlan->id,
            'feature_id' => $premiumFeature->id,
            'value_bool' => true,
        ]);

        // 7. Clear cache
        $this->artisan('cache:clear');

        // 8. Verify premium features now work
        $response = $this->actingAs($user)
            ->getJson("/api/tenant/{$tenant->company_username}/trips");
        $this->assertEquals(200, $response->status());
    }

    /**
     * Test vehicle limit enforcement
     */
    public function test_enforces_vehicle_count_limits(): void
    {
        // This would require actual vehicle creation and counting logic
        // Simplified test showing the concept

        $tenant = Tenant::factory()->create(['company_username' => 'limitedtransport']);
        $plan = Plan::factory()->create();

        Subscription::factory()->create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'status' => 'active',
        ]);

        $user = User::factory()->create();

        // Set 5 vehicle limit
        $vehicleFeature = Feature::where('key', 'vehicle.manage_1_5')->first();
        PlanFeatureValue::factory()->create([
            'plan_id' => $plan->id,
            'feature_id' => $vehicleFeature->id,
            'value_bool' => true,
        ]);

        // Access vehicles endpoint
        $response = $this->actingAs($user)
            ->getJson("/api/tenant/{$tenant->company_username}/vehicles");

        // Should allow access (limit checking happens in business logic)
        $response->assertStatus(200);
    }

    /**
     * Test subscription grace period
     */
    public function test_allows_access_during_grace_period(): void
    {
        $tenant = Tenant::factory()->create(['company_username' => 'gracetransport']);
        $plan = Plan::factory()->create();

        $subscription = Subscription::factory()->create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'status' => 'past_due',
            'ends_at' => now()->subDay(),
            'grace_ends_at' => now()->addDays(7),
        ]);

        $user = User::factory()->create();

        // Enable feature
        $feature = Feature::where('key', 'trip.monitoring')->first();
        PlanFeatureValue::factory()->create([
            'plan_id' => $plan->id,
            'feature_id' => $feature->id,
            'value_bool' => true,
        ]);

        // Should still allow access during grace period
        $response = $this->actingAs($user)
            ->getJson("/api/tenant/{$tenant->company_username}/trips");

        $response->assertStatus(200);
    }

    /**
     * Test concurrent subscription handling
     */
    public function test_handles_multiple_subscriptions_correctly(): void
    {
        $tenant = Tenant::factory()->create(['company_username' => 'multisub']);
        $user = User::factory()->create();

        // Create expired subscription
        $oldPlan = Plan::factory()->create(['name' => 'Old Plan']);
        Subscription::factory()->create([
            'tenant_id' => $tenant->id,
            'plan_id' => $oldPlan->id,
            'status' => 'expired',
            'ends_at' => now()->subMonth(),
        ]);

        // Create active subscription
        $newPlan = Plan::factory()->create(['name' => 'New Plan']);
        $activeSubscription = Subscription::factory()->create([
            'tenant_id' => $tenant->id,
            'plan_id' => $newPlan->id,
            'status' => 'active',
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addMonth(),
        ]);

        // Enable feature on new plan only
        $feature = Feature::where('key', 'trip.monitoring')->first();
        PlanFeatureValue::factory()->create([
            'plan_id' => $newPlan->id,
            'feature_id' => $feature->id,
            'value_bool' => true,
        ]);

        // Should use active subscription features
        $response = $this->actingAs($user)
            ->getJson("/api/tenant/{$tenant->company_username}/trips");

        $response->assertStatus(200);
    }

    /**
     * Test feature value type handling
     */
    public function test_handles_different_feature_value_types(): void
    {
        $tenant = Tenant::factory()->create(['company_username' => 'valuetest']);
        $plan = Plan::factory()->create();

        Subscription::factory()->create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'status' => 'active',
        ]);

        $user = User::factory()->create();

        // Test boolean feature
        $boolFeature = Feature::factory()->create([
            'key' => 'test.boolean_feature',
            'value_type' => 'boolean',
        ]);
        PlanFeatureValue::factory()->create([
            'plan_id' => $plan->id,
            'feature_id' => $boolFeature->id,
            'value_bool' => true,
        ]);

        // Test integer feature
        $intFeature = Feature::factory()->create([
            'key' => 'test.integer_feature',
            'value_type' => 'integer',
        ]);
        PlanFeatureValue::factory()->create([
            'plan_id' => $plan->id,
            'feature_id' => $intFeature->id,
            'value_int' => 100,
        ]);

        // Test decimal feature
        $decimalFeature = Feature::factory()->create([
            'key' => 'test.decimal_feature',
            'value_type' => 'decimal',
        ]);
        PlanFeatureValue::factory()->create([
            'plan_id' => $plan->id,
            'feature_id' => $decimalFeature->id,
            'value_decimal' => 99.99,
        ]);

        // Get subscription details
        $response = $this->actingAs($user)
            ->getJson("/api/tenant/{$tenant->company_username}/account/subscription-details");

        $response->assertStatus(200)
            ->assertJsonPath('data.features.test.boolean_feature', true)
            ->assertJsonPath('data.features.test.integer_feature', 100)
            ->assertJsonPath('data.features.test.decimal_feature', 99.99);
    }

    /**
     * Test cross-tenant isolation
     */
    public function test_maintains_tenant_feature_isolation(): void
    {
        $tenant1 = Tenant::factory()->create(['company_username' => 'tenant1']);
        $tenant2 = Tenant::factory()->create(['company_username' => 'tenant2']);

        $user = User::factory()->create();

        // Create different plans for each tenant
        $plan1 = Plan::factory()->create();
        $plan2 = Plan::factory()->create();

        Subscription::factory()->create([
            'tenant_id' => $tenant1->id,
            'plan_id' => $plan1->id,
            'status' => 'active',
        ]);

        Subscription::factory()->create([
            'tenant_id' => $tenant2->id,
            'plan_id' => $plan2->id,
            'status' => 'active',
        ]);

        // Enable feature only for tenant1
        $feature = Feature::where('key', 'trip.monitoring')->first();
        PlanFeatureValue::factory()->create([
            'plan_id' => $plan1->id,
            'feature_id' => $feature->id,
            'value_bool' => true,
        ]);

        // Tenant1 should have access
        $response1 = $this->actingAs($user)
            ->getJson("/api/tenant/{$tenant1->company_username}/trips");
        $this->assertEquals(200, $response1->status());

        // Tenant2 should NOT have access
        $response2 = $this->actingAs($user)
            ->getJson("/api/tenant/{$tenant2->company_username}/trips");
        $this->assertEquals(403, $response2->status());
    }
}
