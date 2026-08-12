<x-layout.default>
    @section('title', $pageTitle)

    <div class="panel mt-6">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">
                {{ $pageTitle }}
            </h1>
            <a href="{{ route('component.list') }}"
                class="text-sm text-blue-600 hover:text-blue-800">
                {{__('Back to Components')}}
            </a>
        </div>

        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <!-- Component Header -->
            <div class="bg-gradient-to-r from-indigo-600 to-blue-600 px-6 py-4">
                <h2 class="text-xl font-bold text-white">{{ $component->name }}</h2>
                <p class="text-indigo-200 text-sm mt-1">{{ __('Slug:') }} {{ $component->slug }}</p>
            </div>

            <!-- Component Details -->
            <div class="p-6">
                <!-- Description -->
                @if($component->description)
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">{{ __('Description') }}</h3>
                    <p class="text-gray-600">{{ $component->description }}</p>
                </div>
                @endif

                <!-- Status -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">{{ __('Status') }}</h3>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                        {{ $component->status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $component->status ? __('Active') : __('Inactive') }}
                    </span>
                </div>

                <!-- Fields Count -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">{{ __('Fields') }}</h3>
                    <p class="text-gray-600">{{ $component->fields->count() }} {{ __('field(s) configured') }}</p>
                </div>

                <!-- Fields Preview -->
                @if($component->fields->count() > 0)
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">{{ __('Field Structure') }}</h3>
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <ul class="space-y-2">
                            @foreach($component->parentFields as $field)
                            <li class="flex items-start">
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-indigo-100 text-indigo-600 text-xs font-bold mr-3 mt-0.5">
                                    {{ $loop->iteration }}
                                </span>
                                <div>
                                    <span class="font-medium text-gray-800">{{ $field->label }}</span>
                                    <span class="text-gray-500 text-sm ml-2">({{ $field->field_type }})</span>
                                    @if($field->is_required)
                                    <span class="text-red-500 text-xs ml-1">*</span>
                                    @endif
                                    @if($field->children->count() > 0)
                                    <ul class="ml-6 mt-1 space-y-1">
                                        @foreach($field->children as $child)
                                        <li class="text-sm text-gray-600">
                                            └── {{ $child->label }} ({{ $child->field_type }})
                                        </li>
                                        @endforeach
                                    </ul>
                                    @endif
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @endif

                <!-- Metadata -->
                <div class="border-t border-gray-200 pt-6">
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-500">{{ __('Created:') }}</span>
                            <span class="text-gray-700 ml-2">{{ $component->created_at ? $component->created_at->format('M d, Y') : '-' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">{{ __('Updated:') }}</span>
                            <span class="text-gray-700 ml-2">{{ $component->updated_at ? $component->updated_at->format('M d, Y') : '-' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end mt-6 pt-6 border-t border-gray-200">
                    <a href="{{ route('component.fields', $component->id) }}"
                        class="px-4 py-2 bg-gradient-to-r from-indigo-600 to-blue-600 text-white rounded-lg hover:from-indigo-700 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 mr-3">
                        {{ __('Manage Fields') }}
                    </a>
                    <a href="{{ route('component.edit', $component->id) }}"
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500">
                        {{ __('Edit Component') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-layout.default>