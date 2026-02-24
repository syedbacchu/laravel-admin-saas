<x-layout.default>
@section('title', $pageTitle)
<link rel="stylesheet" href="{{ asset('assets/common/datatables/jquery.dataTables.min.css') }}">
<script src="{{ asset('assets/common/jquery-3.7.0.min.js') }}"></script>
<script src="{{ asset('assets/common/datatables/jquery.dataTables.min.js') }}"></script>

    <div class="mt-8 bg-white shadow-xl rounded-2xl p-6 border border-gray-100">
    <!-- Header -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-4">
            <div>
                <h5 class="text-2xl font-bold text-gray-800">{{ $pageTitle }}</h5>
                <p>{{ __('Audit Settings control whether changes made to each model are recorded in the audit log.') }}</p>
            </div>

            <a href="{{ route('audit.resetModel') }}"
                class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-blue-600 text-white font-medium rounded-lg shadow-md hover:shadow-lg hover:from-indigo-700 hover:to-blue-700 focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" fill="none"
                viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m2-2H7m5-3v3" />
            </svg>
                Reset All Settings
            </a>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table id="sliderTable" class="min-w-full border border-gray-200 rounded-xl text-sm text-gray-700">
                <thead class="bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600 uppercase tracking-wide">{{ __('Sl') }}</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600 uppercase tracking-wide">Name</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600 uppercase tracking-wide">Audit Status</th>
                    </tr>
                </thead>

            </table>
        </div>
    </div>


    <script>
    $(document).ready(function() {
        $('#sliderTable').DataTable({
            processing: true,
            serverSide: true,
            searching: true,
            ajax: "{{ route('audit.settings') }}",
            columns: [
                {
                    data: null,
                    render: function (data, type, row, meta) {
                        return meta.row + 1; // auto serial number
                    },
                    orderable: false,
                    searchable: false
                },
                { data: 'name', name: 'name' ,orderable: true, searchable: true},
                { data: 'status', name: 'status' },
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


    // toggle event
    $(document).on('change', '.toggle-status', function () {
        let model = $(this).data('model');
        let enabled = $(this).is(':checked');

        $.post('{{ route("audit.updateModel") }}', {
            _token: '{{ csrf_token() }}',
            model: model,
            enabled: enabled
        }, function (res) {
            console.log(res);
            if (res.success == true) {
                toastr.success(res.message);
            } else {
                toastr.error(res.message);
            }
        });
    });
    </script>

</x-layout.default>
