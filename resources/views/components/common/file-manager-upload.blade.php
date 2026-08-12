@props([
    'name' => 'file', // Input name
    'value' => '', // Existing file URL
    'label' => 'Upload Image', // Label text
    'accept' => 'image/*', // File types to accept
    'width' => null, // Box width in px (null = auto)
    'height' => null, // Box height in px (null = auto)
    'required' => false, // Is field required
    'helpText' => '', // Optional help text
    'responsive' => true, // Make responsive container
])

@php
    $componentId = 'fm_' . uniqid();
    $isRequired = $required ? 'required' : '';

    // Handle width/height - use null, 0, or empty as auto
    $hasWidth = !empty($width) && is_numeric($width);
    $hasHeight = !empty($height) && is_numeric($height);
    $boxWidth = $hasWidth ? $width . 'px' : '100%';
    $boxHeight = $hasHeight ? $height . 'px' : '100%';
    $isResponsive = !$hasWidth || !$hasHeight || $responsive;
@endphp

<div x-data="fileManager('{{ $value ?? '' }}', '{{ $name }}_{{ $componentId }}')"
     class="file-manager-upload-wrapper space-y-3">

    {{-- Label Section --}}
    @if($label)
    <div class="flex items-center justify-between">
        <label class="font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2">
            @if($required)
            <span class="text-red-500">*</span>
            @endif
            {{ __($label) }}
        </label>
        @if($helpText)
        <div class="text-xs text-gray-500 dark:text-gray-400">
            {{ __($helpText) }}
        </div>
        @endif
    </div>
    @endif

    {{-- Upload/Preview Area --}}
    <div class="file-upload-container @if($isResponsive) w-full @endif">
        {{-- Hidden Input --}}
        <input type="hidden"
               name="{{ $name }}"
               x-model="fileUrl"
               {{ $isRequired }}>

        {{-- Image Preview / No Image State --}}
        <div class="relative group file-upload-box @if($isResponsive) w-full @endif"
             @if(!$isResponsive) style="width: {{ $boxWidth }}; min-height: {{ $boxHeight }};" @endif
             @click="$dispatch('open-file-manager', { callback: callbackName })">

            <div class="upload-content @if($isResponsive) w-full aspect-[4/3] @endif"
                 @if(!$isResponsive) style="width: {{ $boxWidth }}; height: {{ $boxHeight }};" @endif>

            {{-- When Image Selected --}}
            <template x-if="filePreview">
                <div class="w-full h-full rounded-lg overflow-hidden border-2 border-gray-200 dark:border-gray-700 cursor-pointer hover:border-blue-400 dark:hover:border-blue-600 transition-all duration-300 relative">
                    <img :src="filePreview"
                         class="w-full h-full object-cover"
                         @if(!$isResponsive) style="width: {{ $boxWidth }}; height: {{ $boxHeight }};" @endif>

                    {{-- Overlay with Change Button --}}
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

            {{-- When No Image Selected --}}
            <template x-if="!filePreview">
                <div class="w-full h-full rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 cursor-pointer hover:border-blue-400 dark:hover:border-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-all duration-300 flex flex-col items-center justify-center p-6">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-gray-400 dark:text-gray-500 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">No image selected</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Click to choose</p>
                </div>
            </template>

            {{-- Remove Button (top-right, always visible when image exists) --}}
            <template x-if="filePreview">
                <button type="button"
                        @click.stop="fileUrl = ''; filePreview = '';"
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

    {{-- Additional Info --}}
    @if($accept && !$helpText)
    <div class="text-xs text-gray-500 dark:text-gray-400 mt-2">
        Accepted formats: {{ $accept }}
    </div>
    @endif
</div>

<style>
    .file-manager-upload-wrapper .group:hover .group-hover\:opacity-100 {
        opacity: 1;
    }

    .file-upload-container .upload-content {
        position: relative;
    }

    @media (max-width: 768px) {
        .file-upload-container .upload-content {
            min-height: 200px;
        }
    }
</style>