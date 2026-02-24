@if(isset($items['data'][0]))
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-5">

        @foreach($items['data'] as $item)
            <div class="group bg-white rounded-2xl border shadow-sm overflow-hidden
                        hover:shadow-lg hover:-translate-y-1 transition-all duration-300 relative">

                <!-- Image Preview (16:9 ratio) -->
                <div class="w-full aspect-video bg-gray-100 overflow-hidden">
                    <img
                        src="{{ asset($item->full_url) }}"
                        alt="{{ $item->alt_text }}"
                        class="w-full h-full object-cover group-hover:scale-105 transition duration-500"
                    >
                </div>

                <!-- File Info -->
                <div class="p-3 text-sm space-y-1">
                    <p class="font-semibold truncate text-gray-800">
                        {{ $item->original_name }}
                    </p>

                    <div class="text-xs text-gray-500 leading-4">
                        <p>{{ number_format($item->size/1024, 1) }} KB</p>
                        <p class="truncate">{{ $item->mime_type }}</p>
                        @if($item->width && $item->height)
                            <p>{{ $item->width }} × {{ $item->height }} px</p>
                        @endif
                    </div>

                    @if($item->alt_text)
                        <p class="text-[10px] text-gray-400 italic truncate">
                            ALT: {{ $item->alt_text }}
                        </p>
                    @endif
                </div>

                <!-- Hover overlay -->
                <div
                    class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100
                           flex flex-col items-center justify-center gap-3 transition-all duration-300">

                    <a href="{{ asset($item->path) }}" target="_blank"
                       class="bg-white text-gray-900 text-xs font-semibold px-4 py-1.5 rounded-lg shadow">
                        {{__('View')}}
                    </a>

                    <button
                        onclick="selectFile('{{ $item->id }}', '{{ asset($item->full_url) }}')"
                        class="bg-blue-600 text-white text-xs font-semibold px-4 py-1.5 rounded-lg shadow hover:bg-blue-700">
                        {{__('Select')}}
                    </button>

                </div>

            </div>
        @endforeach

    </div>
@else
    <p class="text-red-500 text-center py-6 text-lg">{{ __('No item found') }}</p>
@endif
