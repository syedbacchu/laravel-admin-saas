{{-- Clean repeater display - just presentation with Alpine.js --}}
@php
    $repeaterId = 'repeater_' . uniqid();
@endphp

@php
    // Initialize items with proper structure for all field types
    $itemsData = [];
    foreach($field['items'] ?? [] as $item) {
        $itemData = [];
        foreach($item['fields'] as $childField) {
            $fieldName = $childField['name'];

            // Handle responsive_image fields
            if($childField['field_type'] === 'responsive_image') {
                $itemData[$fieldName] = [
                    'mobile' => $childField['mobile_value'] ?? '',
                    'desktop' => $childField['desktop_value'] ?? ''
                ];
            } else {
                // Handle all other field types
                $itemData[$fieldName] = $childField['value'] ?? '';
            }
        }
        $itemsData[] = $itemData;
    }

    // Create structure template for new items
    $newItemTemplate = [];
    foreach($field['children'] ?? [] as $childField) {
        $fieldName = $childField['name'];
        if($childField['field_type'] === 'responsive_image') {
            $newItemTemplate[$fieldName] = ['mobile' => '', 'desktop' => ''];
        } else {
            $newItemTemplate[$fieldName] = '';
        }
    }
@endphp

<div x-data="{
    items: {{ json_encode($itemsData) }},
    newItemTemplate: {{ json_encode($newItemTemplate) }},
    newItemCounter: {{ count($itemsData) }},

    addItem() {
        this.items.push(JSON.parse(JSON.stringify(this.newItemTemplate)));
        this.$nextTick(() => {
            this.newItemCounter++;
        });
    },

    removeItem(index) {
        if (confirm('Are you sure you want to remove this item?')) {
            this.items.splice(index, 1);
        }
    }
}" class="space-y-4">
    <template x-for="(item, index) in items" :key="index">
        <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg relative">
            <div class="flex items-center justify-between mb-4">
                <h4 class="font-semibold text-gray-700">
                    <span x-text="'Item ' + (index + 1)"></span>
                </h4>
                <button type="button" @click="removeItem(index)" class="text-red-600 hover:text-red-800 text-sm font-medium">
                    🗑️ Remove
                </button>
            </div>

            <div class="space-y-3">
                @foreach($field['children'] as $childField)
                    @include('admin.section-translation.partials.repeater-child-display', [
                        'childField' => $childField,
                        'parentFieldName' => $field['input_name'],
                        'language' => $language
                    ])
                @endforeach
            </div>
        </div>
    </template>

    {{-- Add More Button --}}
    <div class="text-center">
        <button type="button" @click="addItem()" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium inline-flex items-center gap-2">
            ➕ Add More Items
        </button>
    </div>
</div>