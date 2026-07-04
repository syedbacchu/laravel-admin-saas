# Feature Testing Suite Documentation

## 🎯 **Testing Strategy**

Comprehensive testing approach for feature-based access control system covering:
- **Unit Tests**: Individual feature checks
- **Integration Tests**: Complete user workflows  
- **Security Tests**: Access control validation
- **Performance Tests**: Cache effectiveness

## 📁 **Test Structure**

```
tests/
├── Feature/
│   ├── FeatureAccessTest.php       # Basic feature access tests
│   ├── FeatureIntegrationTest.php  # End-to-end workflow tests
│   └── FeatureSecurityTest.php      # Security validation tests
└── FEATURE_TESTING_GUIDE.md        # This documentation
```

## 🧪 **Test Categories**

### **1. Access Control Tests**

#### **FeatureAccessTest.php**
Tests basic feature access functionality:

- ✅ Allows access when feature is enabled
- ✅ Denies access when feature is disabled  
- ✅ Respects vehicle tier limits
- ✅ Handles multiple vehicle tiers (OR logic)
- ✅ Denies access with expired subscription
- ✅ Returns correct feature check status
- ✅ Includes feature map in subscription details
- ✅ Handles numeric feature values
- ✅ Invalidates cache on subscription changes
- ✅ Prevents unauthorized access
- ✅ Allows access during trial
- ✅ Respects multiple feature requirements

### **2. Integration Tests**

#### **FeatureIntegrationTest.php**
Tests complete user workflows:

- ✅ Complete upgrade flow
- ✅ Vehicle limit enforcement
- ✅ Subscription grace period handling
- ✅ Multiple subscription handling
- ✅ Feature value type handling
- ✅ Cross-tenant isolation

## 🚀 **Running Tests**

### **Run All Feature Tests**
```bash
php artisan test --testsuite=Feature --filter=Feature
```

### **Run Specific Test File**
```bash
php artisan test tests/Feature/FeatureAccessTest.php
```

### **Run Specific Test Method**
```bash
php artisan test --filter test_allows_access_when_feature_is_enabled
```

### **With Detailed Output**
```bash
php artisan test --testsuite=Feature --filter=Feature --verbose
```

## 📊 **Test Coverage**

### **Features Covered**
- ✅ Trip Monitoring (`trip.monitoring`)
- ✅ Fuel Management (`fuel.management`, `fuel.intelligence`)
- ✅ Vehicle Tiers (`vehicle.manage_X_X`)
- ✅ Payroll (`payroll.salary_commission`)
- ✅ Reports (`reports.basic`, `reports.advanced_analytics`)
- ✅ Customer Management (`customer.management`)
- ✅ Employee Management (`employee.management`)

### **Scenarios Covered**
- ✅ Active subscription with enabled features
- ✅ Active subscription with disabled features
- ✅ Expired subscription
- ✅ Trial subscription
- ✅ Grace period access
- ✅ Subscription upgrade/downgrade
- ✅ Cross-tenant isolation
- ✅ Unauthorized access attempts
- ✅ Feature cache invalidation
- ✅ Numeric/boolean/decimal feature values

## 🔒 **Security Tests**

### **Expected Security Behaviors**

1. **403 Forbidden** - Feature not available in current package
2. **402 Payment Required** - Subscription expired or inactive
3. **401 Unauthorized** - Invalid authentication credentials
4. **404 Not Found** - Tenant or resource doesn't exist

### **Security Test Cases**

```php
// Test: Access without authentication
$this->getJson('/api/tenant/company/trips')
    ->assertStatus(401);

// Test: Access with wrong tenant
$this->actingAs($user)
    ->getJson('/api/tenant/othercompany/trips')
    ->assertStatus(403);

// Test: Access disabled feature
$this->actingAs($user)
    ->getJson('/api/tenant/company/fuel-ledger') // Feature disabled
    ->assertStatus(403);

// Test: Access with expired subscription
$this->actingAs($user)
    ->getJson('/api/tenant/company/trips') // Subscription expired
    ->assertStatus(402);
```

## 🔧 **Test Utilities**

### **Feature Test Factory**

```php
trait FeatureTestHelpers
{
    protected function enableFeature($tenant, $featureKey)
    {
        $feature = Feature::where('key', $featureKey)->first();
        $subscription = $tenant->activeSubscription();
        
        PlanFeatureValue::factory()->create([
            'plan_id' => $subscription->plan_id,
            'feature_id' => $feature->id,
            'value_bool' => true,
        ]);
    }

    protected function disableFeature($tenant, $featureKey)
    {
        $feature = Feature::where('key', $featureKey)->first();
        $subscription = $tenant->activeSubscription();
        
        PlanFeatureValue::factory()->create([
            'plan_id' => $subscription->plan_id,
            'feature_id' => $feature->id,
            'value_bool' => false,
        ]);
    }

    protected function setNumericFeature($tenant, $featureKey, $value)
    {
        $feature = Feature::where('key', $featureKey)->first();
        $subscription = $tenant->activeSubscription();
        
        PlanFeatureValue::factory()->create([
            'plan_id' => $subscription->plan_id,
            'feature_id' => $feature->id,
            'value_int' => $value,
        ]);
    }
}
```

### **Test Data Builders**

```php
// Create tenant with specific features
$tenant = Tenant::factory()
    ->withFeatures(['trip.monitoring', 'fuel.management'])
    ->create();

// Create tenant with specific plan
$tenant = Tenant::factory()
    ->withPlan('premium')
    ->create();

// Create subscription with custom dates
$subscription = Subscription::factory()
    ->active()
    ->withTrialPeriod(14)
    ->create();
```

## 📈 **Performance Testing**

### **Cache Performance Tests**

```php
public function test_feature_check_is_cached()
{
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create();
    
    // First call - cache miss
    $start1 = microtime(true);
    $this->actingAs($user)
        ->getJson("/api/tenant/{$tenant->company_username}/trips");
    $time1 = microtime(true) - $start1;
    
    // Second call - cache hit
    $start2 = microtime(true);
    $this->actingAs($user)
        ->getJson("/api/tenant/{$tenant->company_username}/trips");
    $time2 = microtime(true) - $start2;
    
    // Cache hit should be significantly faster
    $this->assertLessThan($time1, $time2);
    $this->assertLessThan(0.01, $time2); // < 10ms for cached check
}
```

## 🎯 **Test Execution Best Practices**

### **1. Test Isolation**
```php
protected function setUp(): void
{
    parent::setUp();
    // Fresh database for each test
    $this->artisan('migrate:fresh');
}

protected function tearDown(): void
{
    // Clean up cache
    $this->artisan('cache:clear');
    parent::tearDown();
}
```

### **2. Clear Test Naming**
```php
// Good
public function test_allows_access_when_feature_is_enabled_and_subscription_active()

// Bad  
public function test_access()
```

### **3. Arrange-Act-Assert Pattern**
```php
public function test_feature_access(): void
{
    // Arrange
    $tenant = Tenant::factory()->create();
    $this->enableFeature($tenant, 'trip.monitoring');
    
    // Act
    $response = $this->getJson('/api/tenant/company/trips');
    
    // Assert
    $response->assertStatus(200);
}
```

## 🚨 **Common Test Issues & Solutions**

### **Issue 1: Cache Interference**
```php
// Solution: Clear cache in setUp/tearDown
protected function setUp(): void
{
    parent::setUp();
    $this->artisan('cache:clear');
}
```

### **Issue 2: Middleware Not Running**
```php
// Solution: Ensure your test environment uses middleware
$this->withMiddleware(['auth:api', 'tenant.context']);
```

### **Issue 3: Feature Not Found**
```php
// Solution: Seed features before running tests
protected function setUp(): void
{
    parent::setUp();
    $this->seed(FeatureSeeder::class);
}
```

## 📋 **Testing Checklist**

### **Before Running Tests**
- ✅ Database migrations run
- ✅ Features seeded
- ✅ Cache cleared
- ✅ Test environment configured

### **After Code Changes**
- ✅ Run full test suite
- ✅ Check test coverage report
- ✅ Verify no regressions
- ✅ Update test documentation

### **Before Deployment**
- ✅ All tests pass
- ✅ Coverage > 80%
- ✅ No skipped tests
- ✅ Performance benchmarks met

## 🔗 **Related Documentation**

- [FeatureSeeder Guide](../database/seeders/FeatureSeeder.php)
- [Route Organization Guide](../routes/tenant/README.md)
- [Frontend Feature System](../../transport-saas-user/src/features/feature-check/README.md)

---

**Last Updated**: 2026-06-06  
**Maintained By**: QA Team  
**Version**: 1.0.0
