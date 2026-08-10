<x-layout.default>
@section('title', $pageTitle)

<link rel="stylesheet" href="{{ asset('assets/common/datatables/jquery.dataTables.min.css') }}">
<script src="{{ asset('assets/common/jquery-3.7.0.min.js') }}"></script>
<script src="{{ asset('assets/common/datatables/jquery.dataTables.min.js') }}"></script>

<div class="mt-8 bg-white shadow-xl rounded-2xl p-6 border border-gray-100">

    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-4">
        <h5 class="text-2xl font-bold text-gray-800">
            {{ $pageTitle ?? __('Contact Messages') }}
        </h5>
    </div>

    <div class="overflow-x-auto">
        <x-common.datatable
            id="itemsTable"
            ajax="{{ route('contact.index') }}"
            :columns="[
                ['data' => 'name', 'title' => 'Name'],
                ['data' => 'email', 'title' => 'Email'],
                ['data' => 'phone', 'title' => 'Phone'],
                ['data' => 'subject', 'title' => 'Subject'],
                ['data' => 'status', 'title' => 'Status', 'orderable' => false],
                ['data' => 'created_at', 'title' => 'Date'],
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
                        'pending' => __('Pending'),
                        'replied' => __('Replied'),
                    ]
                ],
            ]"
        />
    </div>
</div>

<!-- View Contact Modal -->
<div x-data="{ show: false, contact: null }"
     x-show="show"
     @keydown.escape.window="show = false"
     class="relative z-50"
     style="display: none;">
    <div x-show="show"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm"
         @click="show = false"></div>

    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div x-show="show"
                 @click.stop
                 class="relative transform overflow-hidden rounded-lg bg-white dark:bg-[#1b2e4b] text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl">
                <div class="bg-white dark:bg-[#1b2e4b] px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                            <h3 class="text-xl font-semibold leading-6 text-gray-900 dark:text-gray-100 mb-4">
                                Contact Details
                            </h3>
                            <div x-show="contact" class="space-y-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-xs uppercase font-semibold text-gray-500">Name</label>
                                        <div class="text-gray-900 dark:text-gray-100" x-text="contact?.name"></div>
                                    </div>
                                    <div>
                                        <label class="text-xs uppercase font-semibold text-gray-500">Email</label>
                                        <div class="text-gray-600 dark:text-gray-400" x-text="contact?.email"></div>
                                    </div>
                                    <div>
                                        <label class="text-xs uppercase font-semibold text-gray-500">Phone</label>
                                        <div class="text-gray-600 dark:text-gray-400" x-text="contact?.phone || '-'"></div>
                                    </div>
                                    <div>
                                        <label class="text-xs uppercase font-semibold text-gray-500">Status</label>
                                        <div>
                                            <span x-show="contact?.status === 'replied'" class="badge bg-success/10 text-success">Replied</span>
                                            <span x-show="contact?.status === 'pending'" class="badge bg-info/10 text-info">Pending</span>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label class="text-xs uppercase font-semibold text-gray-500">Subject</label>
                                    <div class="text-gray-900 dark:text-gray-100" x-text="contact?.subject"></div>
                                </div>
                                <div>
                                    <label class="text-xs uppercase font-semibold text-gray-500">Message</label>
                                    <div class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap bg-gray-50 dark:bg-[#0b1320] p-3 rounded" x-text="contact?.message"></div>
                                </div>
                                <div x-show="contact?.reply_message">
                                    <label class="text-xs uppercase font-semibold text-gray-500">Reply</label>
                                    <div class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap bg-green-50 dark:bg-[#0b1320] p-3 rounded" x-text="contact?.reply_message"></div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        Replied on <span x-text="contact?.replied_at"></span>
                                    </div>
                                </div>
                                <div>
                                    <label class="text-xs uppercase font-semibold text-gray-500">Submitted</label>
                                    <div class="text-gray-600 dark:text-gray-400" x-text="contact?.created_at"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-[#0b1320] px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                    <button type="button"
                            @click="show = false"
                            class="btn btn-outline-primary">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reply Modal -->
<div x-data="{ show: false, contactId: null, replyMessage: '', submitting: false }"
     x-show="show"
     @keydown.escape.window="show = false"
     class="relative z-50"
     style="display: none;">
    <div x-show="show"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm"
         @click="show = false"></div>

    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div x-show="show"
                 @click.stop
                 class="relative transform overflow-hidden rounded-lg bg-white dark:bg-[#1b2e4b] text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl">
                <form @submit.prevent="submitReply">
                    <div class="bg-white dark:bg-[#1b2e4b] px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                                <h3 class="text-xl font-semibold leading-6 text-gray-900 dark:text-gray-100 mb-4">
                                    Reply to Contact
                                </h3>
                                <div class="space-y-4">
                                    <div>
                                        <label class="text-xs uppercase font-semibold text-gray-500">Reply Message</label>
                                        <textarea x-model="replyMessage"
                                                rows="8"
                                                class="form-textarea mt-1 w-full"
                                                placeholder="Type your reply here..."
                                                required></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-[#0b1320] px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 gap-2">
                        <button type="submit"
                                :disabled="submitting || !replyMessage.trim()"
                                class="btn btn-primary"
                                x-show="!submitting">
                            Send Reply
                        </button>
                        <button type="button"
                                disabled
                                class="btn btn-primary"
                                x-show="submitting">
                            Sending...
                        </button>
                        <button type="button"
                                @click="show = false; replyMessage = ''"
                                class="btn btn-outline-primary">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function showToast(type, message) {
    // Simple alert fallback with type indication
    let prefix = '';
    if (type === 'success') prefix = '✓ ';
    if (type === 'error') prefix = '✗ ';
    if (type === 'warning') prefix = '⚠ ';

    alert(prefix + message);
}

function viewContact(id) {
    fetch('{{ route('contact.show') }}?id=' + id)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const modal = document.querySelector('[x-data*="show: false, contact: null"]');
                if (modal) {
                    const alpineData = Alpine.$data(modal);
                    alpineData.contact = {
                        name: data.data.name,
                        email: data.data.email,
                        phone: data.data.phone,
                        subject: data.data.subject,
                        message: data.data.message,
                        status: data.data.status,
                        reply_message: data.data.reply_message,
                        replied_at: data.data.replied_at ? new Date(data.data.replied_at).toLocaleString() : null,
                        created_at: new Date(data.data.created_at).toLocaleString()
                    };
                    alpineData.show = true;
                }
            } else {
                showToast('error', data.message || 'Failed to load contact details');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('error', 'Failed to load contact details');
        });
}

function openReplyModal(id) {
    const modal = document.querySelector('[x-data*="show: false, contactId: null"]');
    if (modal) {
        const alpineData = Alpine.$data(modal);
        alpineData.contactId = id;
        alpineData.replyMessage = '';
        alpineData.show = true;
    }
}

function submitReply(event) {
    const form = event.target;
    const modal = document.querySelector('[x-data*="show: false, contactId: null"]');
    if (!modal) return;

    const alpineData = Alpine.$data(modal);

    // Store the base route URL
    const replyUrl = '{{ route('contact.reply', ['id' => '__ID__']) }}';
    const url = replyUrl.replace('__ID__', alpineData.contactId);

    alpineData.submitting = true;

    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            reply_message: alpineData.replyMessage
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Handle different success messages
            let message = data.message || 'Reply sent successfully';
            let messageType = 'success';

            if (message.includes('not configured')) {
                message = 'Reply saved! (Email not configured - go to Settings to setup email)';
                messageType = 'warning';
            } else if (message.includes('email could not be sent')) {
                message = 'Reply saved! (Email failed - check your mail settings)';
                messageType = 'warning';
            }

            showToast(messageType, message);
            alpineData.show = false;
            $('#itemsTable').DataTable().ajax.reload();
        } else {
            showToast('error', data.message || 'Failed to send reply');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('error', 'Failed to send reply');
    })
    .finally(() => {
        alpineData.submitting = false;
    });
}
</script>

</x-layout.default>