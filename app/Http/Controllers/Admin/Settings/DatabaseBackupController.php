<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\DatabaseBackup\DatabaseBackupCreateRequest;
use App\Http\Services\DatabaseBackup\DatabaseBackupServiceInterface;
use App\Http\Services\Response\ResponseService;
use App\Support\DataListManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DatabaseBackupController extends Controller
{
    protected DatabaseBackupServiceInterface $service;

    public function __construct(DatabaseBackupServiceInterface $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $data['pageTitle'] = __('Database Backups');

        if ($request->ajax()) {
            return DataListManager::dataTableHandle(
                request: $request,
                dataProvider: function ($request) {
                    return $this->service->getDataTableData($request)['data']['data'];
                },
                columns: [
                    'file_name' => fn ($item) => $item->file_name,
                    'database_name' => fn ($item) => $item->database_name . '<br><small class="text-muted">Host: ' . $item->database_host . '</small>',
                    'backup_created_at' => fn ($item) => $item->backup_created_at->format('Y-m-d H:i:s') . '<br><small class="text-muted">' . $item->backup_created_at->diffForHumans() . '</small>',
                    'file_size' => fn ($item) => $item->readable_file_size ?? '-',
                    'status' => fn ($item) => '<span class="badge ' . ($item->backup_exists ? 'bg-success' : 'bg-danger') . '">' . ($item->backup_exists ? __('Available') : __('Missing')) . '</span>',
                    'actions' => fn ($item) => action_buttons([
                        $item->backup_exists ? download_column(route('databaseBackup.download', $item->id), __('Download Backup')) : null,
                        delete_column(route('databaseBackup.delete', $item->id)),
                    ]),
                ],
                rawColumns: ['database_name', 'backup_created_at', 'status', 'actions']
            );
        }

        // Get backup statistics
        $statistics = $this->service->getBackupStatistics();
        $data['statistics'] = $statistics['data'] ?? [];

        return ResponseService::send([
            'data' => $data,
        ], view: viewss('databaseBackup', 'list'));
    }

    public function create(Request $request)
    {
        $response = $this->service->backupCreateData($request);
        $data['pageTitle'] = __('Create Database Backup');
        $data['function_type'] = 'create';
        $data = array_merge($data, $response['data']);

        return ResponseService::send([
            'data' => $data,
        ], view: viewss('databaseBackup', 'create'));
    }

    public function store(DatabaseBackupCreateRequest $request): RedirectResponse
    {
        $response = $this->service->createDatabaseBackup($request);

        if ($response['success']) {
            return redirect()->route('databaseBackup.list')
                ->with('success', $response['message']);
        }

        return redirect()->back()
            ->with('error', $response['message'])
            ->withInput();
    }

    public function download(int $id): BinaryFileResponse|RedirectResponse
    {
        try {
            $response = $this->service->downloadBackup($id);

            if (!$response['success']) {
                return redirect()->route('databaseBackup.list')
                    ->with('error', $response['message']);
            }

            $filePath = $response['data']['file_path'];
            $fileName = $response['data']['file_name'];

            if (!file_exists($filePath)) {
                return redirect()->route('databaseBackup.list')
                    ->with('error', __('Backup file not found on server'));
            }

            return Response::download($filePath, $fileName);

        } catch (\Throwable $e) {
            logStore('DatabaseBackupController', $e->getMessage());
            return redirect()->route('databaseBackup.list')
                ->with('error', __('Something went wrong during download'));
        }
    }

    public function destroy(int $id): RedirectResponse
    {
        $response = $this->service->deleteDatabaseBackup($id);

        if ($response['success']) {
            return redirect()->route('databaseBackup.list')
                ->with('success', $response['message']);
        }

        return redirect()->route('databaseBackup.list')
            ->with('error', $response['message']);
    }

    public function statistics(Request $request): JsonResponse
    {
        try {
            $response = $this->service->getBackupStatistics();
            return response()->json($response);
        } catch (\Throwable $e) {
            logStore('DatabaseBackupController', $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => __('Failed to get backup statistics'),
            ]);
        }
    }
}