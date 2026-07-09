# Quick Reference Guide

## Overview
Comprehensive quick reference for day-to-day development in the multi-tenant SaaS platform.

---

## 🚀 Quick Start Commands

### Admin Development
```bash
# Create migration
php artisan make:migration create_your_table

# Create model
php artisan make:model YourModel

# Create controller
php artisan make:controller Admin/YourController

# Create request validator
php artisan make:request YourFeature/YourRequest

# Run migrations
php artisan migrate

# Fresh migration (WARNING: deletes data)
php artisan migrate:fresh

# Rollback last migration
php artisan migrate:rollback
```

### Tenant Development
```bash
# Create tenant model + migration
php artisan make:tenant-model YourFeature -m

# Run tenant migrations for all tenants
php artisan tenant:migrate

# Run tenant migration for specific tenant
php artisan tenant:migrate --company_username=demotenant

# Run tenant migration by ID
php artisan tenant:migrate --tenant_id=1

# Fresh tenant migration (WARNING: deletes tenant data)
php artisan tenant:migrate:fresh --company_username=demotenant

# Fresh migration for all tenants (WARNING: deletes all tenant data)
php artisan tenant:migrate:fresh
```

### Cache & Config
```bash
# Clear application cache
php artisan cache:clear

# Clear configuration cache
php artisan config:clear

# Clear route cache
php artisan route:clear

# Clear view cache
php artisan view:clear

# Re-cache configuration
php artisan config:cache

# Re-cache routes
php artisan route:cache

# Clear all caches
php artisan optimize:clear
```

### Database Operations
```bash
# Seed database
php artisan db:seed

# Fresh migration with seed
php artisan migrate:fresh --seed

# Create seeder
php artisan make:seeder YourSeeder

# Run specific seeder
php artisan db:seed --class=YourSeeder
```

---

## 📁 File Structure Reference

### Admin Side Structure
```
app/
├── Http/
│   ├── Controllers/
│   │   └── Admin/                    # Admin controllers
│   ├── Services/
│   │   └── YourFeature/              # Business logic + data access
│   │       ├── YourFeatureService.php
│   │       ├── YourFeatureServiceInterface.php
│   │       ├── YourFeatureRepository.php
│   │       └── YourFeatureRepositoryInterface.php
│   └── Requests/
│       └── YourFeature/              # Validation
│           ├── YourFeatureCreateRequest.php
│           └── YourFeatureUpdateRequest.php
├── Models/                           # Admin database models
│   └── YourModel.php
└── View/Components/                  # Blade components

resources/
└── views/admin/                       # Admin views
    └── your-feature/

routes/
└── include/                          # Admin routes
    └── your-feature.php

database/
└── migrations/                       # Admin migrations
    └── create_your_table.php
```

### Tenant Side Structure
```
app/
├── Http/
│   ├── Controllers/Api/Tenant/       # Tenant API controllers
│   ├── Services/
│   │   └── YourFeature/              # Tenant business logic + data access
│   │       ├── YourFeatureApiService.php
│   │       ├── YourFeatureApiServiceInterface.php
│   │       ├── YourFeatureApiRepository.php
│   │       └── YourFeatureApiRepositoryInterface.php
│   └── Requests/
│       └── YourFeature/              # API validation
│           ├── YourFeatureCreateRequest.php
│           └── YourFeatureUpdateRequest.php
├── Models/Tenant/                    # Tenant models
│   └── YourFeature.php

routes/tenant/                        # Tenant routes
└── your-feature.php

database/migrations/tenant/          # Tenant migrations
└── create_your_table.php
```

---

## 🔧 Code Templates

### Admin Model Template
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\Auditable;

class YourModel extends Model
{
    use SoftDeletes, Auditable;

    protected $fillable = [
        'name',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
```

### Tenant Model Template
```php
<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class YourFeature extends Model
{
    use SoftDeletes;

    protected $connection = 'tenant';
    protected $table = 'your_features';

    protected $fillable = [
        'name',
        'added_by',
        'updated_by',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
    ];

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(TenantAllEmployee::class, 'added_by');
    }
}
```

### Admin Controller Template
```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Services\Response\ResponseService;
use App\Http\Services\YourFeature\YourFeatureServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class YourFeatureController extends Controller
{
    protected YourFeatureServiceInterface $service;

    public function __construct(YourFeatureServiceInterface $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): JsonResponse
    {
        $response = $this->service->getDataTableData($request);
        return ResponseService::send($response);
    }

    public function store(Request $request): JsonResponse
    {
        $response = $this->service->store($request);
        return ResponseService::send($response);
    }
}
```

### Tenant API Controller Template
```php
<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Services\Response\ResponseService;
use App\Http\Services\YourFeature\YourFeatureApiServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class YourFeatureController extends Controller
{
    protected YourFeatureApiServiceInterface $service;

    public function __construct(YourFeatureApiServiceInterface $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): JsonResponse
    {
        $response = $this->service->getList($request);
        return ResponseService::send($response);
    }
}
```

---

## 🗄️ Database Connection Management

### Admin Database Connection
```php
// Default connection - uses 'mysql' from config/database.php
$data = AdminModel::all();

// Specify connection explicitly
$data = AdminModel::on('mysql')->get();

// Raw query on admin database
$users = DB::connection('mysql')->select('SELECT * FROM users');
```

### Tenant Database Connection
```php
// Tenant models automatically use 'tenant' connection
$tenantData = TenantModel::all();

// Manual tenant connection
$data = DB::connection('tenant')->select('SELECT * FROM your_table');

// Switch connection temporarily
DB::setDefaultConnection('tenant');
$data = YourModel::all();
DB::setDefaultConnection('mysql');
```

### Transaction Management
```php
// Admin database transaction
DB::beginTransaction();
try {
    // Your operations
    DB::commit();
} catch (\Exception $e) {
    DB::rollBack();
}

// Tenant database transaction
DB::connection('tenant')->beginTransaction();
try {
    // Your operations
    DB::connection('tenant')->commit();
} catch (\Exception $e) {
    DB::connection('tenant')->rollBack();
}
```

---

## 🔒 Security Best Practices

### Authentication & Authorization
```php
// Admin routes - require admin authentication
Route::group(['middleware' => ['admin.auth', 'permission']], function () {
    // Your routes here
});

// Tenant API routes - require tenant authentication + context
Route::group(['middleware' => [ 'auth:api', 'tenant.context', 'tenant.subscription.active', 'tenant.api.permission']], function () {
    // Your routes here
});

// Feature-based access control
Route::group(['middleware' => ['tenant.feature:your.feature.read']], function () {
    // Routes requiring specific subscription feature
});
```

### Input Validation
```php
// Always use form request validation
public function rules(): array
{
    return [
        'name' => 'required|string|max:150',
        'email' => 'required|email|unique:users,email',
        'status' => 'required|integer|in:0,1',
    ];
}

// Validate file uploads
'file' => 'required|file|mimes:pdf,doc,docx|max:10240'

// Validate arrays
'items.*.name' => 'required|string|max:150'
'items.*.quantity' => 'required|integer|min:1'
```

### SQL Injection Prevention
```php
// ✅ SAFE - Use Eloquent ORM
User::where('email', $email)->first();

// ✅ SAFE - Use parameter binding
DB::select('SELECT * FROM users WHERE email = ?', [$email]);

// ❌ UNSAFE - Direct string interpolation
DB::select("SELECT * FROM users WHERE email = '$email'");
```

### XSS Prevention
```php
// ✅ SAFE - Use blade templates (automatic escaping)
{{ $userInput }}

// ✅ SAFE - Use Laravel's helpers
{!! e($htmlContent) !!}  // Escape first, then display unescaped

// ❌ UNSAFE - Direct output
echo $userInput;
```

### CSRF Protection
```php
// Forms - include CSRF token
<form method="POST">
    @csrf
    <!-- Your form fields -->
</form>

// AJAX - include CSRF token
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
```

---

## 🎯 Common Patterns

### DataTable Integration
```php
// Repository method
public function dataTableList($request): LengthAwarePaginator
{
    $query = $this->model->query();

    // Search
    if ($request->has('search') && !empty($request->search)) {
        $query->where('name', 'like', "%{$request->search}%");
    }

    // Filter
    if ($request->has('status') && $request->status !== '') {
        $query->where('status', $request->status);
    }

    // Order
    $query->orderBy($request->order_by ?? 'created_at', $request->order_direction ?? 'desc');

    // Paginate
    return $query->paginate($request->per_page ?? 15);
}
```

### DataListManager Usage
```php
// In repository
public function getTenantList($request): array
{
    $query = $this->model->query();

    $searchable = ['name', 'description'];
    $filters = [
        'status' => ['column' => 'status', 'type' => 'basic'],
        'created_at' => ['column' => 'created_at', 'type' => 'daterange'],
    ];

    return DataListManager::list($request, $query, $searchable, $filters);
}
```

### Response Format
```php
// Success response
return [
    'success' => true,
    'message' => 'Operation completed successfully',
    'data' => $yourData,
    'status' => 200
];

// Error response
return [
    'success' => false,
    'message' => 'Operation failed',
    'data' => [],
    'status' => 500,
    'error_message' => 'Detailed error message'
];

// List response with pagination
return [
    'success' => true,
    'message' => 'Data retrieved successfully',
    'data' => [
        'total_count' => 100,
        'total_page' => 10,
        'per_page' => 10,
        'current_page' => 1,
        'data' => $items
    ],
    'status' => 200
];
```

---

## 🐛 Troubleshooting

### Common Issues & Solutions

#### Tenant Connection Issues
```bash
# Issue: Tenant database connection fails
# Solution: Check tenant credentials and database existence

# Diagnose tenant connection
php artisan tenant:diagnose --company_username=demotenant

# Verify tenant database exists
mysql -u root -p -e "SHOW DATABASES LIKE 'sd_tenant_%'"

# Check tenant connection details
mysql -u root -p -e "SELECT * FROM tenants WHERE company_username = 'demotenant'"
```

#### Migration Issues
```bash
# Issue: Migration fails with table already exists
# Solution: Check migration status and rollback if needed

# Check migration status
php artisan migrate:status

# Rollback last migration
php artisan migrate:rollback

# Force migration (WARNING: may cause data loss)
php artisan migrate --force
```

#### Permission Issues
```bash
# Issue: Access denied despite having permissions
# Solution: Clear cache and re-check permissions

# Clear all caches
php artisan optimize:clear

# Check user permissions
mysql -u root -p -e "SELECT * FROM permission_role WHERE role_id = 2"

# Verify feature access
mysql -u root -p -e "SELECT * FROM plan_feature_values WHERE plan_id = 1"
```

#### Cache Issues
```bash
# Issue: Changes not reflecting after deployment
# Solution: Clear all caches

php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
```

### Debug Mode
```bash
# Enable debug mode
# In .env file
APP_DEBUG=true

# Check Laravel logs
tail -f storage/logs/laravel.log

# Check specific error
grep "YourFeature" storage/logs/laravel.log
```

---

## 📊 Performance Optimization

### Database Optimization
```sql
-- Add indexes for frequently queried columns
CREATE INDEX idx_status_created_at ON your_features(status, created_at);
CREATE INDEX idx_search_field ON your_features(search_field);

-- Analyze slow queries
SHOW FULL PROCESSLIST;
-- Check query execution plan
EXPLAIN SELECT * FROM your_features WHERE status = 1;
```

### Caching Strategies
```php
// Cache frequently accessed data
$categories = Cache::remember('case_categories', 3600, function () {
    return CaseCategory::where('status', 1)->get();
});

// Clear cache when data changes
Cache::forget('case_categories');

// Cache with tags
Cache::tags(['case_management'])->remember('cases', 3600, function () {
    return Case::all();
});
Cache::tags(['case_management'])->flush();
```

### Query Optimization
```php
// ❌ INEFFICIENT - N+1 query problem
$cases = Case::all();
foreach ($cases as $case) {
    echo $case->client->name; // Separate query for each case
}

// ✅ EFFICIENT - Eager loading
$cases = Case::with('client')->get();
foreach ($cases as $case) {
    echo $case->client->name; // No additional queries
}

// Select only needed columns
$cases = Case::select('id', 'title', 'case_number')->get();

// Chunk large datasets
Case::chunk(100, function ($cases) {
    foreach ($cases as $case) {
        // Process 100 records at a time
    }
});
```

---

## 🧪 Testing Checklist

### Admin Features
```bash
✅ Authentication & Authorization
✅ CRUD operations
✅ DataTables functionality
✅ Search and filters
✅ Bulk operations
✅ Export functionality
✅ Form validation
✅ Error handling
✅ Permission checks
✅ Audit logging
```

### Tenant API Features
```bash
✅ Tenant context resolution
✅ Tenant data isolation
✅ Authentication
✅ Feature-based access control
✅ CRUD operations
✅ Search and pagination
✅ Input validation
✅ Error responses
✅ Staff permissions
✅ Subscription validation
```

### Security Testing
```bash
✅ SQL injection attempts
✅ XSS vulnerability testing
✅ CSRF token validation
✅ Authentication bypass attempts
✅ Authorization testing
✅ Rate limiting
✅ Data encryption validation
✅ Tenant isolation verification
✅ Session management
✅ File upload security
```

---

## 📋 Deployment Checklist

### Pre-Deployment
```bash
✅ All tests passing
✅ No debug code in production
✅ Environment variables configured
✅ Database backups created
✅ Migrations tested on staging
✅ Cache cleared
✅ Routes cached
✅ Config cached
✅ Log permissions set
✅ File storage configured
```

### Post-Deployment
```bash
✅ Run migrations on all tenant databases
✅ Verify admin functionality
✅ Test tenant API endpoints
✅ Check tenant database connectivity
✅ Verify subscription features
✅ Test authentication
✅ Monitor error logs
✅ Check database performance
✅ Verify email functionality
✅ Test file uploads
```

---

## 📝 Development Workflow

### Feature Development Process
1. **Planning**
   - Define requirements
   - Identify database schema
   - Plan permissions/features
   - Design UI/UX

2. **Database Setup**
   - Create migrations
   - Define models
   - Set up relationships
   - Run migrations

3. **Backend Development**
   - Create validators
   - Implement repositories
   - Implement services
   - Create controllers
   - Add routes
   - Register in ServiceLayerProvider

4. **Frontend Development**
   - Create views/components
   - Implement DataTables
   - Add JavaScript functionality
   - Style with Tailwind

5. **Security Integration**
   - Add permissions
   - Apply middleware
   - Implement audit logging
   - Test security measures

6. **Testing**
   - Unit tests
   - Integration tests
   - Security tests
   - Performance tests

7. **Documentation**
   - Update API docs
   - Add code comments
   - Document permissions
   - Create user guides

---

## 🔗 Useful Resources

### Documentation Links
- Laravel Documentation: https://laravel.com/docs
- DataTables: https://datatables.net/
- Tailwind CSS: https://tailwindcss.com/

### Internal Resources
- Admin Development Guide: `development-guide-1-admin-feature-development.md`
- Tenant API Development Guide: `development-guide-2-tenant-api-development.md`
- Complete Example: `development-guide-3-complete-example.md`

### Quick Commands Reference
```bash
# Quick project setup
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm install && npm run build

# Quick development server
php artisan serve
npm run dev

# Quick testing
php artisan test
php artisan test --filter=YourFeatureTest
```

---

This quick reference guide provides the essential information needed for day-to-day development in your multi-tenant SaaS platform. For detailed implementation guides, refer to the comprehensive guides in this documentation series.