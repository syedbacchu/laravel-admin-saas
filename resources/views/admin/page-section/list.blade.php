<x-layout.default>
@section('title', $pageTitle)
<link rel="stylesheet" href="{{ asset('assets/common/datatables/jquery.dataTables.min.css') }}">
<script src="{{ asset('assets/common/jquery-3.7.0.min.js') }}"></script>
<script src="{{ asset('assets/common/datatables/jquery.dataTables.min.js') }}"></script>

<div class="mt-8 bg-white shadow-xl rounded-2xl p-6 border border-gray-100">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-4">
        <div>
            <h5 class="text-2xl font-bold text-gray-800">{{ $pageTitle ?? __('Page Sections') }}</h5>
            <p class="text-gray-600 mt-1">{{ __('Page') }}: {{ $page->name }}</p>
        </div>

        <div class="flex gap-3">
            <a href="{{ route('pages.sections.create', ['pageId' => $page->id]) }}"
               class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-blue-600 text-white font-medium rounded-lg shadow-md hover:shadow-lg hover:from-indigo-700 hover:to-blue-700 focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 4v16m8-8H4" />
                </svg>
                {{__('Add Section')}}
            </a>
            <a href="{{ route('pages.index') }}"
               class="inline-flex items-center px-5 py-2.5 bg-gray-200 text-gray-700 font-medium rounded-lg hover:bg-gray-300 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                {{__('Back to Pages')}}
            </a>
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table id="itemsTable" class="min-w-full border border-gray-200 rounded-xl text-sm text-gray-700">
            <thead class="bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600 uppercase tracking-wide">{{__('Serial')}}</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600 uppercase tracking-wide">{{__('Component')}}</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600 uppercase tracking-wide">{{__('Visibility')}}</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600 uppercase tracking-wide">{{__('Translations')}}</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600 uppercase tracking-wide">{{__('Created At')}}</th>
                    <th class="px-4 py-3 text-right font-semibold text-gray-600 uppercase tracking-wide">{{__('Actions')}}</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#itemsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: window.location.href,
            type: 'GET',
            data: function(d) {
                d.list_size = 'datatable';
            }
        },
        columns: [
            { data: 'sort_order' },
            { data: 'component' },
            { data: 'visibility_toggle' },
            { data: 'translations_count' },
            { data: 'created_at' },
            { data: 'actions', orderable: false, searchable: false }
        ],
        order: [[2, 'asc']],
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]]
    });
});
</script>
</x-layout.default>
