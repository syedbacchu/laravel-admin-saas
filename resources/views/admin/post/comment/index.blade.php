<x-layout.default>
@section('title', $pageTitle)
<link rel="stylesheet" href="{{ asset('assets/common/datatables/jquery.dataTables.min.css') }}">
<script src="{{ asset('assets/common/jquery-3.7.0.min.js') }}"></script>
<script src="{{ asset('assets/common/datatables/jquery.dataTables.min.js') }}"></script>

    <div class="mt-8 bg-white shadow-xl rounded-2xl p-6 border border-gray-100">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-4">
            <h5 class="text-2xl font-bold text-gray-800">{{ $pageTitle ?? __('Post Comments') }}</h5>
            @if(!empty($selectedPost))
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center px-3 py-1.5 text-sm font-semibold text-indigo-700 bg-indigo-50 border border-indigo-200 rounded-lg">
                        {{ __('Post') }}: {{ $selectedPost->title }}
                    </span>
                    <a href="{{ route('postComment.list') }}"
                       class="inline-flex items-center px-3 py-1.5 text-sm font-semibold text-gray-700 hover:text-white hover:bg-gray-700 border border-gray-400 rounded-lg transition duration-200">
                        {{ __('All Comments') }}
                    </a>
                </div>
            @endif
        </div>

        <div class="overflow-x-auto">
            <x-common.datatable
                id="itemsTable"
                ajax="{{ route('postComment.list', request()->only('post_id')) }}"
                :columns="[
                    ['data' => 'post_title', 'name' => 'post_title', 'title' => 'Post', 'orderable' => false, 'searchable' => false],
                    ['data' => 'commenter', 'name' => 'commenter', 'title' => 'Commenter', 'orderable' => false, 'searchable' => false],
                    ['data' => 'comment_preview', 'name' => 'comment_preview', 'title' => 'Comment', 'orderable' => false, 'searchable' => false],
                    ['data' => 'status_badge', 'name' => 'status_badge', 'title' => 'Status', 'orderable' => false, 'searchable' => false],
                    [
                        'data' => 'actions',
                        'title' => 'Actions',
                        'orderable' => false,
                        'searchable' => false
                    ],
                ]"
                :filters="[
                    [
                        'type' => 'select',
                        'name' => 'status',
                        'label' => 'Comment Status',
                        'options' => [
                            '' => 'All',
                            0 => 'Pending',
                            1 => 'Approved',
                            2 => 'Declined',
                        ]
                    ],
                ]"
                :enableSearch="false"
            />
        </div>
    </div>
</x-layout.default>
