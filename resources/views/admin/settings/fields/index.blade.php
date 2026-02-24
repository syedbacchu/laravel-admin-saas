<x-layout.default>
    <div class="panel mt-6 bg-white shadow-xl rounded-2xl p-6 border border-gray-100">

        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold">Setting Fields</h1>

            <a href="{{ route('settings.fields.create') }}"
               class="btn btn-primary">
                + Add New Field
            </a>
        </div>

        <div class="panel bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="flex flex-col md:flex-row"
                 x-data="{ tab: '{{ array_key_first($fields->toArray()) }}' }">

                <!-- LEFT TABS -->
                <aside class="w-full md:w-56 border-b md:border-b-0 md:border-r border-gray-200">
                    <ul class="p-4 space-y-1">
                        @foreach($fields as $group => $groupFields)
                            <li>
                                <button type="button"
                                        @click="tab = '{{ $group }}'"
                                        class="w-full flex items-center gap-3 px-4 py-2 rounded-lg text-sm font-medium transition"
                                        :class="tab === '{{ $group }}'
                                            ? 'bg-success/10 text-success'
                                            : 'text-gray-600 hover:bg-gray-100'">

                                    <span class="h-2 w-2 rounded-full"
                                          :class="tab === '{{ $group }}'
                                            ? 'bg-success'
                                            : 'bg-transparent'">
                                    </span>

                                    {{ ucfirst(str_replace('-', ' ', $group)) }}
                                </button>
                            </li>
                        @endforeach
                    </ul>
                </aside>

                <!-- CONTENT -->
                <section class="flex-1 p-6">

                    @foreach($fields as $group => $groupFields)
                        <div x-show="tab === '{{ $group }}'" x-cloak>

                            <div class="overflow-x-auto">
                                <table class="table table-bordered w-full sortable-table"
                                       data-group="{{ $group }}">
                                    <thead>
                                    <tr class="bg-gray-100">
                                        <th width="40">☰</th>
                                        <th>Label</th>
                                        <th>Slug</th>
                                        <th>Type</th>
                                        <th width="140">Action</th>
                                    </tr>
                                    </thead>

                                    <tbody class="sortable-body">
                                    @foreach($groupFields as $field)
                                        <tr data-id="{{ $field->id }}">
                                            <td class="cursor-move text-center">☰</td>
                                            <td>{{ $field->label }}</td>
                                            <td>
                                                <code>{{ $field->slug }}</code>
                                            </td>
                                            <td>
                                                {{ ucfirst($field->type) }}
                                            </td>
                                            <td>
                                                <div class="flex gap-2">
                                                    <a href="{{ route('settings.fields.edit', $field) }}"
                                                       title="{{__('Edit')}}"
                                                       class="inline-flex items-center justify-center w-8 h-8 text-blue-600 hover:text-white hover:bg-blue-600 border border-blue-600 rounded-lg transition duration-200"
                                                    >
                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                             class="w-4 h-4"
                                                             fill="none"
                                                             viewBox="0 0 24 24"
                                                             stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                  d="M15.232 5.232l3.536 3.536M9 11l6.232-6.232a2 2 0 112.828 2.828L11.828 13.828a2 2 0 01-.828.486L7 15l1.686-4a2 2 0 01.314-.768z" />
                                                        </svg>
                                                    </a>

                                                    <form action="{{ route('settings.fields.delete', $field) }}"
                                                          method="POST"
                                                          onsubmit="return confirm('Delete this field?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button  title="{{__('Delete')}}"
                                                                class="inline-flex items-center px-3 py-1.5 text-sm font-semibold text-red-600 hover:text-white hover:bg-red-600 border border-red-600 rounded-lg transition duration-200">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5-4h4a1 1 0 011 1v1H9V4a1 1 0 011-1z" />
                                                            </svg>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>

                                </table>
                            </div>

                        </div>
                    @endforeach

                </section>
            </div>
        </div>
    </div>
</x-layout.default>
