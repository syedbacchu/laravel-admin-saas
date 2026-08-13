<x-layout.default>
@section('title', $pageTitle)

@php
    $componentFields = $section->component->fields->sortBy('sort_order');
    $fieldTypes = ['text', 'textarea', 'number', 'email', 'url', 'image', 'file', 'select', 'checkbox', 'radio', 'repeater', 'color', 'date', 'datetime', 'wysiwyg'];
@endphp

<div class="panel mt-6">
    <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100 mb-6">
        {{ $pageTitle }}
    </h1>

    <div class="mb-6">
        <div class="flex flex-wrap gap-4 text-sm text-gray-600">
            <span><strong>{{ __('Page') }}:</strong> {{ $page->name }}</span>
            <span><strong>{{ __('Component') }}:</strong> {{ $section->component->title }}</span>
            <span><strong>{{ __('Language') }}:</strong>
                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">{{ $language->name }}</span>
            </span>
        </div>
    </div>

    @if($componentFields->count() > 0)
        <div x-data="{
            formData: @js($contentData),
            isSaving: false,
            saveSuccess: false,
            hasChanges: false,
            originalData: @js($contentData),
            checkChanges() {
                this.hasChanges = JSON.stringify(this.formData) !== JSON.stringify(this.originalData);
            },
            async saveContent() {
                this.isSaving = true;
                this.saveSuccess = false;

                try {
                    const formData = new FormData();
                    formData.append('language_id', '{{ $language->id }}');
                    formData.append('data', JSON.stringify(this.formData));
                    formData.append('_token', '{{ csrf_token() }}');
                    formData.append('_method', 'POST');

                    const response = await fetch('{{ route('pages.sections.translations.update-content', ['pageId' => $page->id, 'sectionId' => $section->id]) }}', {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    });

                    const result = await response.json();

                    if (result.success) {
                        this.saveSuccess = true;
                        this.originalData = JSON.parse(JSON.stringify(this.formData));
                        this.hasChanges = false;

                        // Show success notification
                        const notification = document.createElement('div');
                        notification.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-4 rounded-lg shadow-lg z-50 flex items-center gap-3';
                        notification.innerHTML = `
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <div>
                                <p class="font-semibold">${result.message || 'Content saved successfully!'}</p>
                            </div>
                        `;
                        document.body.appendChild(notification);
                        setTimeout(() => {
                            notification.style.opacity = '0';
                            setTimeout(() => notification.remove(), 300);
                        }, 3000);
                    } else {
                        throw new Error(result.message || 'Failed to save content');
                    }
                } catch (error) {
                    const notification = document.createElement('div');
                    notification.className = 'fixed top-4 right-4 bg-red-500 text-white px-6 py-4 rounded-lg shadow-lg z-50';
                    notification.textContent = error.message || 'Failed to save content';
                    document.body.appendChild(notification);
                    setTimeout(() => notification.remove(), 3000);
                } finally {
                    this.isSaving = false;
                }
            },
            getFieldLabel(fieldName) {
                const field = @js($componentFields->map(function($f) { return ['id' => $f->id, 'name' => $f->name, 'label' => $f->label, 'field_type' => $f->field_type, 'is_required' => $f->is_required, 'config' => $f->config]; }));
                return field.find(f => f.name === fieldName)?.label || fieldName;
            },
            getFieldType(fieldName) {
                const field = @js($componentFields->map(function($f) { return ['id' => $f->id, 'name' => $f->name, 'label' => $f->label, 'field_type' => $f->field_type, 'is_required' => $f->is_required, 'config' => $f->config]; }));
                return field.find(f => f.name === fieldName)?.field_type || 'text';
            },
            isFieldRequired(fieldName) {
                const field = @js($componentFields->map(function($f) { return ['id' => $f->id, 'name' => $f->name, 'label' => $f->label, 'field_type' => $f->field_type, 'is_required' => $f->is_required, 'config' => $f->config]; }));
                return field.find(f => f.name === fieldName)?.is_required || false;
            }
        }" x-init="checkChanges()">
            <form @submit.prevent="saveContent()">
                <div class="space-y-6">
                    @foreach($componentFields as $field)
                        @php
                            $fieldValue = $contentData[$field->name] ?? '';
                            $isRequired = $field->is_required ? 'required' : '';
                        @endphp

                        <div class="bg-white border border-gray-200 rounded-lg p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <label class="block text-lg font-semibold text-gray-800">
                                        {{ $field->label }}
                                        @if($field->is_required)
                                            <span class="text-red-500 ml-1">*</span>
                                        @endif
                                    </label>
                                    <p class="text-sm text-gray-500 mt-1">
                                        {{ $field->name }} • <span class="text-blue-600">{{ $field->field_type }}</span>
                                        @if($field->is_translatable)
                                            <span class="ml-2 text-green-600">✓ {{__('Translatable')}}</span>
                                        @endif
                                    </p>
                                </div>

                                @if(!empty($field->config))
                                    <div class="text-xs text-gray-400">
                                        <button type="button" class="text-blue-600 hover:text-blue-800" @click="$el.textContent = $el.textContent === '?' ? '✕' : '?'">
                                            ?
                                        </button>
                                    </div>
                                @endif
                            </div>

                            <!-- Dynamic Field Rendering -->
                            <div class="mt-4">
                                @switch($field->field_type)
                                    @case('text')
                                        <input type="text"
                                               x-model="formData['{{ $field->name }}']"
                                               @input="checkChanges()"
                                               placeholder="{{ $field->label }}"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                               {{ $isRequired }}>
                                        @break

                                    @case('textarea')
                                        <textarea x-model="formData['{{ $field->name }}']"
                                                  @input="checkChanges()"
                                                  rows="4"
                                                  placeholder="{{ $field->label }}"
                                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                  {{ $isRequired }}></textarea>
                                        @break

                                    @case('number')
                                        <input type="number"
                                               x-model="formData['{{ $field->name }}']"
                                               @input="checkChanges()"
                                               placeholder="{{ $field->label }}"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                               {{ $isRequired }}>
                                        @break

                                    @case('email')
                                        <input type="email"
                                               x-model="formData['{{ $field->name }}']"
                                               @input="checkChanges()"
                                               placeholder="{{ $field->label }}"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                               {{ $isRequired }}>
                                        @break

                                    @case('url')
                                        <input type="url"
                                               x-model="formData['{{ $field->name }}']"
                                               @input="checkChanges()"
                                               placeholder="https://"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                               {{ $isRequired }}>
                                        @break

                                    @case('wysiwyg')
                                        <textarea x-model="formData['{{ $field->name }}']"
                                                  @input="checkChanges()"
                                                  rows="6"
                                                  placeholder="{{ $field->label }}"
                                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                  {{ $isRequired }}></textarea>
                                        <p class="text-xs text-gray-500 mt-1">{{__('Rich text editor - supports basic HTML')}}</p>
                                        @break

                                    @case('color')
                                        <div class="flex items-center gap-3">
                                            <input type="color"
                                                   x-model="formData['{{ $field->name }}']"
                                                   @input="checkChanges()"
                                                   class="w-16 h-10 rounded cursor-pointer border-0"
                                                   {{ $isRequired }}>
                                            <input type="text"
                                                   x-model="formData['{{ $field->name }}']"
                                                   @input="checkChanges()"
                                                   placeholder="#000000"
                                                   class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                   {{ $isRequired }}>
                                        </div>
                                        @break

                                    @case('date')
                                        <input type="date"
                                               x-model="formData['{{ $field->name }}']"
                                               @input="checkChanges()"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                               {{ $isRequired }}>
                                        @break

                                    @case('datetime')
                                        <input type="datetime-local"
                                               x-model="formData['{{ $field->name }}']"
                                               @input="checkChanges()"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                               {{ $isRequired }}>
                                        @break

                                    @case('select')
                                        @php
                                            $options = $field->config['options'] ?? [];
                                        @endphp
                                        <select x-model="formData['{{ $field->name }}']"
                                                @change="checkChanges()"
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                {{ $isRequired }}>
                                            <option value="">{{__('Select an option')}}</option>
                                            @foreach($options as $value => $label)
                                                <option value="{{ $value }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        @break

                                    @case('checkbox')
                                        @php
                                            $options = $field->config['options'] ?? [];
                                        @endphp
                                        <div class="space-y-2">
                                            @foreach($options as $value => $label)
                                                <label class="flex items-center gap-2 cursor-pointer">
                                                    <input type="checkbox"
                                                           value="{{ $value }}"
                                                           {{ $field->is_required ? 'required' : '' }}
                                                           class="w-4 h-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-500"
                                                           :checked="formData['{{ $field->name }}'] && formData['{{ $field->name }}'].includes('{{ $value }}')"
                                                           @change="
                                                               if (!formData['{{ $field->name }}']) formData['{{ $field->name }}'] = [];
                                                               if ($event.target.checked) {
                                                                   formData['{{ $field->name }}'].push('{{ $value }}');
                                                               } else {
                                                                   formData['{{ $field->name }}'] = formData['{{ $field->name }}'].filter(item => item !== '{{ $value }}');
                                                               }
                                                               checkChanges();
                                                           ">
                                                    <span>{{ $label }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                        @break

                                    @case('radio')
                                        @php
                                            $options = $field->config['options'] ?? [];
                                        @endphp
                                        <div class="space-y-2">
                                            @foreach($options as $value => $label)
                                                <label class="flex items-center gap-2 cursor-pointer">
                                                    <input type="radio"
                                                           value="{{ $value }}"
                                                           x-model="formData['{{ $field->name }}']"
                                                           @change="checkChanges()"
                                                           class="w-4 h-4 text-blue-600 focus:ring-2 focus:ring-blue-500"
                                                           {{ $isRequired }}>
                                                    <span>{{ $label }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                        @break

                                    @case('image')
                                        <div class="space-y-3">
                                            <div class="flex items-start gap-3">
                                                <input type="text"
                                                       x-model="formData['{{ $field->name }}']"
                                                       @input="checkChanges()"
                                                       placeholder="https://example.com/image.jpg"
                                                       class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                       {{ $isRequired }}>
                                                <button type="button" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                                                    {{__('Browse')}}
                                                </button>
                                            </div>
                                            @if(!empty($fieldValue))
                                                <div class="mt-2">
                                                    <img :src="formData['{{ $field->name }}']" alt="{{ $field->label }}" class="max-w-xs h-auto rounded border border-gray-200" onerror="this.style.display='none'">
                                                </div>
                                            @endif
                                        </div>
                                        @break

                                    @case('file')
                                        <div class="flex items-start gap-3">
                                            <input type="text"
                                                   x-model="formData['{{ $field->name }}']"
                                                   @input="checkChanges()"
                                                   placeholder="https://example.com/file.pdf"
                                                   class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                   {{ $isRequired }}>
                                            <button type="button" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                                                {{__('Upload')}}
                                            </button>
                                        </div>
                                        @break

                                    @case('repeater')
                                        <div class="space-y-4">
                                            <template x-for="(item, index) in formData['{{ $field->name }}'] || []" :key="index">
                                                <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg">
                                                    <div class="flex items-center justify-between mb-3">
                                                        <span class="font-medium text-gray-700">Item <span x-text="index + 1"></span></span>
                                                        <button type="button" @click="formData['{{ $field->name }}'].splice(index, 1); checkChanges();" class="text-red-600 hover:text-red-800">
                                                            {{__('Remove')}}
                                                        </button>
                                                    </div>
                                                    <textarea x-model="formData['{{ $field->name }}'][index]"
                                                              @input="checkChanges()"
                                                              rows="2"
                                                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"></textarea>
                                                </div>
                                            </template>

                                            <button type="button" @click="if (!formData['{{ $field->name }}']) formData['{{ $field->name }}'] = []; formData['{{ $field->name }}'].push(''); checkChanges();" class="px-4 py-2 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 border border-blue-200">
                                                + {{__('Add Item')}}
                                            </button>
                                        </div>
                                        @break

                                    @default
                                        <input type="text"
                                               x-model="formData['{{ $field->name }}']"
                                               @input="checkChanges()"
                                               placeholder="{{ $field->label }}"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                               {{ $isRequired }}>
                                @endswitch
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Action Bar -->
                <div class="sticky bottom-0 bg-white border-t border-gray-200 p-4 mt-6 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <span x-show="hasChanges" class="text-sm text-amber-600">
                            ● {{__('You have unsaved changes')}}
                        </span>
                        <span x-show="saveSuccess" class="text-sm text-green-600">
                            ✓ {{__('Saved successfully')}}
                        </span>
                    </div>

                    <div class="flex gap-3">
                        <a href="{{ route('pages.sections.translations.index', ['pageId' => $page->id, 'sectionId' => $section->id]) }}"
                           class="px-6 py-2.5 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                            {{__('Back')}}
                        </a>
                        <button type="submit"
                                :disabled="isSaving || !hasChanges"
                                :class="(isSaving || !hasChanges) ? 'opacity-50 cursor-not-allowed' : 'hover:from-indigo-700 hover:to-blue-700'"
                                class="px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-blue-600 text-white rounded-lg transition-all flex items-center gap-2">
                            <template x-if="isSaving">
                                <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </template>
                            <template x-if="!isSaving">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </template>
                            <span x-text="isSaving ? 'Saving...' : 'Save Changes'"></span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    @else
        <div class="text-center py-12">
            <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <h3 class="text-lg font-medium text-gray-900">{{__('No Fields Configured')}}</h3>
            <p class="text-gray-500 mt-2">{{__('This component has no fields configured yet. Add fields to the component to enable content editing.')}}</p>
            <a href="{{ route('component.fields', $section->component->id) }}" class="inline-flex items-center px-6 py-3 mt-6 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                {{__('Configure Component Fields')}}
            </a>
        </div>
    @endif
</div>
</x-layout.default>