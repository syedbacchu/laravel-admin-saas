<?php

namespace App\Console\Commands;

use App\Http\Services\Tenant\TenantProvisionServiceInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ProvisionTenantCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:provision
        {company_username : Unique tenant/company username (used in URL)}
        {company_name : Company display name}
        {owner_name : Owner full name}
        {--email= : Owner email}
        {--phone= : Owner phone}
        {--password= : Owner password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create tenant owner user, tenant DB, and tenant config file';

    public function __construct(
        protected TenantProvisionServiceInterface $service
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $generatedPassword = null;
        $optionPassword = (string) $this->option('password');
        if ($optionPassword === '') {
            $generatedPassword = Str::random(12);
            $optionPassword = $generatedPassword;
        }

        $payload = [
            'company_username' => strtolower((string) $this->argument('company_username')),
            'company_name' => (string) $this->argument('company_name'),
            'owner_name' => (string) $this->argument('owner_name'),
            'owner_email' => $this->option('email'),
            'owner_phone' => $this->option('phone'),
            'owner_password' => $optionPassword,
        ];

        try {
            $this->validatePayload($payload);
            $response = $this->service->provision($payload);

            if (($response['success'] ?? false) !== true) {
                $this->error($response['message'] ?? 'Tenant provisioning failed');
                $errorMessage = $response['error_message'] ?? '';
                if (!empty($errorMessage)) {
                    $this->line($errorMessage);
                }
                return self::FAILURE;
            }

            $this->info($response['message'] ?? 'Tenant provisioned successfully');
            $this->table(['Field', 'Value'], [
                ['Tenant ID', (string) data_get($response, 'data.tenant_id')],
                ['Company Username', (string) data_get($response, 'data.company_username')],
                ['Database', (string) data_get($response, 'data.db_name')],
                ['Config Path', (string) data_get($response, 'data.config_path')],
            ]);
            if ($generatedPassword !== null) {
                $this->warn('Generated owner password: ' . $generatedPassword);
            }

            return self::SUCCESS;
        } catch (ValidationException $e) {
            foreach ($e->errors() as $errors) {
                foreach ($errors as $error) {
                    $this->error($error);
                }
            }
            return self::INVALID;
        } catch (\Throwable $e) {
            $this->error('Provisioning failed: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    protected function validatePayload(array $payload): void
    {
        $validator = Validator::make($payload, [
            'company_name' => ['required', 'string', 'max:150'],
            'company_username' => ['required', 'string', 'min:3', 'max:60', 'regex:/^[a-z0-9_]+$/', 'unique:tenants,company_username', 'unique:users,username'],
            'owner_name' => ['required', 'string', 'max:120'],
            'owner_email' => ['nullable', 'email', 'max:190', 'unique:users,email'],
            'owner_phone' => ['nullable', 'string', 'max:30', 'unique:users,phone'],
            'owner_password' => ['required', 'string', 'min:8', 'max:120'],
        ]);

        $validator->after(function ($validator) use ($payload) {
            if (in_array($payload['company_username'], config('tenancy.reserved_paths', []), true)) {
                $validator->errors()->add('company_username', 'This company username is reserved.');
            }
        });

        $validator->validate();
    }
}
