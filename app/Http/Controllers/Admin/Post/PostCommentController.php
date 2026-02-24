<?php

namespace App\Http\Controllers\Admin\Post;

use App\Http\Controllers\Controller;
use App\Http\Requests\Post\PostCommentReplyRequest;
use App\Http\Services\PostComment\PostCommentServiceInterface;
use App\Http\Services\Response\ResponseService;
use App\Models\Post;
use App\Support\DataListManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PostCommentController extends Controller
{
    protected PostCommentServiceInterface $service;

    public function __construct(PostCommentServiceInterface $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $data['pageTitle'] = __('Post Comments');
        $selectedPostId = $request->integer('post_id');
        $data['selectedPost'] = $selectedPostId ? Post::query()->select(['id', 'title'])->find($selectedPostId) : null;

        if ($request->ajax()) {
            return DataListManager::dataTableHandle(
                request: $request,
                dataProvider: function ($request) {
                    return $this->service->getAdminCommentList($request)['data']['data'];
                },
                columns: [
                    'post_title' => fn ($item) => e($item->post?->title ?: '-'),
                    'commenter' => fn ($item) => e($item->user?->name ?: $item->name ?: 'Guest'),
                    'comment_preview' => fn ($item) => e(Str::limit((string) $item->comment, 120)),
                    'status_badge' => fn ($item) => $this->statusBadge((int) $item->status),
                    'actions' => fn ($item) => $this->actionButtons($item),
                ],
                rawColumns: ['status_badge', 'actions']
            );
        }

        return ResponseService::send([
            'data' => $data,
        ], view: viewss('postComment', 'list'));
    }

    public function reply(int $id)
    {
        $response = $this->service->getCommentForReply($id);
        if (($response['success'] ?? false) === false) {
            return ResponseService::send([
                'response' => $response,
            ]);
        }

        $data['pageTitle'] = __('Reply Comment');
        $data['item'] = $response['data'];

        return ResponseService::send([
            'data' => $data,
        ], view: viewss('postComment', 'reply'));
    }

    public function storeReply(PostCommentReplyRequest $request, int $id): RedirectResponse
    {
        $response = $this->service->adminReplyToComment($request, $id);
        return ResponseService::send([
            'response' => $response,
        ], successRoute: 'postComment.list');
    }

    public function approve(int $id): RedirectResponse
    {
        $response = $this->service->approveComment($id);
        return ResponseService::send([
            'response' => $response,
        ], successRoute: 'postComment.list');
    }

    public function decline(int $id): RedirectResponse
    {
        $response = $this->service->declineComment($id);
        return ResponseService::send([
            'response' => $response,
        ], successRoute: 'postComment.list');
    }

    public function destroy(int $id): RedirectResponse
    {
        $response = $this->service->deleteComment($id);
        return ResponseService::send([
            'response' => $response,
        ], successRoute: 'postComment.list');
    }

    protected function statusBadge(int $status): string
    {
        if ($status === 1) {
            return '<span class="badge bg-success">Approved</span>';
        }

        if ($status === 2) {
            return '<span class="badge bg-danger">Declined</span>';
        }

        return '<span class="badge bg-warning">Pending</span>';
    }

    protected function actionButtons($item): string
    {
        $buttons = [];

        if ((int) $item->status !== 1) {
            $buttons[] = '<a href="'.route('postComment.approve', $item->id).'" class="inline-flex items-center px-3 py-1.5 text-sm font-semibold text-green-600 hover:text-white hover:bg-green-600 border border-green-600 rounded-lg transition duration-200">Approve</a>';
        }

        if ((int) $item->status !== 2) {
            $buttons[] = '<a href="'.route('postComment.decline', $item->id).'" class="inline-flex items-center px-3 py-1.5 text-sm font-semibold text-yellow-600 hover:text-white hover:bg-yellow-600 border border-yellow-600 rounded-lg transition duration-200">Decline</a>';
        }

        $buttons[] = '<a href="'.route('postComment.reply', $item->id).'" class="inline-flex items-center px-3 py-1.5 text-sm font-semibold text-blue-600 hover:text-white hover:bg-blue-600 border border-blue-600 rounded-lg transition duration-200">Reply</a>';
        $buttons[] = delete_column(route('postComment.delete', $item->id));

        return action_buttons($buttons);
    }
}
