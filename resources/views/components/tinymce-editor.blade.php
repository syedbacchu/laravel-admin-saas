@props([
    'name' => 'content',
    'value' => '',
    'height' => '400px',
    'id' => 'tinymce-' . uniqid(),
])

@php
    $uniqueId = $id ?: 'tinymce-' . uniqid();
@endphp

<div class="tinymce-editor-wrapper">
    <textarea
        id="{{ $uniqueId }}"
        name="{{ $name }}"
        class="tinymce-textarea"
        rows="10"
    >{{ old($name, $value) }}</textarea>
</div>

@once
@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/skins/ui/tinymce-5/skin.min.css">
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    tinymce.init({
        selector: '.tinymce-textarea',
        height: '{{ $height }}',
        menubar: true,
        statusbar: true,
        resize: true,
        plugins: [
            'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
            'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
            'insertdatetime', 'media', 'table', 'help', 'wordcount', 'directionality',
            'emoticons', 'codesample', 'nonbreaking', 'template', 'paste'
        ],
        toolbar: 'undo redo | blocks | ' +
            'bold italic underline strikethrough forecolor backcolor | alignleft aligncenter ' +
            'alignright alignjustify | bullist numlist outdent indent | ' +
            'link image media table codesample | removeformat code fullscreen | ' +
            'help directionality',
        toolbar_mode: 'sliding',
        image_advtab: true,
        image_caption: true,
        image_uploadtab: true,

        // File Manager Integration for Images
        file_picker_callback: function(callback, value, meta) {
            // Trigger file manager
            window.dispatchEvent(new CustomEvent('open-file-manager', {
                detail: {
                    callback: 'tinymce-file-selected'
                }
            }));

            // Listen for file selection
            window.addEventListener('tinymce-file-selected', function fileSelected(e) {
                window.removeEventListener('tinymce-file-selected', fileSelected);
                callback(e.detail.url, {
                    alt: 'Selected image',
                    style: 'max-width: 100%; height: auto;'
                });
            });
        },

        // Automatic link detection
        link_default_target: '_blank',
        link_title: true,

        // Table styling
        table_default_styles: {
            'border-collapse': 'collapse',
            'width': '100%'
        },
        table_default_attributes: {
            'border': '1',
            'cellpadding': '5',
            'cellspacing': '0'
        },

        // Content styling
        content_style: `
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
                font-size: 14px;
                line-height: 1.6;
                color: #333;
            }
            table { border-collapse: collapse; }
            table td, table th { border: 1px solid #ddd; padding: 8px; }
            table th { background-color: #f2f2f2; }
            img { max-width: 100%; height: auto; }
            a { color: #0066cc; text-decoration: underline; }
            a:hover { color: #004499; }
        `,

        // Additional options
        branding: false,
        promotion: false,
        relative_urls: false,
        remove_script_host: false,
        convert_urls: true,

        // Language direction support
        directionality: '{{ app()->getLocale() === "ar" || app()->getLocale() === "he" ? "rtl" : "ltr" }}',

        // Cleanup and formatting
        entity_encoding: 'raw',
        valid_elements: '*[*]',
        extended_valid_elements: '*[*]',

        // Image handling
        images_upload_url: '/api/tinymce/upload',
        images_upload_handler: function (blobInfo, success, failure) {
            let xhr, formData;
            xhr = new XMLHttpRequest();
            xhr.withCredentials = false;
            xhr.open('POST', '/api/tinymce/upload');
            xhr.onload = function() {
                let json;
                if (xhr.status != 200) {
                    failure('HTTP Error: ' + xhr.status);
                    return;
                }
                json = JSON.parse(xhr.responseText);
                if (!json || typeof json.location != 'string') {
                    failure('Invalid JSON: ' + xhr.responseText);
                    return;
                }
                success(json.location);
            };
            formData = new FormData();
            formData.append('file', blobInfo.blob(), blobInfo.filename());
            xhr.send(formData);
        }
    });
});
</script>
@endpush
@endonce

<style>
.tinymce-editor-wrapper {
    position: relative;
}

.tinymce-editor-wrapper .tox-tinymce {
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
}

.tox-toolbar__primary,
.tox-toolbar__secondary {
    background-color: #f9fafb !important;
}

.tox-sidebar-wrap {
    border-left: 1px solid #e5e7eb;
}
</style>