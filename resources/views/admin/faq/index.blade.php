<x-layout.default>
@section('title', $pageTitle)
<link rel="stylesheet" href="{{ asset('assets/common/datatables/jquery.dataTables.min.css') }}">
<script src="{{ asset('assets/common/jquery-3.7.0.min.js') }}"></script>
<script src="{{ asset('assets/common/datatables/jquery.dataTables.min.js') }}"></script>

    <div class="mt-8 bg-white shadow-xl rounded-2xl p-6 border border-gray-100">
    <!-- Header -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-4">
            <h5 class="text-2xl font-bold text-gray-800">{{ $pageTitle ?? __('Title') }}</h5>

            <a href="{{ route('faq.create') }}"
            class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-blue-600 text-white font-medium rounded-lg shadow-md hover:shadow-lg hover:from-indigo-700 hover:to-blue-700 focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4v16m8-8H4" />
                </svg>
                {{__('Create Faq ')}}
            </a>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table id="itemsTable" class="min-w-full border border-gray-200 rounded-xl text-sm text-gray-700">
                <thead class="bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600 uppercase tracking-wide">{{__('Category Name')}}</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600 uppercase tracking-wide">{{__('Question')}}</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600 uppercase tracking-wide">{{__('Answer')}}</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600 uppercase tracking-wide">{{__('Status')}}</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-600 uppercase tracking-wide">{{__('Actions')}}</th>
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
            searching: true,
            ajax: "{{ route('faq.list') }}",
            columns: [
                { data: 'category_name', name: 'category_name' }, 
                { data: 'question', name: 'question' },
                { data: 'answer', name: 'answer', orderable: false, searchable: false },
                { data: 'status', name: 'status', orderable: false, searchable: false },
                { data: 'actions', name: 'actions', orderable: false, searchable: false }
            ],
            layout: {
                topStart: 'search',
                bottomEnd: 'paging',
            },
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search ...",
                paginate: {
                    next: '→',
                    previous: '←'
                }
            },
            classes: {
                table: 'min-w-full divide-y divide-gray-200 border border-gray-100 rounded-lg shadow-sm',
                thead: 'bg-gray-100 text-gray-700 uppercase text-xs font-semibold',
                tbody: 'divide-y divide-gray-100',
                tr: 'hover:bg-gray-50 transition-colors duration-150',
                th: 'px-4 py-3 text-left',
                td: 'px-4 py-3 text-sm text-gray-700'
            },
            pageLength: 10,
            order: [[0, 'desc']],
        });
    });
    </script>


</x-layout.default>
