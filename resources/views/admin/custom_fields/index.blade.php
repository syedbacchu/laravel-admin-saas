<x-layout.default>
    @section('title', $pageTitle)
    @php
        $firstModel = $models[0] ?? null;
        $firstKey   = $firstModel ? md5($firstModel) : '';
    @endphp
    <div class="mt-8 bg-white shadow-xl rounded-2xl p-6 border border-gray-100">

        <div class="flex items-center justify-between mb-6">
            <h5 class="text-2xl font-bold text-gray-800">{{ $pageTitle }}</h5>
        </div>

        <div class="panel"
             x-data="{ tab: '{{ $firstKey }}' }"
             x-init="loadFields(@js($firstModel), '{{ $firstKey }}')">

            <div class="flex flex-col sm:flex-row gap-6">

                <!-- LEFT: MODEL LIST -->
                <div class="sm:w-1/5">
                    <ul class="space-y-2">
                        @foreach($models as $model)
                            @php
                                $key = md5($model);
                                $short = class_basename($model);
                            @endphp
                            <li>
                                <a href="javascript:;"
                                   @click="tab='{{ $key }}'; loadFields(@js($model), '{{ $key }}')"
                                   :class="{ '!bg-blue-800 text-white': tab === '{{ $key }}' }"
                                   class="p-3 py-2 block rounded-md hover:bg-blue-700 hover:text-white transition-all">
                                    {{ $short }}
                                </a>
                            </li>

                        @endforeach
                    </ul>
                </div>

                <!-- RIGHT PANEL -->
                <div class="flex-1">

                    @foreach($models as $model)
                        @php $key = md5($model); @endphp

                        <template x-if="tab === '{{ $key }}'">
                            <div class="space-y-6">

                                <h4 class="text-xl font-bold">
                                    {{ class_basename($model) }} {{__('Custom Fields')}}
                                </h4>

                                <!-- EXISTING FIELDS -->
                                <div class="border rounded-lg p-4">
                                    <h5 class="font-semibold mb-3">{{__('Existing Fields')}}</h5>

                                    <table class="w-full text-sm">
                                        <thead>
                                        <tr class="border-b">
                                            <th>{{__('Label')}}</th>
                                            <th>{{__('Name')}}</th>
                                            <th>{{__('Type')}}</th>
                                            <th>{{__('Required')}}</th>
                                            <th>{{__('Serial')}}</th>
                                            <th>{{__('Show In')}}</th>
                                            <th>{{__('Action')}}</th>
                                        </tr>
                                        </thead>
                                        <tbody id="field_list_{{ $key }}">
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-gray-400">
                                                {{__('Select model to load fields')}}
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- ADD FIELD -->
                                <div class="border rounded-lg p-4 bg-gray-50">
                                    <h3 class="font-bold mb-3 text-2xl">{{__('Add New Field')}}</h3>

                                    <form data-form="{{ $key }}" onsubmit="return saveField(this, @js($model), '{{ $key }}')">
                                        @csrf
                                        <input type="hidden" name="module" value="{{ $model }}">
                                        <input type="hidden" name="status" value="1">
                                        <input type="hidden" name="edit_id" id="edit_id_{{ $key }}">

                                        <div>
                                            <label>Label *</label>
                                            <input type="text" name="label" class="form-input w-full" required>
                                        </div>

                                        <div>
                                            <label>Field Name</label>
                                            <input type="text" name="name" class="form-input w-full" placeholder="auto-generate">
                                        </div>

                                        <div x-data="{ show: false }">
                                            <label>Type *</label>

                                            <select name="type" class="form-select w-full"
                                                    @change="show = ['select','radio','checkbox'].includes($event.target.value)">
                                                <option value="text">Input</option>
                                                <option value="textarea">Textarea</option>
                                                <option value="number">Number</option>
                                                <option value="checkbox">Checkbox</option>
                                                <option value="radio">Radio</option>
                                                <option value="select">Select</option>
                                                <option value="file">File</option>
                                            </select>

                                            <div class="mt-3 options-container"
                                                 x-show="show"
                                                 x-transition>
                                                <label>Options (comma separated)</label>
                                                <input type="text" name="options" class="form-input w-full"
                                                       placeholder="red, green, blue">
                                            </div>
                                        </div>


                                        <div>
                                            <label>Required</label>
                                            <select name="is_required" class="form-select w-full">
                                                <option value="0">No</option>
                                                <option value="1">Yes</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label>Default Value</label>
                                            <input type="text" name="default_value" class="form-input w-full">
                                        </div>

                                        <div>
                                            <label>Validation Rules</label>
                                            <input type="text" name="validation_rules" class="form-input w-full">
                                        </div>
                                        <div>
                                            <label>Serial</label>
                                            <input type="number" name="sort_order" value="0" class="form-input w-full">
                                        </div>
                                        <div>
                                            <label>Show In *</label>
                                            <select name="show_in[]" class="form-select w-full" multiple required x-ref="showIn">
                                                <option value="create">Create Form</option>
                                                <option value="update">Update Form</option>
                                                <option value="api">API</option>
                                            </select>
                                        </div>
                                        <div class="mt-4">
                                            <button class="btn btn-secondary px-4 py-2">
                                                {{__('Save Field')}}
                                            </button>
                                        </div>
                                    </form>
                                </div>

                            </div>
                        </template>
                    @endforeach

                </div>
            </div>
        </div>
    </div>

    <!-- JS -->
    <script>
        function saveField(form, module, key) {
            const formData = new FormData(form);

            // Frontend validation: if type requires options, ensure it's not empty
            const type = form.querySelector('[name="type"]').value;
            const options = form.querySelector('[name="options"]')?.value || '';
            if(['select','radio','checkbox'].includes(type) && !options.trim()){
                toastr.error('Options are required for select, radio, or checkbox type.');
                return false;
            }

            fetch("{{ route('customField.store') }}", {
                method: "POST",
                headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                body: formData
            })
                .then(res => res.json())
                .then(res => {
                    if(res.success){
                        form.reset();
                        loadFields(module, key);
                        form.querySelector('[name="label"]').focus();
                        toastr.success(res.message);
                    } else {
                        toastr.error(res.message);
                    }
                });

            return false;
        }


        function loadFields(module, key) {
            fetch("{{ route('customField.list') }}?module=" + encodeURIComponent(module))
                .then(res => res.json())
                .then(res => {
                    const tbody = document.getElementById('field_list_' + key);
                    tbody.innerHTML = '';
                    if(!res.data || !res.data.length){
                        tbody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-gray-400">No custom fields found</td></tr>`;
                        return;
                    }

                    res.data.forEach(field => {
                        tbody.innerHTML += `
<tr class="border-b">
<td>${field.label}</td>
<td>${field.name}</td>
<td>${field.type}</td>
<td>${field.is_required ? 'Yes' : 'No'}</td>
<td>${field.sort_order}</td>
<td>${field.show_in ? field.show_in.join(', ') : ''}</td>
<td>
<button class="text-primary edit-btn"
    data-id="${field.id}"
    data-module="${module}"
    data-key="${key}">Edit</button>
<button class="text-red-600 delete-btn"
            data-id="${field.id}"
            data-module="${module}"
            data-key="${key}">Delete</button>
</td>
</tr>`;
                    });
                });
        }

        document.addEventListener('click', function(e){
            if(e.target.matches('.edit-btn')){
                const btn = e.target;
                editField(btn.dataset.id, btn.dataset.module, btn.dataset.key);
            }
        });

        function editField(id, module, key) {
            fetch("{{ route('customField.list') }}?module=" + encodeURIComponent(module))
                .then(res => res.json())
                .then(res => {
                    const field = res.data.find(f => f.id == id);
                    if (!field) return;

                    const form = document.querySelector(`[data-form="${key}"]`);
                    if (!form) return;

                    // Fill basic fields
                    form.querySelector('[name="edit_id"]').value = field.id;
                    form.querySelector('[name="label"]').value = field.label;
                    form.querySelector('[name="name"]').value = field.name;
                    form.querySelector('[name="type"]').value = field.type;
                    form.querySelector('[name="sort_order"]').value = field.sort_order;
                    form.querySelector('[name="is_required"]').value = field.is_required ? 1 : 0;
                    form.querySelector('[name="default_value"]').value = field.default_value ?? '';
                    form.querySelector('[name="validation_rules"]').value = field.validation_rules ?? '';

                    // Show options input if needed
                    const optionsContainer = form.querySelector('.options-container'); // just a normal class
                    const optionsInput = form.querySelector('[name="options"]');
                    if (['select', 'radio', 'checkbox'].includes(field.type)) {
                        if (optionsContainer) optionsContainer.style.display = 'block';
                        optionsInput.value = field.options ? field.options.join(', ') : '';
                    } else {
                        if (optionsContainer) optionsContainer.style.display = 'none';
                        optionsInput.value = '';
                    }
// Fill show_in multi-select
                    const showInSelect = form.querySelector('[name="show_in[]"]');
                    if (field.show_in && showInSelect) {
                        [...showInSelect.options].forEach(opt => {
                            opt.selected = field.show_in.includes(opt.value);
                        });
                    }

                    form.scrollIntoView({ behavior: 'smooth' });
                });
        }


        document.addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('delete-btn')) {
                const id = e.target.getAttribute('data-id');
                const module = e.target.getAttribute('data-module');
                const key = e.target.getAttribute('data-key');

                if (!confirm('Are you sure you want to delete this field?')) return;

                fetch(`/admin/custom-fields/delete/${id}`, { method: 'GET' })
                    .then(res => res.json())
                    .then(res => {
                        if(res.success) {
                            Swal.fire("Deleted!", res.message ?? "Deleted successfully.", "success");
                            // reload the table
                            loadFields(module, key);
                        } else {
                            alert('Delete failed: ' + (res.message || 'Unknown error'));
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        Swal.fire("Error!", "Delete failed.", "error");
                    });
            }
        });


    </script>
</x-layout.default>
