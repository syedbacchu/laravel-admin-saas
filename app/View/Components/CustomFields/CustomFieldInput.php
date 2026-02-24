<?php
namespace App\View\Components\CustomFields;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CustomFieldInput extends Component
{
    public $field;
    public $value;

    public function __construct($field, $value = null)
    {
        $this->field = $field;
        $this->value = $value;
    }

    public function render(): View|Closure|string
    {
        return view('components.custom-fields.custom-field-input');
    }
}
