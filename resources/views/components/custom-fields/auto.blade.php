<div class="mt-8 border-t pt-6">
    <h3 class="text-lg font-bold mb-4">Additional Information</h3>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach($fields as $field)
            <x-custom-fields.custom-field-input
                :field="$field"
                :value="$field->resolved_value ?? null"
            />
        @endforeach
    </div>
</div>
