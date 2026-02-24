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
            <p class="text-gray-600 text-sm">
                {{ __('Audit Settings control whether changes made to each model are recorded in the audit log.') }}
            </p>
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <x-common.datatable
            id="itemsTable"
            ajax="{{ route('audit.logs') }}"
            :columns="[
                    ['data' => 'DT_RowIndex', 'title' => 'Sl', 'orderable' => false,'searchable' => false],
                    ['data' => 'user', 'name' => 'user', 'title' => 'User'],
                    ['data' => 'event', 'name' => 'event', 'title' => 'Event'],
                    ['data' => 'model_type', 'name' => 'model_type', 'title' => 'Model'],
                    ['data' => 'ip_address', 'name' => 'ip_address', 'title' => 'Ip Address'],
                    ['data' => 'created_at', 'name' => 'created_at', 'title' => 'Created at'],
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
                        'name' => 'model_type',
                        'label' => 'Model',
                        'options' => [
                            '' => 'All',
                            'App\Models\User' => 'User',
                            'App\Models\AdminSettings' => 'Admin Settings',
                        ]
                    ],
                    [
                        'type' => 'select',
                        'name' => 'event',
                        'label' => 'Event',
                        'options' => [
                            '' => 'All',
                            'created' => 'Created',
                            'updated' => 'Updated',
                        ]
                    ],
                ]"
            :enableSearch="false"
        />
    </div>
</div>

<!-- ✅ Tailwind Modal (pure jQuery based) -->
<div id="auditModal" class="hidden fixed inset-0 bg-black bg-opacity-40 z-50 flex items-center justify-center">
    <div class="bg-white w-full max-w-3xl rounded-xl shadow-lg p-6 relative">
        <button id="closeModal" class="absolute top-3 right-3 text-gray-500 hover:text-gray-700 text-xl">
            ✖
        </button>

        <h2 class="text-xl font-semibold mb-4">Audit Details</h2>

        <div id="auditDetails" class="text-sm text-gray-700 space-y-2 overflow-y-auto max-h-[75vh]"></div>
    </div>
</div>

<script>
$(document).ready(function() {
    // ✅ DataTable initialization
    {{--let table = $('#auditTable').DataTable({--}}
    {{--    processing: true,--}}
    {{--    serverSide: true,--}}
    {{--    ajax: "{{ route('audit.logs') }}",--}}
    {{--    columns: [--}}
    {{--        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },--}}
    {{--        { data: 'user', name: 'user' },--}}
    {{--        { data: 'event', name: 'event' },--}}
    {{--        { data: 'model', name: 'model_type' },--}}
    {{--        { data: 'ip_address', name: 'ip_address' },--}}
    {{--        { data: 'created_at', name: 'created_at' },--}}
    {{--        { data: 'actions', name: 'actions', orderable: false, searchable: false },--}}
    {{--    ],--}}
    {{--    pageLength: 10,--}}
    {{--    order: [[0, 'desc']],--}}
    {{--    language: {--}}
    {{--        search: "_INPUT_",--}}
    {{--        searchPlaceholder: "Search ...",--}}
    {{--        paginate: { next: '→', previous: '←' }--}}
    {{--    }--}}
    {{--});--}}

    // ✅ View details button click → open modal
    $(document).on('click', '.view-details', function () {
        let id = $(this).data('id');
        $.get("{{ url('/admin/audit/log') }}/" + id, function (data) {
            let html = `
                <p><strong>User:</strong> ${data.user}</p>
                <p><strong>Event:</strong> ${data.event}</p>
                <p><strong>Model:</strong> ${data.model_type} (ID: ${data.model_id})</p>
                <p><strong>IP:</strong> ${data.ip_address || ''}</p>
                <p><strong>Date:</strong> ${data.created_at}</p>
                <hr class="my-2">
                <h6 class="font-semibold">Old Values:</h6>
                <pre class="bg-gray-100 p-3 rounded-md text-xs overflow-x-auto">${JSON.stringify(data.old_values, null, 2)}</pre>
                <h6 class="font-semibold mt-3">New Values:</h6>
                <pre class="bg-gray-100 p-3 rounded-md text-xs overflow-x-auto">${JSON.stringify(data.new_values, null, 2)}</pre>
            `;
            $('#auditDetails').html(html);
            $('#auditModal').removeClass('hidden').hide().fadeIn(200);
        });
    });

    // ✅ Close modal
    $('#closeModal').on('click', function() {
        $('#auditModal').fadeOut(200, function() {
            $(this).addClass('hidden');
        });
    });

    // ✅ Close when clicking outside the modal box
    $(document).on('click', function(e) {
        if ($(e.target).is('#auditModal')) {
            $('#auditModal').fadeOut(200, function() {
                $(this).addClass('hidden');
            });
        }
    });
});
</script>

</x-layout.default>
