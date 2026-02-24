<?php

function defaultInputIcon() {
    $html = "";
    $html .= '<div class="bg-[#eee] flex justify-center items-center ltr:rounded-l-md rtl:rounded-r-md px-3 border ltr:border-r-0 rtl:border-l-0 border-[#e0e6ed] dark:border-[#17263c] dark:bg-[#1b2e4b]">';
    $html .= '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500 dark:text-gray-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">';
    $html .= '<path d="M12 20h9" /><path d="M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4 12.5-12.5z" /></svg>';
    $html .= '</div>';
    return $html;
}


if (! function_exists('action_buttons')) {
    /**
     * Render a group of action buttons.
     *
     * @param array $buttons
     * @return string
     */
    function action_buttons(array $buttons): string
    {
        return '<div class="flex gap-2 justify-center">'.implode('', $buttons).'</div>';
    }
}

if (! function_exists('edit_column')) {
    function edit_column(string $route, string $label = 'Edit'): string
    {
        return <<<HTML
        <a href="{$route}"
            title="{$label}"
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
        HTML;
    }
}



if (! function_exists('delete_column')) {
    function delete_column(string $route, string $label = 'Delete'): string
    {
        return <<<HTML
        <button type="button" title="{$label}"
            onclick="confirmDelete('{$route}')"
            class="inline-flex items-center px-3 py-1.5 text-sm font-semibold text-red-600 hover:text-white hover:bg-red-600 border border-red-600 rounded-lg transition duration-200">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5-4h4a1 1 0 011 1v1H9V4a1 1 0 011-1z" />
            </svg>
        </button>
        HTML;
    }
}

if (! function_exists('view_column')) {
    function view_column(string $route, string $label = 'View'): string
    {
        return <<<HTML
        <a href="{$route}"
            class="inline-flex items-center px-3 py-1.5 text-sm font-semibold text-gray-600 hover:text-white hover:bg-gray-600 border border-gray-600 rounded-lg transition duration-200">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.065 7-9.542 7s-8.268-2.943-9.542-7z" />
            </svg>
            {$label}
        </a>
        HTML;
    }
}

if (! function_exists('status_column')) {
    function status_column(string $route, bool $isActive, string $labelActive = 'Active', string $labelInactive = 'Inactive'): string
    {
        if ($isActive) {
            return <<<HTML
            <a href="{$route}"
                class="inline-flex items-center px-3 py-1.5 text-sm font-semibold text-green-600 hover:text-white hover:bg-green-600 border border-green-600 rounded-lg transition duration-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M5 13l4 4L19 7" />
                </svg>
                {$labelActive}
            </a>
            HTML;
        }

        return <<<HTML
        <a href="{$route}"
            class="inline-flex items-center px-3 py-1.5 text-sm font-semibold text-yellow-600 hover:text-white hover:bg-yellow-600 border border-yellow-600 rounded-lg transition duration-200">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M18 12H6" />
            </svg>
            {$labelInactive}
        </a>
        HTML;
    }
}


if (!function_exists('toggle_column')) {
    function toggle_column(string $route, int $id, bool $status, string $class = 'toggle-switch'): string
    {
        $checked = $status ? 'checked' : '';

        return <<<HTML
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" class="sr-only peer {$class}" data-id="{$id}" data-url="{$route}" {$checked}>
                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4
                            peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer
                            dark:bg-gray-700 peer-checked:after:translate-x-full
                            peer-checked:after:border-white after:content-[''] after:absolute
                            after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300
                            after:border after:rounded-full after:h-5 after:w-5 after:transition-all
                            dark:border-gray-600 peer-checked:bg-blue-600"></div>
            </label>
        HTML;
    }
}

if (! function_exists('view_button')) {
    function view_button(int|string $id, string $label = 'View'): string
    {
        return <<<HTML
        <button
            type="button"
            class="inline-flex items-center justify-center w-8 h-8 text-green-600 hover:text-white hover:bg-green-600 border border-green-600 rounded-lg transition duration-200 view-details"
            data-id="{$id}"
            title="{$label}"
        >
            <svg xmlns="http://www.w3.org/2000/svg"
                class="w-4 h-4"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
        </button>
        HTML;
    }
}
