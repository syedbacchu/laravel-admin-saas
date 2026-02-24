<ul class="perfect-scrollbar font-semibold space-y-0.5">

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
                $isChildActive = collect($menu['children'])
                    ->pluck('route')
                    ->contains(fn ($route) => request()->routeIs($route));
            @endphp

            <li class="menu nav-item"
                x-data="{ open: {{ $isChildActive ? 'true' : 'false' }} }">

                <button type="button"
                        class="nav-link group {{ $isChildActive ? 'active' : '' }}"
                        @click="open = !open">

                    <div class="flex items-center">
                        <x-common.icon :name="$menu['icon']"/>
                        <span class="ltr:pl-3">{{ __($menu['label']) }}</span>
                    </div>

                    <div class="transition-transform"
                         :class="{ '!rotate-90': open }">
                        ▶
                    </div>
                </button>

                <ul x-show="open"
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
