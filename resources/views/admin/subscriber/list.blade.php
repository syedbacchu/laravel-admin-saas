<x-layout.default>
@section('title', $pageTitle)

<link rel="stylesheet" href="{{ asset('assets/common/datatables/jquery.dataTables.min.css') }}">
<script src="{{ asset('assets/common/jquery-3.7.0.min.js') }}"></script>
<script src="{{ asset('assets/common/datatables/jquery.dataTables.min.js') }}"></script>

<div class="mt-8 bg-white shadow-xl rounded-2xl p-6 border border-gray-100">

    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-4">
        <h5 class="text-2xl font-bold text-gray-800">
            {{ $pageTitle ?? __('Newsletter Subscribers') }}
        </h5>
    </div>

    <div class="overflow-x-auto">
        <x-common.datatable
            id="subscribersTable"
            ajax="{{ route('subscriber.index') }}"
            :columns="[
                ['data' => 'email', 'title' => 'Email'],
                ['data' => 'status', 'title' => 'Status', 'orderable' => false],
                ['data' => 'created_at', 'title' => 'Subscribed On'],
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
                    'label' => __('Status'),
                    'options' => [
                        '' => __('All'),
                        '1' => __('Active'),
                        '0' => __('Inactive'),
                    ]
                ],
            ]"
        />
    </div>
</div>

<script>
function toggleStatus(id) {
    if (!confirm('Are you sure you want to toggle this subscriber status?')) {
        return;
    }

    fetch('{{ route('subscriber.toggleStatus') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            id: id
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('success', data.message || 'Status updated successfully');
            $('#subscribersTable').DataTable().ajax.reload();
        } else {
            showToast('error', data.message || 'Failed to update status');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('error', 'Failed to update status');
    });
}

function confirmDelete(route, id) {
    if (!confirm('Are you sure you want to delete this subscriber?')) {
        return;
    }

    fetch(route, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            id: id
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('success', data.message || 'Subscriber deleted successfully');
            $('#subscribersTable').DataTable().ajax.reload();
        } else {
            showToast('error', data.message || 'Failed to delete subscriber');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('error', 'Failed to delete subscriber');
    });
}
</script>

</x-layout.default>
