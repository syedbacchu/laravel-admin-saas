<link rel="stylesheet" href="{{ asset('assets/common/datatables/jquery.dataTables.min.css') }}">
<script src="{{ asset('assets/common/jquery-3.7.0.min.js') }}"></script>
<script src="{{ asset('assets/common/datatables/jquery.dataTables.min.js') }}"></script>

@props([
    'id' => 'datatable',
    'ajax',
    'columns' => [],
    'filters' => [],
    'order' => [[0, 'desc']],
    'pageLength' => 20,
    'enableSearch' => true,
])
@if(count($filters) || !$enableSearch)
    <div class="mb-6 grid grid-cols-12 gap-4">

        {{-- LEFT SIDE: FILTERS --}}
        <div class="col-span-12 lg:col-span-9">
            <div class="flex flex-wrap gap-4 items-start">
                @foreach($filters as $filter)
                    <div class="w-full sm:w-[calc(50%-0.5rem)] lg:w-[calc(33.333%-0.67rem)]">
                        <label class="mb-1 block text-sm font-medium text-gray-700">
                            {{ $filter['label'] }}
                        </label>

                        @if($filter['type'] === 'select')
                            <select id="{{ $filter['name'] }}" data-filter="{{ $filter['name'] }}"
                                    class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-blue-500">
                                @foreach($filter['options'] as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        @endif

                        @if($filter['type'] === 'date')
                            <input id="{{ $filter['name'] }}" type="date" data-filter="{{ $filter['name'] }}"
                                   class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-blue-500">
                        @endif

                        @if($filter['type'] === 'daterange')
                            <div class="flex gap-2">
                                <input id="{{ $filter['name'] }}_from" type="date" data-filter="{{ $filter['name'] }}_from"
                                       class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-blue-500">
                                <input id="{{ $filter['name'] }}_to" type="date" data-filter="{{ $filter['name'] }}_to"
                                       class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-blue-500">
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        {{-- RIGHT SIDE: SEARCH (ALWAYS FIRST ROW) --}}
        @if(!$enableSearch)
            <div class="col-span-12 lg:col-span-3">
                <div class="sticky top-0">
                    <label class="mb-1 block text-sm font-medium text-gray-700">
                        Search
                    </label>
                    <input
                        type="text"
                        id="{{ $id }}_search"
                        placeholder="Search..."
                        class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        @endif

    </div>
@endif


<table id="{{ $id }}" class="min-w-full border border-gray-200 rounded-xl text-sm text-gray-700">
    <thead class="bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
    <tr>
        @foreach ($columns as $column)
            <th class="px-4 py-3 text-left font-semibold text-gray-600 uppercase tracking-wide">
                {{ __($column['title']
                    ?? ucfirst(str_replace('_', ' ', $column['data'] ?? ''))) }}
            </th>
        @endforeach
    </tr>
    </thead>
</table>

@push('scripts')
    <script>
        $(document).ready(function () {

            let table = $('#{{ $id }}').DataTable({
                processing: true,
                serverSide: true,
                searching: {{ $enableSearch ? 'true' : 'false' }},
                ajax: {
                    url: "{{ $ajax }}",
                    data: function (d) {
                        $('[data-filter]').each(function () {
                            d[$(this).data('filter')] = $(this).val();
                        });

                        @if(!$enableSearch)
                            d.search = $('#{{ $id }}_search').val();
                        @endif
                    }
                },
                columns: @json($columns),
                order: @json($order),
                pageLength: {{ $pageLength }},
            });

            // Reload on filter change
            $('[data-filter]').on('change', function () {
                table.ajax.reload();
            });

            @if(!$enableSearch)
            $('#{{ $id }}_search').on('keyup', function () {
                table.ajax.reload();
            });
            @endif

        });
    </script>
@endpush

