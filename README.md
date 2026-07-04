# laravel-admin-saas
Enterprise-grade SaaS foundation built in Laravel with landlord/tenant architecture, dynamic database switching, automated tenant provisioning, and scalable multi-database infrastructure.

## Docker Setup

### 1) Prepare environment

```bash
cp .env.docker.example .env
```

### 2) Build and start containers

```bash
docker compose up -d --build
```

### 3) Install dependencies

```bash
docker compose exec app composer install
docker compose exec node npm install
```

### 4) Generate app key

```bash
docker compose exec app php artisan key:generate
```

### 5) Run migrations and seeders

```bash
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan storage:link
```

### 6) Frontend assets

```bash
# one-time build
docker compose exec node npm run build

# or dev mode
docker compose exec node npm run dev -- --host
```

### 7) Access services

- Laravel app: http://localhost:8080
- phpMyAdmin: http://localhost:8081
  - Server: `mysql`
  - Username: `root`
  - Password: `root`

### Useful commands

```bash
# Stop containers
docker compose down

# Stop and remove DB volume (fresh database)
docker compose down -v

# Run tenant migrations for all tenant DBs
docker compose exec app php artisan tenant:migrate

# Run tenant migrations for specific tenant
docker compose exec app php artisan tenant:migrate --company_username=rifatmotor
```

## Tenant Database Commands

Use these commands for tenant database schema management.

```bash
# Run tenant migrations for all tenant databases
docker compose exec app php artisan tenant:migrate

# Run tenant migrations for one tenant by ID
docker compose exec app php artisan tenant:migrate --tenant_id=1

# Run tenant migrations for one tenant by company username
docker compose exec app php artisan tenant:migrate --company_username=rifatmotor

# Drop all tables and rebuild tenant schema for all tenants
docker compose exec app php artisan tenant:migrate:fresh

# Drop all tables and rebuild tenant schema for one tenant
docker compose exec app php artisan tenant:migrate:fresh --tenant_id=1
docker compose exec app php artisan tenant:migrate:fresh --company_username=rifatmotor
```

Notes:

- `tenant:migrate` runs only pending tenant migrations.
- `tenant:migrate:fresh` is destructive. It drops all existing tables in the tenant database and recreates them from `database/migrations/tenant`.
- Use `tenant:migrate:fresh` only when you want a fully clean tenant database.

## Tenant Model & Migration Development

### Custom Command: `make:tenant-model`

This project includes a custom Artisan command for quickly creating tenant-specific models and migrations.

#### Command Usage

```bash
# Create tenant model only
php artisan make:tenant-model Product

# Create tenant model with migration
php artisan make:tenant-model Product -m

# Other variations
php artisan make:tenant-model Product --migration
php artisan make:tenant-model OrderItem -m
```

#### What It Creates

**1. Model:** `app/Models/Tenant/Product.php`
- Namespace: `App\Models\Tenant`
- Database connection: `tenant`
- Table name: Auto-pluralized (e.g., `products`)
- Standard tenant columns ready

**2. Migration:** `database/migrations/tenant/YYYY_MM_DD_HHMMSS_create_products_table.php`
- Standard tenant schema structure
- Common columns: `added_by`, `updated_by`, `status`, `timestamps`
- Proper indexes for performance

#### Step-by-Step Example

**Step 1: Create a new tenant model with migration**
```bash
php artisan make:tenant-model Product -m
```

**Step 2: Edit the migration file**
Edit: `database/migrations/tenant/XXXX_XX_XX_XXXXXX_create_products_table.php`

```php
public function up(): void
{
    Schema::create('products', function (Blueprint $table) {
        $table->id();
        $table->string('name', 150);
        $table->string('sku', 100)->unique();
        $table->decimal('price', 14, 2)->default(0);
        $table->text('description')->nullable();
        $table->unsignedBigInteger('added_by')->nullable();
        $table->unsignedBigInteger('updated_by')->nullable();
        $table->tinyInteger('status')->default(1);
        $table->timestamps();

        $table->index(['status', 'created_at']);
        $table->index('sku');
    });
}
```

**Step 3: Edit the model file**
Edit: `app/Models/Tenant/Product.php`

```php
<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $table = 'products';

    protected $fillable = [
        'name',
        'sku', 
        'price',
        'description',
        'added_by',
        'updated_by',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'status' => 'integer',
    ];

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
```

**Step 4: Run migrations for all tenant databases**
```bash
# Docker environment
docker compose exec app php artisan tenant:migrate

# Local environment
php artisan tenant:migrate
```

**Step 5: Run migration for specific tenant**
```bash
# Docker environment
docker compose exec app php artisan tenant:migrate --company_username=demotenant

# Local environment
php artisan tenant:migrate --company_username=demotenant
```

#### Complete Workflow Example

```bash
# 1. Create tenant model + migration
php artisan make:tenant-model Category -m

# 2. Edit migration and model files

# 3. Run migration for all tenants
docker compose exec app php artisan tenant:migrate

# 4. Or run for specific tenant
docker compose exec app php artisan tenant:migrate --company_username=demotenant

# 5. Fresh migrate (drop & recreate) if needed
docker compose exec app php artisan tenant:migrate:fresh --company_username=demotenant
```

#### File Structure

```
app/
└── Models/
    └── Tenant/
        ├── Product.php
        ├── Category.php
        ├── Order.php
        └── Customer.php

database/
└── migrations/
    └── tenant/
        ├── create_products_table.php
        ├── create_categories_table.php
        ├── create_orders_table.php
        └── create_customers_table.php
```

#### Key Features

✅ **Auto-creates directories** if they don't exist
✅ **Proper namespace** (`App\Models\Tenant`)
✅ **Tenant connection** automatically set
✅ **Table naming** (pluralizes model name)
✅ **Standard tenant columns** (added_by, updated_by, status, etc.)
✅ **Safe file creation** (warns if files exist)

#### Important Notes

- All tenant models MUST use `protected $connection = 'tenant'`
- Tenant migrations are stored in `database/migrations/tenant/`
- Tenant models are stored in `app/Models/Tenant/`
- Each tenant has their own separate database with identical schema
- Migrations run against EACH tenant database separately

## Common Api
- Test Connection
- Blogs
- basic home with pricing
- profile
- vehicle category
- registration serials
- registration zone
- areas

## Tenant Api
- Auth: login , password reset
- Profile, update , change password
- staff
- dashboard
- file system
- customers
- offices
- daily office expense
- salary expense
- employees
