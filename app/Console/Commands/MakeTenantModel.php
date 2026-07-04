<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class MakeTenantModel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:tenant-model {name : The name of the model} {--m|migration : Create a migration for the model}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new tenant-specific model and optional migration';

    protected $files;

    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');
        $createMigration = $this->option('migration');

        // Create Models/Tenant directory if it doesn't exist
        $this->createTenantDirectory();

        // Create the model
        $this->createTenantModel($name);

        // Create migration if requested
        if ($createMigration) {
            $this->createTenantMigration($name);
        }

        $this->info("✅ Tenant model '{$name}' created successfully!");
        $this->info("📁 Location: app/Models/Tenant/{$name}.php");

        if ($createMigration) {
            $tableName = Str::plural(Str::snake($name));
            $this->info("📁 Migration: database/migrations/tenant/" . date('Y_m_d_His') . "_create_{$tableName}_table.php");
        }
    }

    protected function createTenantDirectory()
    {
        $directory = app_path('Models/Tenant');

        if (!$this->files->exists($directory)) {
            $this->files->makeDirectory($directory, 0755, true);
            $this->info("📁 Created directory: app/Models/Tenant");
        }
    }

    protected function createTenantModel($name)
    {
        $modelName = Str::singular($name);
        $modelPath = app_path('Models/Tenant/' . $modelName . '.php');

        if ($this->files->exists($modelPath)) {
            $this->error("❌ Model '{$modelName}' already exists!");
            return;
        }

        $stub = $this->getModelStub($modelName);

        $this->files->put($modelPath, $stub);
        $this->info("✅ Created model: {$modelName}");
    }

    protected function getModelStub($name)
    {
        $tableName = Str::plural(Str::snake($name));

        return <<<EOT
<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class {$name} extends Model
{
    use HasFactory;

    /**
     * The database connection that should be used by the model.
     *
     * @var string
     */
    protected \$connection = 'tenant';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected \$table = '{$tableName}';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected \$fillable = [
        // Add your fillable fields here
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected \$casts = [
        // Add your casts here
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected \$dates = [
        'created_at',
        'updated_at',
    ];
}
EOT;
    }

    protected function createTenantMigration($name)
    {
        $tableName = Str::plural(Str::snake($name));
        $migrationName = "create_{$tableName}_table";

        // Create the tenant migrations directory if it doesn't exist
        $tenantMigrationPath = database_path('migrations/tenant');

        if (!$this->files->exists($tenantMigrationPath)) {
            $this->files->makeDirectory($tenantMigrationPath, 0755, true);
        }

        // Generate the migration file name
        $fileName = date('Y_m_d_His') . "_{$migrationName}.php";
        $filePath = $tenantMigrationPath . '/' . $fileName;

        // Check if migration already exists
        if ($this->files->exists($filePath)) {
            $this->warn("⚠️  Migration already exists: {$fileName}");
            return;
        }

        $stub = $this->getMigrationStub($tableName);

        $this->files->put($filePath, $stub);
        $this->info("✅ Created migration: {$fileName}");
    }

    protected function getMigrationStub($tableName)
    {
        return <<<EOT
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('{$tableName}', function (Blueprint \$table) {
            \$table->id();
            \$table->string('name', 150);
            \$table->text('description')->nullable();
            \$table->unsignedBigInteger('added_by')->nullable();
            \$table->unsignedBigInteger('updated_by')->nullable();
            \$table->tinyInteger('status')->default(1);
            \$table->timestamps();

            \$table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('{$tableName}');
    }
};
EOT;
    }
}
