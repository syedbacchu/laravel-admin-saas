{{-- Clean child field display within repeaters - just presentation --}}
@php
    $isRequired = $childField['is_required'] ? 'required' : '';
@endphp

<div class="bg-white border border-gray-200 rounded p-3">
    <label class="block text-sm font-medium text-gray-700 mb-1">
        {{ $childField['label'] }}
        @if($childField['is_required'])
            <span class="text-red-500">*</span>
        @endif
    </label>

    {{-- Dynamic field rendering with Alpine.js binding --}}
    @switch($childField['field_type'])
        @case('text')
            <input type="text"
                   :name="'data[{{ $parentFieldName }}][' + index + '][{{ $childField['name'] }}]'"
                   x-model="items[index]['{{ $childField['name'] }}']"
                   placeholder="{{ $childField['label'] }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                   {{ $isRequired }}>
            @break

        @case('textarea')
            <textarea :name="'data[{{ $parentFieldName }}][' + index + '][{{ $childField['name'] }}]'"
                      x-model="items[index]['{{ $childField['name'] }}']"
                      rows="2"
                      placeholder="{{ $childField['label'] }}"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                      {{ $isRequired }}></textarea>
            @break

        @case('number')
            <input type="number"
                   :name="'data[{{ $parentFieldName }}][' + index + '][{{ $childField['name'] }}]'"
                   x-model="items[index]['{{ $childField['name'] }}']"
                   placeholder="{{ $childField['label'] }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                   {{ $isRequired }}>
            @break

        @case('email')
            <input type="email"
                   :name="'data[{{ $parentFieldName }}][' + index + '][{{ $childField['name'] }}]'"
                   x-model="items[index]['{{ $childField['name'] }}']"
                   placeholder="{{ $childField['label'] }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                   {{ $isRequired }}>
            @break

        @case('url')
            <input type="url"
                   :name="'data[{{ $parentFieldName }}][' + index + '][{{ $childField['name'] }}]'"
                   x-model="items[index]['{{ $childField['name'] }}']"
                   placeholder="https://"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                   {{ $isRequired }}>
            @break

        @case('wysiwyg')
            <textarea :name="'data[{{ $parentFieldName }}][' + index + '][{{ $childField['name'] }}]'"
                      x-model="items[index]['{{ $childField['name'] }}']"
                      rows="3"
                      placeholder="{{ $childField['label'] }}"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                      {{ $isRequired }}></textarea>
            @break

        @case('color')
            <input type="color"
                   :name="'data[{{ $parentFieldName }}][' + index + '][{{ $childField['name'] }}]'"
                   x-model="items[index]['{{ $childField['name'] }}']"
                   class="w-12 h-8 rounded cursor-pointer border-0"
                   {{ $isRequired }}>
            @break

        @case('date')
            <input type="date"
                   :name="'data[{{ $parentFieldName }}][' + index + '][{{ $childField['name'] }}]'"
                   x-model="items[index]['{{ $childField['name'] }}']"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                   {{ $isRequired }}>
            @break

        @case('datetime')
            <input type="datetime-local"
                   :name="'data[{{ $parentFieldName }}][' + index + '][{{ $childField['name'] }}]'"
                   x-model="items[index]['{{ $childField['name'] }}']"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                   {{ $isRequired }}>
            @break

        @case('select')
            @php $options = $childField['config']['options'] ?? []; @endphp
            <select :name="'data[{{ $parentFieldName }}][' + index + '][{{ $childField['name'] }}]'"
                    x-model="items[index]['{{ $childField['name'] }}']"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                    {{ $isRequired }}>
                <option value="">{{__('Select an option')}}</option>
                @foreach($options as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
            @break

        @case('checkbox')
            @php $options = $childField['config']['options'] ?? []; @endphp
            <div class="space-y-1">
                @foreach($options as $value => $label)
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox"
                               :name="'data[{{ $parentFieldName }}][' + index + '][{{ $childField['name'] }}'][]"
                               :value="'{{ $value }}'"
                               x-model="items[index]['{{ $childField['name'] }}']"
                               {{ $childField['is_required'] ? 'required' : '' }}
                               class="w-4 h-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-500">
                        <span class="text-sm">{{ $label }}</span>
                    </label>
                @endforeach
            </div>
            @break

        @case('radio')
            @php $options = $childField['config']['options'] ?? []; @endphp
            <div class="space-y-1">
                @foreach($options as $value => $label)
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio"
                               :name="'data[{{ $parentFieldName }}][' + index + '][{{ $childField['name'] }}]'"
                               :value="'{{ $value }}'"
                               x-model="items[index]['{{ $childField['name'] }}']"
                               class="w-4 h-4 text-blue-600 focus:ring-2 focus:ring-blue-500"
                               {{ $isRequired }}>
                        <span class="text-sm">{{ $label }}</span>
                    </label>
                @endforeach
            </div>
            @break

        @case('image')
            @php
                $fieldId = 'repeater_image_' . $parentFieldName . '_' . $childField['name'];
                $existingValue = $childField['value'] ?? '';
            @endphp

            <div x-data="{
                fileUrl: items[index]['{{ $childField['name'] }}'] || '',
                filePreview: items[index]['{{ $childField['name'] }}'] || '',
                get callbackName() {
                    return '{{ $fieldId }}_' + index;
                },
                init() {
                    this.$nextTick(() => {
                        const handler = (e) => {
                            this.fileUrl = e.detail.url;
                            this.filePreview = e.detail.url;
                            items[index]['{{ $childField['name'] }}'] = e.detail.url;
                        };
                        window.addEventListener(this.callbackName, handler);
                        this._cleanup = () => window.removeEventListener(this.callbackName, handler);
                    });
                },
                destroy() {
                    if (this._cleanup) this._cleanup();
                }
            }">
                {{-- Hidden input bound to repeater data --}}
                <input type="hidden"
                       :name="'data[{{ $parentFieldName }}][' + index + '][{{ $childField['name'] }}]'"
                       x-model="items[index]['{{ $childField['name'] }}']"
                       {{ $isRequired }}>

                <div class="space-y-2">
                    @if($childField['label'])
                    <label class="font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2">
                        @if($childField['is_required'])
                        <span class="text-red-500">*</span>
                        @endif
                        {{ __($childField['label']) }}
                    </label>
                    @endif

                    {{-- Upload/Preview Area --}}
                    <div class="file-upload-container">
                        <div class="relative group file-upload-box"
                             @click="$dispatch('open-file-manager', { callback: callbackName })">

                            <div class="upload-content w-full aspect-[4/3]">
                                {{-- Image Preview --}}
                                <template x-if="filePreview">
                                    <div class="w-full h-full rounded-lg overflow-hidden border-2 border-gray-200 dark:border-gray-700 cursor-pointer hover:border-blue-400 dark:hover:border-blue-600 transition-all duration-300 relative">
                                        <img :src="filePreview"
                                             class="w-full h-full object-cover">

                                        <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                                            <div class="text-center text-white">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                                <span class="text-sm font-medium">Change Image</span>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                {{-- No Image State --}}
                                <template x-if="!filePreview">
                                    <div class="w-full h-full rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 cursor-pointer hover:border-blue-400 dark:hover:border-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-all duration-300 flex flex-col items-center justify-center p-6">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-gray-400 dark:text-gray-500 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">No image selected</p>
                                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Click to choose</p>
                                    </div>
                                </template>

                                {{-- Remove Button --}}
                                <template x-if="filePreview">
                                    <button type="button"
                                            @click.stop="items[index]['{{ $childField['name'] }}'] = ''; filePreview = '';"
                                            class="absolute -top-2 -right-2 w-7 h-7 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center shadow-lg transition-all duration-300 hover:scale-110 z-10"
                                            title="Remove image">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @break

        @case('file')
            <input type="text"
                   :name="'data[{{ $parentFieldName }}][' + index + '][{{ $childField['name'] }}]'"
                   x-model="items[index]['{{ $childField['name'] }}']"
                   placeholder="https://example.com/file.pdf"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                   {{ $isRequired }}>
            @break

        @case('responsive_image')
            @php
                $desktopFieldId = 'resp_desktop_' . $parentFieldName . '_' . $childField['name'];
                $mobileFieldId = 'resp_mobile_' . $parentFieldName . '_' . $childField['name'];
            @endphp

            <div class="space-y-4">
                {{-- Desktop Image --}}
                <div x-data="{
                    fileUrl: items[index]['{{ $childField['name'] }}']['desktop'] || '',
                    filePreview: items[index]['{{ $childField['name'] }}']['desktop'] || '',
                    get callbackName() {
                        return '{{ $desktopFieldId }}_' + index;
                    },
                    init() {
                        this.$nextTick(() => {
                            const handler = (e) => {
                                this.fileUrl = e.detail.url;
                                this.filePreview = e.detail.url;
                                items[index]['{{ $childField['name'] }}']['desktop'] = e.detail.url;
                            };
                            window.addEventListener(this.callbackName, handler);
                            this._cleanup = () => window.removeEventListener(this.callbackName, handler);
                        });
                    },
                    destroy() {
                        if (this._cleanup) this._cleanup();
                    }
                }">
                    {{-- Hidden input --}}
                    <input type="hidden"
                           :name="'data[{{ $parentFieldName }}][' + index + '][{{ $childField['name'] }}][desktop]'"
                           x-model="items[index]['{{ $childField['name'] }}']['desktop']"
                           {{ $isRequired }}>

                    <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 mb-2 block">Desktop Image</label>

                    <div class="file-upload-container">
                        <div class="relative group file-upload-box"
                             @click="$dispatch('open-file-manager', { callback: callbackName })">

                            <div class="upload-content w-full aspect-[4/3]">
                                <template x-if="filePreview">
                                    <div class="w-full h-full rounded-lg overflow-hidden border-2 border-gray-200 dark:border-gray-700 cursor-pointer hover:border-blue-400 dark:hover:border-blue-600 transition-all duration-300 relative">
                                        <img :src="filePreview" class="w-full h-full object-cover">
                                        <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                                            <div class="text-center text-white">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                                <span class="text-sm font-medium">Change Image</span>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <template x-if="!filePreview">
                                    <div class="w-full h-full rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 cursor-pointer hover:border-blue-400 dark:hover:border-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-all duration-300 flex flex-col items-center justify-center p-6">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-gray-400 dark:text-gray-500 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">No image selected</p>
                                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Click to choose</p>
                                    </div>
                                </template>

                                <template x-if="filePreview">
                                    <button type="button"
                                            @click.stop="items[index]['{{ $childField['name'] }}']['desktop'] = ''; filePreview = '';"
                                            class="absolute -top-2 -right-2 w-7 h-7 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center shadow-lg transition-all duration-300 hover:scale-110 z-10"
                                            title="Remove image">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Mobile Image --}}
                <div x-data="{
                    fileUrl: items[index]['{{ $childField['name'] }}']['mobile'] || '',
                    filePreview: items[index]['{{ $childField['name'] }}']['mobile'] || '',
                    get callbackName() {
                        return '{{ $mobileFieldId }}_' + index;
                    },
                    init() {
                        this.$nextTick(() => {
                            const handler = (e) => {
                                this.fileUrl = e.detail.url;
                                this.filePreview = e.detail.url;
                                items[index]['{{ $childField['name'] }}']['mobile'] = e.detail.url;
                            };
                            window.addEventListener(this.callbackName, handler);
                            this._cleanup = () => window.removeEventListener(this.callbackName, handler);
                        });
                    },
                    destroy() {
                        if (this._cleanup) this._cleanup();
                    }
                }">
                    {{-- Hidden input --}}
                    <input type="hidden"
                           :name="'data[{{ $parentFieldName }}][' + index + '][{{ $childField['name'] }}][mobile]'"
                           x-model="items[index]['{{ $childField['name'] }}']['mobile']"
                           {{ $isRequired }}>

                    <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 mb-2 block">Mobile Image</label>

                    <div class="file-upload-container">
                        <div class="relative group file-upload-box"
                             @click="$dispatch('open-file-manager', { callback: callbackName })">

                            <div class="upload-content w-full aspect-[4/3]">
                                <template x-if="filePreview">
                                    <div class="w-full h-full rounded-lg overflow-hidden border-2 border-gray-200 dark:border-gray-700 cursor-pointer hover:border-blue-400 dark:hover:border-blue-600 transition-all duration-300 relative">
                                        <img :src="filePreview" class="w-full h-full object-cover">
                                        <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                                            <div class="text-center text-white">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                                <span class="text-sm font-medium">Change Image</span>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <template x-if="!filePreview">
                                    <div class="w-full h-full rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 cursor-pointer hover:border-blue-400 dark:hover:border-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-all duration-300 flex flex-col items-center justify-center p-6">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-gray-400 dark:text-gray-500 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">No image selected</p>
                                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Click to choose</p>
                                    </div>
                                </template>

                                <template x-if="filePreview">
                                    <button type="button"
                                            @click.stop="items[index]['{{ $childField['name'] }}']['mobile'] = ''; filePreview = '';"
                                            class="absolute -top-2 -right-2 w-7 h-7 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center shadow-lg transition-all duration-300 hover:scale-110 z-10"
                                            title="Remove image">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @break

        @default
            <input type="text"
                   :name="'data[{{ $parentFieldName }}][' + index + '][{{ $childField['name'] }}]'"
                   x-model="items[index]['{{ $childField['name'] }}']"
                   placeholder="{{ $childField['label'] }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                   {{ $isRequired }}>
    @endswitch
</div>
