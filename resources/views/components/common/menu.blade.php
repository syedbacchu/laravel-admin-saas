@php
    // Get current route for initial menu state
    $currentRouteName = request()->route()->getName();

    // Find which parent menu should be active based on current route
    $activeParentMenu = collect($sidebarMenus)->first(function($menu) use ($currentRouteName) {
        if (empty($menu['children'])) {
            return false;
        }

        // Check exact matches first
        $childRoutes = collect($menu['children'])->pluck('route');
        if ($childRoutes->contains(fn($route) => $currentRouteName === $route)) {
            return true;
        }

        // Check if current route belongs to this parent group
        if (isset($menu['key'])) {
            $parentRouteGroups = [
                'tenants' => ['tenant.', 'backups', 'logs', 'backup', 'migrate'],
                'users' => ['user.', 'profile'],
                'billing' => ['subscription', 'payment', 'feature', 'plan'],
                'role' => ['permission'],
                'language' => ['lang'],
                'vehicle_management' => ['vehicle', 'area', 'registration'],
                'settings' => ['setting'],
                'app' => ['app', 'slider', 'onboard'],
            ];

            if (isset($parentRouteGroups[$menu['key']])) {
                $keywords = $parentRouteGroups[$menu['key']];
                foreach ($keywords as $keyword) {
                    if (str_contains($currentRouteName, $keyword)) {
                        return true;
                    }
                }
            }
        }

        return false;
    });

    $initialActiveMenu = $activeParentMenu ? ($activeParentMenu['key'] ?? '') : '';
@endphp

<ul class="perfect-scrollbar font-semibold space-y-0.5" x-data="{ activeMenu: '{{ $initialActiveMenu }}' }">

    @foreach($sidebarMenus as $menu)

        {{-- ================= SINGLE LINK ================= --}}
        @if(empty($menu['children']))
            <li class="nav-item">
                <a href="{{ route($menu['route']) }}"
                   class="nav-link group {{ request()->routeIs($menu['route']) ? 'active' : '' }}">
                    <div class="flex items-center">
                        <x-common.icon :name="$menu['icon']"/>
                        <span class="ltr:pl-3">{{ __($menu['label']) }}</span>
                    </div>
                </a>
            </li>

            {{-- ================= DROPDOWN ================= --}}
        @else
            @php
                // Get current route name
                $currentRoute = request()->route()->getName();

                // Check if any child route matches exactly
                $childRoutes = collect($menu['children'])->pluck('route')->toArray();
                $isChildActive = collect($childRoutes)
                    ->contains(fn ($route) => request()->routeIs($route));

                // If no exact match, check if current route belongs to this parent menu group
                if (!$isChildActive && isset($menu['key'])) {
                    $menuKey = $menu['key'];

                    // Define parent menu route groups
                    $parentRouteGroups = [
                        'tenants' => ['tenant.', 'backups', 'logs', 'backup', 'migrate'],
                        'users' => ['user.', 'profile'],
                        'billing' => ['subscription', 'payment', 'feature', 'plan'],
                        'role' => ['permission'],
                        'language' => ['lang'],
                        'settings' => ['setting'],
                        'app' => ['app', 'slider', 'onboard'],
                        'components' => ['component.','fields']
                    ];

                    if (isset($parentRouteGroups[$menuKey])) {
                        $relatedKeywords = $parentRouteGroups[$menuKey];
                        foreach ($relatedKeywords as $keyword) {
                            if (str_contains($currentRoute, $keyword)) {
                                $isChildActive = true;
                                break;
                            }
                        }
                    }
                }

                $menuKey = $menu['key'] ?? $loop->index;
            @endphp

            <li class="menu nav-item">

                <button type="button"
                        class="nav-link group {{ $isChildActive ? 'active' : '' }}"
                        @click="activeMenu = activeMenu === '{{ $menuKey }}' ? '' : '{{ $menuKey }}'">

                    <div class="flex items-center">
                        <x-common.icon :name="$menu['icon']"/>
                        <span class="ltr:pl-3">{{ __($menu['label']) }}</span>
                    </div>

                    <div class="transition-transform"
                         :class="{ '!rotate-90': activeMenu === '{{ $menuKey }}' }">
                        ▶
                    </div>
                </button>

                <ul x-show="activeMenu === '{{ $menuKey }}'"
                    x-collapse
                    x-cloak
                    class="sub-menu text-gray-500">

                    @foreach($menu['children'] as $child)
                        <li>
                            <a href="{{ route($child['route']) }}"
                               class="nav-link {{ request()->routeIs($child['route']) ? 'active' : '' }}">
                                {{ __($child['label']) }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </li>
        @endif

    @endforeach
</ul>
