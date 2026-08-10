<?php

namespace App\Http\Controllers\Admin\Contact;

use App\Http\Controllers\Controller;
use App\Http\Requests\Contact\ContactReplyRequest;
use App\Http\Services\Response\ResponseService;
use App\Http\Services\Contact\ContactServiceInterface;
use App\Support\DataListManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    protected ContactServiceInterface $service;

    public function __construct(ContactServiceInterface $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $data['pageTitle'] = __('Contact Messages');
        if ($request->ajax()) {
            return DataListManager::dataTableHandle(
                request: $request,
                dataProvider: function ($request) {
                    return $this->service
                        ->getDataTableData($request)['data']['data'];
                },
                columns: [
                    'name' => fn ($item) => $item->name,
                    'email' => fn ($item) => $item->email,
                    'phone' => fn ($item) => $item->phone ?? '-',
                    'subject' => fn ($item) => $item->subject,
                    'status' => fn ($item) => $item->status === 'replied'
                        ? '<span class="badge bg-success/10 text-success">Replied</span>'
                        : '<span class="badge bg-info/10 text-info">Pending</span>',
                    'created_at' => fn ($item) => $item->created_at->format('M j, Y'),
                    'actions' => function ($item) {
                        $buttons = [
                            '<button type="button" onclick="viewContact(' . $item->id . ')" class="btn btn-sm btn-outline-primary">View</button>',
                        ];
//                        if ($item->status === 'pending') {
                            $buttons[] = '<button type="button" onclick="openReplyModal(' . $item->id . ')" class="btn btn-sm btn-primary">Reply</button>';
//                        }
                        return implode(' ', $buttons);
                    },
                ],
                rawColumns: ['status', 'actions']
            );
        }

        return ResponseService::send([
            'data' => $data,
        ], null, viewss('contact', 'list'));
    }

    public function show(Request $request): JsonResponse
    {
        $id = (int) $request->query('id');

        if (!$id) {
            return response()->json([
                'success' => false,
                'message' => 'Contact ID is required',
                'data' => [],
                'status' => 400,
            ], 400);
        }

        $response = $this->service->getContactDetail($id);
        return response()->json($response);
    }

    public function reply(ContactReplyRequest $request, int $id): JsonResponse
    {
        $response = $this->service->replyToContact($id, $request->validated());
        return response()->json($response);
    }
}
