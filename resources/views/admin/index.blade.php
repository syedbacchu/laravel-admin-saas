<x-layout.default>
    <script defer src="/assets/js/apexcharts.js"></script>
    <div x-data="sales">
        <ul class="flex space-x-2 rtl:space-x-reverse">
            <li>
                <a href="javascript:;" class="text-primary hover:underline">Dashboard</a>
            </li>
            <li class="before:content-['/'] ltr:before:mr-1 rtl:before:ml-1">
                <span>Admin Panel</span>
            </li>
        </ul>

        <div class="pt-5">
            {{'Admin panel data loading .........'}}
        </div>
    </div>
    <script>
        document.addEventListener("alpine:init", () => {

        });
    </script>
</x-layout.default>
