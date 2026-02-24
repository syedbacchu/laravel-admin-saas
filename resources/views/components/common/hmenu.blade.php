<!-- horizontal menu -->
<ul
    class="horizontal-menu hidden py-1.5 font-semibold px-6 lg:space-x-1.5 xl:space-x-8 rtl:space-x-reverse bg-white border-t border-[#ebedf2] dark:border-[#191e3a] dark:bg-[#0e1726] text-black dark:text-white-dark">

    @foreach($sidebarMenus as $menu)

        {{-- ================= SINGLE LINK ================= --}}
        @if(empty($menu['children']))
            <li class="menu nav-item relative">
                <a href="{{ route($menu['route']) }}"
                   class="nav-link {{ request()->routeIs($menu['route']) ? 'active' : '' }}">

                    <div class="flex items-center">
                        <x-common.icon :name="$menu['icon']"/>
                        <span class="px-1">{{ __($menu['label']) }}</span>
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

            <li class="menu nav-item relative"
                x-data="{ open: false }"
                @mouseenter="open = true"
                @mouseleave="open = false">

                <a href="javascript:;"
                   class="nav-link {{ $isChildActive ? 'active' : '' }}">

                    <div class="flex items-center">
                        <x-common.icon :name="$menu['icon']"/>
                        <span class="px-1">{{ __($menu['label']) }}</span>
                    </div>

                    <div class="right_arrow">
                        <svg class="w-4 h-4 rotate-90" viewBox="0 0 24 24" fill="none">
                            <path d="M9 5L15 12L9 19"
                                  stroke="currentColor"
                                  stroke-width="1.5"
                                  stroke-linecap="round"
                                  stroke-linejoin="round"/>
                        </svg>
                    </div>
                </a>

                <ul x-show="open"
                    x-transition
                    x-cloak
                    class="sub-menu absolute top-full left-0 mt-1
                           bg-white dark:bg-[#0e1726]
                           shadow-lg rounded-md min-w-[200px] z-50">

                    @foreach($menu['children'] as $child)
                        <li>
                            <a href="{{ route($child['route']) }}"
                               class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-[#191e3a]
                                      {{ request()->routeIs($child['route']) ? 'active' : '' }}">
                                {{ __($child['label']) }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </li>
        @endif

    @endforeach
</ul>
