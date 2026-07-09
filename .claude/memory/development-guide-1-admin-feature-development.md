# Admin Feature Development Guide

## Overview
Complete guide for developing admin panel features in the multi-tenant SaaS platform following the established codebase patterns.

## Key Pattern Overview

### 🎯 Core Pattern Principles
This codebase follows a specific pattern that MUST be strictly followed:

1. **Controller Layer**: Uses `ResponseService::send()` for ALL responses
2. **Service Layer**: Uses `sendResponse()` method for ALL returns (inherited from BaseService)
3. **Repository Layer**: Uses `DataListManager::list()` for ALL list operations
4. **DataTables Integration**: Uses `DataListManager::dataTableHandle()` for DataTables

### 📦 Standard Response Pattern
**Service Layer (always returns this format):**
```php
return $this->sendResponse(
    bool $success,
    string $message,
    mixed $data = [],
    int $status = 200,
    string $errorMessage = ""
);
```

**Controller Layer (always uses ResponseService):**
```php
return ResponseService::send([
    'response' => $response, // Pass service response here
    'data' => $data,          // Optional additional data
], view: $view, successRoute: $route);
```

### 🎨 View Path Pattern (MUST USE)
**NEVER hardcode view paths - ALWAYS use `viewss()` function:**

```php
// ✅ CORRECT
view: viewss('your-feature', 'list')

// ❌ WRONG
view: 'admin.your-feature.index'
```

**Before creating controllers, add your view paths to Viewed class:**
```php
// app/Http/Services/Response/Viewed.php
protected static array $views = [
    'your-feature' => [
        'list' => 'admin.your-feature.index',
        'create' => 'admin.your-feature.create',
        'edit' => 'admin.your-feature.edit',
    ],
];
```

### 🔍 DataListManager Pattern (Repository)
**ALL list methods must follow this exact structure:**
```php
public function dataList($request): array
{
    return DataListManager::list(
        request: $request,
        query: Model::query(),
        
        searchable: [
            'table.column_name',
            // More searchable columns
        ],
        
        filters: [
            'filter_key' => [
                'column' => 'table.column_name',
                'type' => 'basic' // or 'date' or 'daterange'
            ],
            // More filters
        ],
        
        select: [
            'table.id',
            'table.column_name',
            // More select columns
        ],
        
        notIn: isset($request->notIn) ? $request->notIn : [],
    );
}
```

### 📋 Standard Method Names
**Repository Methods:**
- `dataList($request)` - List with DataListManager
- `createData(array $data)` - Create new record
- `get{Model}ByAny(string|int $value)` - Find by ID or slug

**Service Methods:**
- `getListData($request)` - Get list data
- `storeOrUpdateData($request)` - Create or update
- `deleteData($id)` - Delete record
- `statusUpdate($id, $status)` - Update status
- `createData($request)` - Get create form data
- `singleData($request)` - Get single record

**Controller Methods:**
- `index($request)` - List page with DataTable
- `create($request)` - Create form page
- `store($request)` - Store new record
- `edit($request, $id)` - Edit form page
- `destroy($id)` - Delete record
- `status($request)` - Status toggle AJAX

## Table of Contents
1. [File Structure](#file-structure)
2. [Development Workflow](#development-workflow)
3. [Step-by-Step Implementation](#step-by-step-implementation)
4. [Security Considerations](#security-considerations)
5. [Best Practices](#best-practices)

---

## File Structure

### Admin Feature Files Location
```
app/
├── Http/
│   ├── Controllers/
│   │   └── Admin/
│   │       └── YourFeatureController.php
│   ├── Services/
│   │   └── YourFeature/
│   │       ├── YourFeatureService.php
│   │       ├── YourFeatureServiceInterface.php
│   │       ├── YourFeatureRepository.php
│   │       └── YourFeatureRepositoryInterface.php
│   └── Requests/
│       └── YourFeature/
│           ├── YourFeatureCreateRequest.php
│           └── YourFeatureUpdateRequest.php
├── Models/
│   └── YourModel.php
└── View/
    └── Components/
        └── YourFeatureComponent.php

resources/
└── views/
    └── admin/
        └── your-feature/
            ├── index.blade.php
            ├── create.blade.php
            └── edit.blade.php

routes/
└── include/
    └── your-feature.php

database/
└── migrations/
    └── YYYY_MM_DD_HHMMSS_create_your_table.php
```

---

## Development Workflow

### Phase 1: Planning
1. Define feature requirements
2. Identify database schema
3. Plan permissions needed
4. Design UI/UX

### Phase 2: Database Setup
1. Create migration
2. Define model with relationships
3. Run migration

### Phase 3: Backend Development
1. Create Form Request Validators
2. Implement Repository
3. Implement Service
4. Create Controller
5. Add Routes

### Phase 4: Frontend Development
1. Create views
2. Implement DataTables
3. Add JavaScript functionality
4. Style with Tailwind

### Phase 5: Integration & Testing
1. Test CRUD operations
2. Validate permissions
3. Test security measures
4. Performance testing

---

## Step-by-Step Implementation

### Step 1: Database Migration

**Create Migration:**
```bash
php artisan make:migration create_your_features_table
```

**Migration File Structure:**
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('your_features', function (Blueprint $table) {
            $table->id();
            
            // Your columns here
            $table->string('name', 150);
            $table->string('slug', 150)->unique();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->tinyInteger('status')->default(1);
            
            // Indexes for performance
            $table->index(['status', 'created_at']);
            $table->index('slug');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('your_features');
    }
};
```

**Run Migration:**
```bash
php artisan migrate
```

---

### Step 2: Model Creation

**Create Model:**
```bash
php artisan make:model YourFeature
```

**Model Structure:**
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\Auditable;

class YourFeature extends Model
{
    use SoftDeletes, Auditable;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'created_by',
        'updated_by',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
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

---

### Step 3: Form Request Validation

**Create Request Classes:**
```bash
php artisan make:request YourFeature/YourFeatureCreateRequest
php artisan make:request YourFeature/YourFeatureUpdateRequest
```

**Create Request:**
```php
<?php

namespace App\Http\Requests\YourFeature;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class YourFeatureCreateRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Add your authorization logic
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        $featureId = $this->edit_id;

        return [
            'name' => 'required|string|max:150',
            'slug' => 'required|string|max:150|unique:your_features,slug,' . $featureId,
            'description' => 'nullable|string|max:1000',
            'status' => 'required|integer|in:0,1',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The name field is required.',
            'slug.unique' => 'This slug is already in use.',
            // Custom messages
        ];
    }
}
```

**Update Request:**
```php
<?php

namespace App\Http\Requests\YourFeature;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class YourFeatureUpdateRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $featureId = $this->route('id');

        return [
            'name' => 'required|string|max:150',
            'slug' => 'required|string|max:150|unique:your_features,slug,' . $featureId,
            'description' => 'nullable|string|max:1000',
            'status' => 'required|integer|in:0,1',
        ];
    }
}
```

### 🎯 Important: BaseFormRequest Pattern
**ALL request classes MUST extend `BaseFormRequest` (not FormRequest):**

```php
// ✅ CORRECT
use App\Http\Requests\BaseFormRequest;

class YourFeatureCreateRequest extends BaseFormRequest
{
    // ...
}

// ❌ WRONG - Don't use Laravel's default FormRequest
use Illuminate\Foundation\Http\FormRequest;

class YourFeatureCreateRequest extends FormRequest
{
    // ...
}
```

**Why BaseFormRequest?**
- Handles API vs Web validation responses automatically
- Returns consistent JSON error format for API requests
- Standardizes validation error handling across the application

---

### Step 3.5: Add View Paths to Viewed Class

**CRITICAL: Before creating controllers, you MUST add view paths to the Viewed class:**

**File Location:** `app/Http/Services/Response/Viewed.php`

**Add Your Feature View Paths:**
```php
protected static array $views = [
    // ... existing views ...

    'your-feature' => [
        'list' => 'admin.your-feature.index',
        'create' => 'admin.your-feature.create',
        'edit' => 'admin.your-feature.edit',
    ],

    // ... more views ...
];
```

### 🎯 View Path Pattern

**How to use `viewss()` helper function:**

```php
// ✅ CORRECT - Always use viewss()
return ResponseService::send([
    'data' => $data,
], view: viewss('your-feature', 'list'));

// ❌ WRONG - Never hardcode view paths
return ResponseService::send([
    'data' => $data,
], view: 'admin.your-feature.index');
```

**Benefits of Viewed class:**
- Centralized view path management
- Easy to refactor view paths later
- Consistent view path structure
- Prevents typos in view paths
- Makes view locations discoverable

---

### Step 4: Repository Layer

**Create Repository Interface:**
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

**Create Repository:**
```php
<?php

namespace App\Http\Services\YourFeature;

use App\Http\Repositories\BaseRepository;
use App\Models\YourFeature;
use App\Support\DataListManager;
use Illuminate\Support\Collection;
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

### Step 5: Service Layer

**Create Service Interface:**
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

**Create Service:**
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
            $data['created_by'] = Auth::id();
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

### Step 6: Controller

**Create Controller:**
```bash
php artisan make:controller Admin/YourFeatureController
```

**Controller Implementation:**
```php
<?php

namespace App\Http\Controllers\Admin\YourFeature;

use App\Enums\StatusEnum;
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

    /**
     * Display a listing of the resource.
     */
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
                    'name' => function ($item) {
                        return '
                        <div class="flex items-center gap-2">
                            <p>' . $item->name . '<br>
                            <small>' . $item->slug . '</small>
                            </p>
                        </div>';
                    },

                    'created_at' => fn ($item) =>
                    $item->created_at?->diffForHumans(),

                    'description' => fn ($item) =>
                    $item->description ? substr($item->description, 0, 50) . '...' : '-',

                    'status' => fn ($item) =>
                    toggle_column(
                        route('feature.status'),
                        $item->id,
                        $item->status == 1
                    ),

                    'actions' => fn ($item) =>
                    action_buttons([
                        edit_column(route('feature.edit', $item->id)),
                        delete_column(route('feature.delete', $item->id)),
                    ]),
                ],
                rawColumns: ['name', 'description', 'status', 'actions']
            );
        }

        return ResponseService::send([
            'data' => $data,
        ], view: viewss('your-feature', 'list'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $data = $this->service->createData($request)['data'];
        $data['pageTitle'] = __('Create New Feature');

        return ResponseService::send([
            'data' => $data,
        ], view: viewss('your-feature', 'create'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(YourFeatureCreateRequest $request)
    {
        $response = $this->service->storeOrUpdateData($request);
        return ResponseService::send([
            'response' => $response,
        ], successRoute: 'feature.list');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $id)
    {
        $request->merge(['id' => $id]);
        $data = $this->service->createData($request)['data'];
        $data['pageTitle'] = __('Update Feature');

        return ResponseService::send([
            'data' => $data,
        ], view: viewss('your-feature', 'create'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
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

### Step 7: Service Provider Registration

**Register in ServiceLayerProvider:**
```php
// app/Providers/ServiceLayerProvider.php

use App\Http\Services\YourFeature\YourFeatureRepository;
use App\Http\Services\YourFeature\YourFeatureRepositoryInterface;
use App\Http\Services\YourFeature\YourFeatureService;
use App\Http\Services\YourFeature\YourFeatureServiceInterface;

public function register(): void
{
    // ... other bindings
    
    $this->app->bind(YourFeatureRepositoryInterface::class, YourFeatureRepository::class);
    $this->app->bind(YourFeatureServiceInterface::class, YourFeatureService::class);
}
```

---

### Step 8: Routes

**Create Route File:**
```php
<?php

// routes/include/your-feature.php

use App\Http\Controllers\Admin\YourFeature\YourFeatureController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['admin.auth', 'permission']], function () {
    
    // Main listing (view + DataTable AJAX)
    Route::get('features', [YourFeatureController::class, 'index'])
        ->name('feature.list');
    
    // Create form
    Route::get('features/create', [YourFeatureController::class, 'create'])
        ->name('feature.create');
    
    // Store new feature
    Route::post('features', [YourFeatureController::class, 'store'])
        ->name('feature.store');
    
    // Edit form
    Route::get('features/{id}', [YourFeatureController::class, 'edit'])
        ->name('feature.edit');
    
    // Delete feature
    Route::delete('features/{id}', [YourFeatureController::class, 'destroy'])
        ->name('feature.delete');
    
    // Status toggle
    Route::post('features/status', [YourFeatureController::class, 'status'])
        ->name('feature.status');
});
```

**Include in Main Routes:**
```php
// routes/web.php or api.php

require __DIR__.'/include/your-feature.php';
```

---

### Step 9: Admin Views

**Index View with DataTables:**
```blade
{{-- resources/views/admin/your-feature/index.blade.php ---

@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Your Features Management</h3>
                    <div class="card-tools">
                        <button class="btn btn-primary btn-sm" onclick="createFeature()">
                            <i class="fas fa-plus"></i> Add New
                        </button>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <input type="text" class="form-control" id="search" placeholder="Search...">
                        </div>
                        <div class="col-md-2">
                            <select class="form-control" id="status">
                                <option value="">All Status</option>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-secondary" onclick="resetFilters()">Reset</button>
                        </div>
                    </div>
                    
                    <!-- DataTable -->
                    <table class="table table-striped table-bordered" id="featuresTable">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="selectAll"></th>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Slug</th>
                                <th>Status</th>
                                <th>Created Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                    
                    <!-- Bulk Actions -->
                    <div class="bulk-actions mt-3" style="display:none;">
                        <button class="btn btn-danger btn-sm" onclick="bulkDelete()">
                            <i class="fas fa-trash"></i> Delete Selected
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="featureModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add Feature</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="featureForm">
                    @csrf
                    <div class="form-group">
                        <label>Name *</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="form-group">
                        <label>Slug *</label>
                        <input type="text" class="form-control" name="slug" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Status *</label>
                        <select class="form-control" name="status" required>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveFeature()">Save</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    loadDataTable();
});

function loadDataTable() {
    $('#featuresTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('yourFeatures.list') }}',
            data: function(d) {
                d.search = $('#search').val();
                d.status = $('#status').val();
            }
        },
        columns: [
            { data: 'checkbox', orderable: false },
            { data: 'id' },
            { data: 'name' },
            { data: 'slug' },
            { data: 'status' },
            { data: 'created_at' },
            { data: 'actions', orderable: false }
        ],
        order: [[5, 'desc']]
    });
}

function createFeature() {
    $('#featureForm')[0].reset();
    $('#modalTitle').text('Add Feature');
    $('#featureModal').modal('show');
}

function editFeature(id) {
    // Fetch feature data and populate modal
    $.get('{{ route('yourFeatures.show', ['id' => '']) }}/' + id, function(response) {
        if (response.success) {
            const feature = response.data;
            $('#featureForm').find('[name="name"]').val(feature.name);
            $('#featureForm').find('[name="slug"]').val(feature.slug);
            $('#featureForm').find('[name="description"]').val(feature.description);
            $('#featureForm').find('[name="status"]').val(feature.status);
            
            $('#modalTitle').text('Edit Feature');
            $('#featureModal').modal('show');
            $('#featureForm').attr('data-id', id);
        }
    });
}

function saveFeature() {
    const formData = new FormData($('#featureForm')[0]);
    const id = $('#featureForm').attr('data-id');
    
    const url = id 
        ? '{{ route('yourFeatures.update', ['id' => '']) }}/' + id
        : '{{ route('yourFeatures.store') }}';
    
    $.ajax({
        url: url,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        success: function(response) {
            if (response.success) {
                $('#featureModal').modal('hide');
                $('#featuresTable').DataTable().ajax.reload();
                toastr.success(response.message);
            } else {
                toastr.error(response.message);
            }
        },
        error: function(xhr) {
            const errors = xhr.responseJSON?.errors || {};
            Object.values(errors).forEach(error => {
                toastr.error(error[0]);
            });
        }
    });
}

function deleteFeature(id) {
    if (confirm('Are you sure you want to delete this feature?')) {
        $.ajax({
            url: '{{ route('yourFeatures.delete', ['id' => '']) }}/' + id,
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    $('#featuresTable').DataTable().ajax.reload();
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message);
                }
            }
        });
    }
}

function bulkDelete() {
    const ids = [];
    $('input[name="feature_id[]"]:checked').each(function() {
        ids.push($(this).val());
    });
    
    if (ids.length === 0) {
        toastr.warning('Please select items to delete');
        return;
    }
    
    if (confirm(`Are you sure you want to delete ${ids.length} items?`)) {
        $.ajax({
            url: '{{ route('yourFeatures.bulkDelete') }}',
            type: 'POST',
            data: {
                ids: ids,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    $('#featuresTable').DataTable().ajax.reload();
                    $('.bulk-actions').hide();
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message);
                }
            }
        });
    }
}

function resetFilters() {
    $('#search').val('');
    $('#status').val('');
    $('#featuresTable').DataTable().ajax.reload();
}
</script>
@endpush
@endsection
```

---

### Step 10: Add Admin Permissions

**Add Permission to Database:**
```sql
INSERT INTO permissions (name, display_name, module_name) 
VALUES ('your_feature_access', 'Access Your Features', 'your_feature');
```

**Add Permission Check:**
```php
// In controller constructor
public function __construct(YourFeatureServiceInterface $service)
{
    $this->service = $service;
    $this->middleware('permission:your_feature_access');
}
```

---

## Security Considerations

### 1. Authentication & Authorization
✅ Always use admin authentication middleware  
✅ Implement permission checks  
✅ Validate user ownership of resources  
✅ Log all admin actions

### 2. Input Validation
✅ Use Form Request validation  
✅ Sanitize all user inputs  
✅ Validate file uploads  
✅ Prevent SQL injection with Eloquent

### 3. CSRF Protection
✅ Include CSRF tokens in forms  
✅ Verify tokens on AJAX requests  
✅ Use secure HTTP methods

### 4. XSS Protection
✅ Escape output in views  
✅ Use Laravel's blade templates  
✅ Sanitize HTML content

### 5. Data Protection
✅ Never expose sensitive data in responses  
✅ Use secure passwords  
✅ Implement rate limiting  
✅ Log security events

---

## Best Practices

### Performance
✅ Use database indexes  
✅ Implement pagination  
✅ Cache frequently accessed data  
✅ Optimize queries

### Code Quality
✅ Follow SOLID principles  
✅ Keep controllers thin  
✅ Use dependency injection  
✅ Write maintainable code

### Testing
✅ Write unit tests  
✅ Test security measures  
✅ Test edge cases  
✅ Performance testing

---

## Quick Reference Checklist

### ✅ Pattern Compliance Check
- [ ] **Controller**: All responses use `ResponseService::send()`
- [ ] **Service**: All returns use `$this->sendResponse()`
- [ ] **Repository**: All lists use `DataListManager::list()`
- [ ] **DataTables**: Uses `DataListManager::dataTableHandle()`
- [ ] **Requests**: ALL extend `BaseFormRequest` (NOT FormRequest)
- [ ] **Views**: ALL use `viewss()` helper (NO hardcoded paths)
- [ ] **Viewed Class**: Add view paths before creating controllers
- [ ] **Method Names**: Follow standard naming convention

### Database
- [ ] Create migration with proper indexes
- [ ] Define model with relationships
- [ ] Add fillable fields
- [ ] Run migration

### Backend
- [ ] Create Form Request validators (extend BaseFormRequest)
- [ ] Add view paths to Viewed class (before creating controllers)
- [ ] Implement repository interface with correct method names
- [ ] Implement repository class with DataListManager pattern
- [ ] Implement service interface with correct method names
- [ ] Implement service class with sendResponse pattern
- [ ] Register interfaces in ServiceLayerProvider
- [ ] Create controller with ResponseService pattern
- [ ] Use viewss() for all view paths (never hardcode)
- [ ] Add routes with proper naming

### Frontend
- [ ] Create index view with DataTables
- [ ] Create form views (create/edit)
- [ ] Implement DataTable columns and actions
- [ ] Add AJAX functionality
- [ ] Style with Tailwind
- [ ] Add CSRF protection

### Security
- [ ] Add permission checks
- [ ] Validate all inputs with Form Requests
- [ ] Implement CSRF protection
- [ ] Add audit logging
- [ ] Test for vulnerabilities

---

## Pattern Quick Reference

### 📁 File Locations
```
Controllers:   app/Http/Controllers/Admin/YourFeature/
Services:      app/Http/Services/YourFeature/
Repositories:  app/Http/Services/YourFeature/
Requests:     app/Http/Requests/YourFeature/
Models:        app/Models/YourFeature.php
Views:         resources/views/admin/your-feature/
Routes:        routes/include/your-feature.php
```

### 🔑 Key Imports
```php
// Controllers
use App\Http\Services\Response\ResponseService;
use App\Http\Services\YourFeature\YourFeatureServiceInterface;
use App\Support\DataListManager;

// Services
use App\Http\Services\BaseService;
use App\Http\Services\Response\DataService;

// Repositories
use App\Http\Repositories\BaseRepository;
use App\Support\DataListManager;

// Requests (CRITICAL - Always use BaseFormRequest)
use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

// Helper Functions (CRITICAL - Always use viewss())
// viewss() is globally available, no import needed
```

### 🎯 Response Patterns
**Service Success:**
```php
return $this->sendResponse(true, __('Success message'), $data);
```

**Service Error:**
```php
return $this->sendResponse(false, __('Error message'));
```

**Controller with View (CRITICAL - use viewss):**
```php
return ResponseService::send([
    'data' => $data,
], view: viewss('your-feature', 'list'));
```

**Controller with Redirect:**
```php
return ResponseService::send([
    'response' => $response,
], successRoute: 'feature.list');
```

### 🎨 View Path Pattern (CRITICAL)

**Step 1: Add view paths to Viewed class:**
```php
// app/Http/Services/Response/Viewed.php
protected static array $views = [
    'your-feature' => [
        'list' => 'admin.your-feature.index',
        'create' => 'admin.your-feature.create',
        'edit' => 'admin.your-feature.edit',
    ],
];
```

**Step 2: Use viewss() helper in controllers:**
```php
// ✅ CORRECT
view: viewss('your-feature', 'list')
view: viewss('your-feature', 'create')
view: viewss('your-feature', 'edit')

// ❌ WRONG - Never hardcode paths
view: 'admin.your-feature.index'
view: 'admin.your-feature.create'
view: 'admin.your-feature.edit'
```

**Why this pattern?**
- Centralized view path management
- Easy to refactor view paths later
- Prevents typos and inconsistencies
- Makes view structure discoverable
- Follows single responsibility principle

---

This guide provides the complete workflow for developing admin features in your multi-tenant SaaS platform following the EXACT patterns established in your codebase. Always reference existing code (like UserController) for implementation details.