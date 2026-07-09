# Tenant API Feature Development Guide

## Overview
Complete guide for developing tenant-specific API features using the **UNIFIED SERVICE PATTERN** where the same service and repository are used for both Admin and API development.

## Table of Contents
1. [Unified Architecture Pattern](#unified-architecture-pattern)
2. [File Structure](#file-structure)
3. [Development Workflow](#development-workflow)
4. [Step-by-Step Implementation](#step-by-step-implementation)
5. [API Resources Pattern](#api-resources-pattern)
6. [Security & Permissions](#security--permissions)

---

## Unified Architecture Pattern

### 🎯 Key Principle: Same Service, Separate Controllers

```
┌─────────────────────────────────────────────────────────────┐
│                    ADMIN CONTROLLER                           │
│  app/Http/Controllers/Admin/YourFeature/YourFeatureController │
│  - Uses: YourFeatureService (SHARED)                        │
│  - Returns: Views via ResponseService::send()               │
│  - Pattern: ResponseService::send($response, view: viewss())  │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│              SHARED SERVICE LAYER                             │
│  app/Http/Services/YourFeature/YourFeatureService            │
│  - Methods return: sendResponse() format                   │
│  - Works for both: Admin (Views) and API (JSON)              │
│  - Pattern: return $this->sendResponse(true, $msg, $data)    │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│             SHARED REPOSITORY LAYER                           │
│  app/Http/Services/YourFeature/YourFeatureRepository        │
│  - Uses: DataListManager::list() for lists                  │
│  - Pattern: return DataListManager::list(...)              │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│                      API CONTROLLER                           │
│  app/Http/Controllers/Api/Tenant/YourFeatureController       │
│  - Uses: YourFeatureService (SHARED)                        │
│  - Returns: JSON via ResponseService::send()                 │
│  - Pattern: ResponseService::send($response)                 │
│  - Optional: Transform data with API Resources               │
└─────────────────────────────────────────────────────────────┘
```

### 🔄 How ResponseService Handles Both

**Automatic Detection:**
```php
// ResponseService::send() automatically detects:
// 1. API Request → Returns JSON
// 2. Web Request → Returns View (if provided) or Redirect

// Admin Controller (Web)
return ResponseService::send([
    'response' => $response,
], view: viewss('your-feature', 'list'));  // Returns View

// API Controller (API)  
return ResponseService::send($response);  // Returns JSON
```

### 🎯 Benefits of Unified Pattern

**Single Source of Truth:**
- ✅ Same business logic for Admin + API
- ✅ No code duplication
- ✅ Consistent behavior across platforms
- ✅ Easier maintenance and updates

**Automatic Response Handling:**
- ✅ ResponseService detects API vs Web automatically
- ✅ No need for separate response handling
- ✅ Consistent response format

**Development Efficiency:**
- ✅ Create service once, use everywhere
- ✅ Create repository once, use everywhere
- ✅ Focus on controller-specific logic only

---

## File Structure

### Unified Feature Files Location
```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/
│   │   │   └── YourFeature/
│   │   │       └── YourFeatureController.php      (ADMIN - Views)
│   │   └── Api/
│   │       └── Tenant/
│   │           └── YourFeatureController.php      (API - JSON)
│   ├── Services/
│   │   └── YourFeature/                           (SHARED)
│   │       ├── YourFeatureService.php
│   │       ├── YourFeatureServiceInterface.php
│   │       ├── YourFeatureRepository.php
│   │       └── YourFeatureRepositoryInterface.php
│   └── Requests/
│       ├── BaseFormRequest.php                    (SHARED)
│       └── YourFeature/
│           ├── YourFeatureCreateRequest.php       (SHARED)
│           └── YourFeatureUpdateRequest.php       (SHARED)
│
├── Models/
│   └── Tenant/
│       └── YourFeature.php                        (SHARED)
│
└── Http/Resources/
    └── Tenant/
        └── YourFeatureResource.php                (API - Data Transformation)

routes/
├── include/
│   └── your-feature.php                           (ADMIN Routes)
└── tenant/
    └── your-feature.php                           (API Routes)

database/
└── migrations/
    └── tenant/
        └── YYYY_MM_DD_HHMMSS_create_your_table.php
```

---

## Development Workflow

### Phase 1: Planning
1. **Feature Definition**: Define feature requirements for both Admin and API
2. **Database Schema**: Plan tenant database structure
3. **API Design**: Plan API endpoints and responses
4. **Resource Design**: Plan API Resource transformations

### Phase 2: Shared Layer Development
1. **Database Migration**: Create tenant migration
2. **Model**: Create tenant model with relationships
3. **Repository**: Implement shared repository with DataListManager
4. **Service**: Implement shared service with sendResponse()
5. **Validators**: Create shared form request validators

### Phase 3: Controller Development
1. **Admin Controller**: Create admin controller with views
2. **API Controller**: Create API controller with resources
3. **Routes**: Add both admin and API routes
4. **Resources**: Create API Resources for data transformation

### Phase 4: Testing & Security
1. **Service Testing**: Test shared business logic
2. **Admin Testing**: Test admin interface
3. **API Testing**: Test API endpoints
4. **Security Testing**: Test tenant isolation and permissions

---

## Step-by-Step Implementation

### Step 1: Tenant Database Migration

**Create Tenant Migration:**
```bash
php artisan make:migration create_your_features_table --path=database/migrations/tenant
```

**Tenant Migration File:**
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tenant')->create('your_features', function (Blueprint $table) {
            $table->id();
            
            // Your columns here
            $table->string('name', 150);
            $table->string('slug', 150)->unique();
            $table->text('description')->nullable();
            
            // Audit fields - standard for tenant tables
            $table->unsignedBigInteger('added_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->tinyInteger('status')->default(1);
            
            // Indexes for performance
            $table->index(['status', 'created_at']);
            $table->index('slug');
            $table->index('added_by');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('your_features');
    }
};
```

---

### Step 2: Tenant Model Creation

**Tenant Model Structure:**
```php
<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class YourFeature extends Model
{
    use SoftDeletes;

    /**
     * The database connection that should be used by the model.
     */
    protected $connection = 'tenant';

    /**
     * The table associated with the model.
     */
    protected $table = 'your_features';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'added_by',
        'updated_by',
        'status',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'status' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(TenantAllEmployee::class, 'added_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(TenantAllEmployee::class, 'updated_by');
    }
}
```

---

### Step 3: Shared Repository Layer

**Repository Interface:**
```php
<?php

namespace App\Http\Services\YourFeature;

use App\Http\Repositories\BaseRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

interface YourFeatureRepositoryInterface extends BaseRepositoryInterface
{
    public function dataList(Request $request): array;
    public function createData(array $data): Model;
    public function getFeatureByAny(string|int $value): ?Model;
}
```

**Repository Implementation:**
```php
<?php

namespace App\Http\Services\YourFeature;

use App\Http\Repositories\BaseRepository;
use App\Models\Tenant\YourFeature;
use App\Support\DataListManager;
use Illuminate\Database\Eloquent\Model;

class YourFeatureRepository extends BaseRepository implements YourFeatureRepositoryInterface
{
    public function __construct(YourFeature $model)
    {
        parent::__construct($model);
    }

    public function dataList(Request $request): array
    {
        return DataListManager::list(
            request: $request,
            query: YourFeature::query(),

            searchable: [
                'your_features.name',
                'your_features.slug',
                'your_features.description',
            ],

            filters: [
                'status' => [
                    'column' => 'your_features.status'
                ],
                'created_at' => [
                    'column' => 'your_features.created_at',
                    'type' => 'date'
                ],
                'created_range' => [
                    'column' => 'your_features.created_at',
                    'type' => 'daterange'
                ],
            ],

            select: [
                'your_features.id',
                'your_features.name',
                'your_features.slug',
                'your_features.description',
                'your_features.status',
                'your_features.created_at',
            ],
            notIn: isset($request->notIn) ? $request->notIn : [],
        );
    }

    public function createData(array $data): Model
    {
        return $this->create($data);
    }

    public function getFeatureByAny(string|int $value): ?Model
    {
        if (is_numeric($value)) {
            $feature = YourFeature::find($value);
            if ($feature) {
                return $feature;
            }
        }

        return YourFeature::where('slug', $value)
            ->orWhere('name', 'like', '%' . $value . '%')
            ->first();
    }
}
```

---

### Step 4: Shared Service Layer

**Service Interface:**
```php
<?php

namespace App\Http\Services\YourFeature;

use App\Http\Requests\YourFeature\YourFeatureCreateRequest;
use App\Http\Services\BaseServiceInterface;

interface YourFeatureServiceInterface extends BaseServiceInterface
{
    public function getListData($request): array;
    public function storeOrUpdateData(YourFeatureCreateRequest $request): array;
    public function deleteData($id): array;
    public function statusUpdate($id, $status): array;
    public function createData($request): array;
    public function singleData($request): array;
}
```

**Service Implementation:**
```php
<?php

namespace App\Http\Services\YourFeature;

use App\Enums\StatusEnum;
use App\Http\Requests\YourFeature\YourFeatureCreateRequest;
use App\Http\Services\BaseService;
use App\Http\Services\Response\DataService;
use App\Support\Helpers;
use Illuminate\Support\Facades\Auth;

class YourFeatureService extends BaseService implements YourFeatureServiceInterface
{
    protected YourFeatureRepositoryInterface $itemRepository;

    public function __construct(YourFeatureRepositoryInterface $repository)
    {
        parent::__construct($repository);
        $this->itemRepository = $repository;
    }

    public function getListData($request): array
    {
        $data = $this->itemRepository->dataList($request);
        return $this->sendResponse(true, __('Data get successfully.'), $data);
    }

    public function storeOrUpdateData(YourFeatureCreateRequest $request): array
    {
        $item = "";
        $data = DataService::featureCreateData($request);
        $message = "";

        if ($request->edit_id) {
            $item = $this->itemRepository->find($request->edit_id);
            if ($item) {
                $this->itemRepository->update($item->id, $data);
                $item = $this->itemRepository->find($item->id);
                $message = __('Feature updated successfully');
            } else {
                return $this->sendResponse(false, __('Data not found'));
            }
        } else {
            $data['slug'] = Helpers::generateUniqueSlug($request->name);
            $data['added_by'] = Auth::guard('api')->id();
            $item = $this->itemRepository->create($data);
            $message = __('Feature created successfully');
        }

        return $this->sendResponse(true, $message, $item);
    }

    public function deleteData($id): array
    {
        $item = $this->itemRepository->find($id);
        if ($item) {
            $this->delete($item->id);
            return $this->sendResponse(true, __('Data deleted successfully'));
        } else {
            return $this->sendResponse(false, __('Data not found'));
        }
    }

    public function statusUpdate($id, $status): array
    {
        $item = $this->itemRepository->find($id);
        if ($item) {
            $this->itemRepository->update($item->id, ['status' => $status]);
            return $this->sendResponse(true, __('Status updated successfully'));
        } else {
            return $this->sendResponse(false, __('Data not found'));
        }
    }

    public function createData($request): array
    {
        $request->merge(['status' => enum(StatusEnum::ACTIVE)]);
        $data = [];

        if ($request->id) {
            $data['item'] = $this->itemRepository->find($request->id);
        }

        return $this->sendResponse(true, __('Data get successfully'), $data);
    }

    public function singleData($request): array
    {
        $value = $request->input('id')
            ?? $request->input('slug')
            ?? $request->input('name');

        if (!$value) {
            return $this->sendResponse(false, __('Invalid request parameter'));
        }

        $data = $this->itemRepository->getFeatureByAny($value);

        if (!$data) {
            return $this->sendResponse(false, __('Feature not found'));
        }

        return $this->sendResponse(true, __('Data get successfully'), $data);
    }
}
```

---

### Step 5: Admin Controller (Web)

**Admin Controller:**
```php
<?php

namespace App\Http\Controllers\Admin\YourFeature;

use App\Http\Controllers\Controller;
use App\Http\Requests\YourFeature\YourFeatureCreateRequest;
use App\Http\Services\Response\ResponseService;
use App\Http\Services\YourFeature\YourFeatureServiceInterface;
use App\Support\DataListManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class YourFeatureController extends Controller
{
    protected YourFeatureServiceInterface $service;

    public function __construct(YourFeatureServiceInterface $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $data['pageTitle'] = __('Feature List');

        if ($request->ajax()) {
            return DataListManager::dataTableHandle(
                request: $request,
                dataProvider: function ($request) {
                    return $this->service
                        ->getListData($request)['data']['data'];
                },
                columns: [
                    'name' => fn ($item) => $item->name,
                    'created_at' => fn ($item) => $item->created_at?->diffForHumans(),
                    'status' => fn ($item) => toggle_column(route('feature.status'), $item->id, $item->status == 1),
                    'actions' => fn ($item) => action_buttons([
                        edit_column(route('feature.edit', $item->id)),
                        delete_column(route('feature.delete', $item->id)),
                    ]),
                ],
                rawColumns: ['status', 'actions']
            );
        }

        return ResponseService::send([
            'data' => $data,
        ], view: viewss('your-feature', 'list'));
    }

    public function create(Request $request)
    {
        $data = $this->service->createData($request)['data'];
        $data['pageTitle'] = __('Create New Feature');

        return ResponseService::send([
            'data' => $data,
        ], view: viewss('your-feature', 'create'));
    }

    public function store(YourFeatureCreateRequest $request)
    {
        $response = $this->service->storeOrUpdateData($request);
        return ResponseService::send([
            'response' => $response,
        ], successRoute: 'feature.list');
    }

    public function edit(Request $request, string $id)
    {
        $request->merge(['id' => $id]);
        $data = $this->service->createData($request)['data'];
        $data['pageTitle'] = __('Update Feature');

        return ResponseService::send([
            'data' => $data,
        ], view: viewss('your-feature', 'create'));
    }

    public function destroy(string $id)
    {
        $response = $this->service->deleteData($id);
        return ResponseService::send([
            'response' => $response,
        ]);
    }

    public function status(Request $request): JsonResponse
    {
        try {
            $response = $this->service->statusUpdate($request->id, $request->status);
            return response()->json($response);
        } catch (\Exception $e) {
            logStore('feature Status', $e->getMessage());
            return response()->json(['success' => false, 'message' => somethingWrong()]);
        }
    }
}
```

---

### Step 6: API Controller (API)

**API Controller:**
```php
<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\YourFeature\YourFeatureCreateRequest;
use App\Http\Resources\Tenant\YourFeatureResource;
use App\Http\Resources\Tenant\YourFeatureCollectionResource;
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
        $response = $this->service->getListData($request);
        
        // Transform data with API Resources (optional)
        if (($response['success'] ?? false) === true && isset($response['data']['data']) && is_iterable($response['data']['data'])) {
            $response['data']['data'] = YourFeatureCollectionResource::collection(
                $response['data']['data']
            );
        }
        
        return ResponseService::send($response);
    }

    public function store(YourFeatureCreateRequest $request): JsonResponse
    {
        $response = $this->service->storeOrUpdateData($request);
        
        // Transform single item with API Resource (optional)
        if (($response['success'] ?? false) === true && isset($response['data'])) {
            $response['data'] = YourFeatureResource::make($response['data']);
        }
        
        return ResponseService::send($response);
    }

    public function show(Request $request, string $company_username, int $id): JsonResponse
    {
        $request->merge(['id' => $id]);
        $response = $this->service->singleData($request);
        
        // Transform single item with API Resource (optional)
        if (($response['success'] ?? false) === true && isset($response['data'])) {
            $response['data'] = YourFeatureResource::make($response['data']);
        }
        
        return ResponseService::send($response);
    }

    public function update(YourFeatureCreateRequest $request, string $company_username, int $id): JsonResponse
    {
        $request->merge(['edit_id' => $id]);
        $response = $this->service->storeOrUpdateData($request);
        
        // Transform single item with API Resource (optional)
        if (($response['success'] ?? false) === true && isset($response['data'])) {
            $response['data'] = YourFeatureResource::make($response['data']);
        }
        
        return ResponseService::send($response);
    }

    public function destroy(Request $request, string $company_username, int $id): JsonResponse
    {
        $response = $this->service->deleteData($id);
        return ResponseService::send($response);
    }
}
```

---

## API Resources Pattern

### Why Use API Resources?

**Data Transformation:**
- ✅ Transform model data for API output
- ✅ Hide sensitive fields
- ✅ Format dates and numbers
- ✅ Include related resources
- ✅ Conditional field display

**Separation of Concerns:**
- ✅ Keep transformation logic separate from controllers
- ✅ Reusable transformation logic
- ✅ Consistent API responses

### Creating API Resources

**Single Item Resource:**
```php
<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\JsonResource;

class YourFeatureResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'status' => $this->status,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            
            // Include related resources if needed
            'added_by' => $this->whenLoaded('addedBy', function () {
                return [
                    'id' => $this->addedBy->id,
                    'name' => $this->addedBy->name,
                ];
            }),
        ];
    }
}
```

**Collection Resource:**
```php
<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\JsonResource;

class YourFeatureCollectionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'status' => $this->status,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
```

**Using Resources in Controllers:**
```php
// Single item
$response['data'] = YourFeatureResource::make($response['data']);

// Collection
$response['data']['data'] = YourFeatureCollectionResource::collection($response['data']['data']);
```

---

## Security & Permissions

### 1. Tenant Isolation

**Automatic Tenant Context:**
- `ResolveTenantContext` middleware handles tenant database switching
- All queries automatically run on tenant database
- User authorization validated against tenant

### 2. Feature-Based Access Control

**API Routes with Subscription Features:**
```php
// routes/tenant/your-feature.php

Route::group(['middleware' => ['tenant.feature:your.feature.read']], function() {
    Route::get('your-features', [YourFeatureController::class, 'index'])
        ->name('yourFeatures.list');
    
    Route::get('your-features/{id}', [YourFeatureController::class, 'show'])
        ->name('yourFeatures.show');
});

Route::group(['middleware' => ['tenant.feature:your.feature.write']], function() {
    Route::post('your-features', [YourFeatureController::class, 'store'])
        ->name('yourFeatures.store');
    
    Route::post('your-features/{id}', [YourFeatureController::class, 'update'])
        ->name('yourFeatures.update');
    
    Route::delete('your-features/{id}', [YourFeatureController::class, 'destroy'])
        ->name('yourFeatures.delete');
});
```

---

## Quick Reference Checklist

### ✅ Unified Pattern Compliance
- [ ] **Service/Repository**: Created ONCE, used for both Admin and API
- [ ] **Admin Controller**: Uses SHARED service + returns views
- [ ] **API Controller**: Uses SHARED service + returns JSON
- [ ] **ResponseService**: Handles both view and JSON automatically
- [ ] **API Resources**: Optional transformation for API responses

### Database
- [ ] Create tenant migration in `database/migrations/tenant/`
- [ ] Use `Schema::connection('tenant')`
- [ ] Add audit fields (added_by, updated_by, status)
- [ ] Add indexes for performance
- [ ] Run migration for all tenants

### Models
- [ ] Create model in `app/Models/Tenant/`
- [ ] Set `protected $connection = 'tenant'`
- [ ] Define fillable fields
- [ ] Add relationships
- [ ] Use soft deletes

### Shared Layer
- [ ] Create repository interface with standard methods
- [ ] Implement repository with DataListManager pattern
- [ ] Create service interface with standard methods
- [ ] Implement service with sendResponse pattern
- [ ] Use BaseFormRequest for all validators

### Admin Controller
- [ ] Create admin controller with SHARED service
- [ ] Use ResponseService::send() with viewss()
- [ ] Add admin routes
- [ ] Add view paths to Viewed class

### API Controller
- [ ] Create API controller with SHARED service
- [ ] Use ResponseService::send() for JSON responses
- [ ] Optional: Transform with API Resources
- [ ] Add tenant routes with feature middleware

### Security
- [ ] Add subscription features to database
- [ ] Apply feature middleware to API routes
- [ ] Test tenant isolation
- [ ] Validate API permissions
- [ ] Add audit logging

---

This guide provides the complete workflow for developing tenant API features using the **UNIFIED SERVICE PATTERN** where the same service and repository are used for both Admin and API development, maximizing code reuse and maintaining consistency across platforms.