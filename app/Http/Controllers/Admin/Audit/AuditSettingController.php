<?php

namespace App\Http\Controllers\Admin\Audit;

use App\Http\Controllers\Controller;
use App\Http\Services\Audit\AuditServiceInterface;
use App\Models\AuditLog;
use App\Support\DataListManager;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Yajra\DataTables\Facades\DataTables;


class AuditSettingController extends Controller
{
    protected AuditServiceInterface $service;

    public function __construct(AuditServiceInterface $service)
    {
        $this->service = $service;
    }

    //
    public function settings(Request $request) {
        $data['pageTitle'] = __('Audit Settings');
        $data['disabled'] = [];

        if (!Storage::exists('sd_audit_settings.json')) {
            Storage::put('sd_audit_settings.json', json_encode($data['disabled'], JSON_PRETTY_PRINT));
        } else {
            $data['disabled'] = json_decode(Storage::get('sd_audit_settings.json'), true) ?? [];
        }
        if ($request->ajax()) {
            return $this->modelList();
        }
        return view('admin.audit.model_setting', $data);
    }

    public function modelList() {
        $models = collect(File::files(app_path('Models')))
            ->map(function ($file) {
                return [
                    'class' => 'App\\Models\\' . pathinfo($file->getFilename(), PATHINFO_FILENAME),
                    'name'  => pathinfo($file->getFilename(), PATHINFO_FILENAME),
                ];
            });

        $disabled = [];
        if (Storage::exists('sd_audit_settings.json')) {
            $disabled = json_decode(Storage::get('sd_audit_settings.json'), true) ?? [];
        }

        return DataTables::of($models)
            ->addIndexColumn()
            ->addColumn('status', function ($model) use ($disabled) {
                $isDisabled = isset($disabled[$model['class']]);
                $checked = $isDisabled ? '' : 'checked';
                return '<input type="checkbox" class="toggle-status" data-model="'.$model['class'].'" '.$checked.'>';
            })
            ->rawColumns(['status'])
            ->make(true);
    }


    public function updateModel(Request $request)
    {
        $model = $request->model;

        $enabled = filter_var($request->enabled, FILTER_VALIDATE_BOOLEAN);

        $disabled = [];
        if (Storage::exists('sd_audit_settings.json')) {
            $disabled = json_decode(Storage::get('sd_audit_settings.json'), true) ?? [];
        }

        if ($enabled) {
            unset($disabled[$model]);
        } else {
            $disabled[$model] = false;
        }

        Storage::put('sd_audit_settings.json', json_encode($disabled, JSON_PRETTY_PRINT));

        return response()->json([
            'success' => true,
            'message' => __('Audit setting updated successfully'),
            'data' => $disabled
        ]);
    }

    public function resetModel()
    {
        if (Storage::exists('sd_audit_settings.json')) {
            Storage::delete('sd_audit_settings.json');
        }

        Storage::put('sd_audit_settings.json', json_encode([], JSON_PRETTY_PRINT));

        return redirect()->back()->with('success', __('Audit settings reset successfully! All models are now enabled.'));
    }


    public function index(Request $request): View|JsonResponse
    {
        $data['pageTitle'] = __('Audit Report');
        if ($request->ajax()) {
            return $this->getDataTableAuditLog($request);
        }

        return view('admin.audit.index', $data);
    }

    protected function getDataTableAuditLog($request): JsonResponse
    {
        return DataListManager::dataTableHandle(
            request: $request,
            dataProvider: function ($request) {
                return $this->service
                    ->getDataTableData($request)['data']['data'];
            },
            columns: [
                'created_at' => fn ($item) =>
                    $item->created_at?->diffForHumans(),

                'user' => fn ($item) =>
                    $item->user ? $item->user->name : 'System',

                'model_type' => fn ($item) =>
                    class_basename($item->model_type),

                'actions' => fn ($item) =>
                    action_buttons([
                        view_button($item->id, 'View Details'),
                        delete_column(route('audit.log.delete', $item->id)),
                    ]),
            ],
            rawColumns: ['actions','status']
        );
//
//        $query = $this->service->getDataTableData($request->model_type);
//
//        return DataTables::eloquent($query)
//            ->addIndexColumn()
//            ->addColumn('created_at', function ($item) {
//                return $item->created_at;
//            })
//            ->addColumn('user', function ($item) {
//                return $item->user ? $item->user->name : 'System';
//            })
//            ->addColumn('model', function ($item) {
//                return class_basename($item->model_type);
//            })
//            ->addColumn('actions', function ($item) {
//                return action_buttons([
//                    view_button($item->id, 'View Details'),
//                    delete_column(route('audit.log.delete', $item->id)),
//                ]);
//            })
//            ->rawColumns(['actions'])
//            ->make(true);
    }

    public function show($id)
    {
        $item = $this->service->detailsData($id);
        if ($item['success']) {
            $log = $item['data'];
            return response()->json([
                'id' => $log->id,
                'user' => optional($log->user)->name ?? 'System',
                'event' => $log->event,
                'model_type' => class_basename($log->model_type),
                'model_id' => $log->model_id,
                'ip_address' => $log->ip_address,
                'user_agent' => $log->user_agent,
                'created_at' => $log->created_at->format('Y-m-d H:i:s'),
                'old_values' => $log->old_values,
                'new_values' => $log->new_values,
            ]);
        } else {
            return response()->json([]);
        }
    }

    public function destroy($id): RedirectResponse {

        $response = $this->service->deleteData($id);
        if ($response['success']) {
            return redirect()->back()->with('success',$response['message']);
        } else {
            return redirect()->back()->with('dismiss',$response['message']);
        }
    }

}
