<x-layout.default>
    @section('title', $pageTitle)

    <div class="mt-8 bg-white shadow-xl rounded-2xl p-6 border border-gray-100">
    <!-- Header -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-4">
            <h5 class="text-2xl font-bold text-gray-800">{{ $pageTitle ?? __('Title') }}</h5>

            <!-- Upload Button -->
            <label class="relative inline-flex items-center px-5 py-2.5
           bg-gradient-to-r from-indigo-600 to-blue-600
           text-white font-semibold rounded-lg shadow-md hover:shadow-lg
           transition-all duration-300 cursor-pointer">

                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 4v16m8-8H4" />
                </svg>

                <span>{{__('Upload')}}</span>

                <input
                    type="file"
                    name="photo"
                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                    onchange="uploadFile(this)"
                >
            </label>
        </div>

        <div class="py-4">
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
                                {!! delete_column(route('fileManager.delete', $item->id)) !!}
                            </div>

                        </div>
                    @endforeach

                </div>
            @else
                <p class="text-red-500 text-center py-6 text-lg">{{ __('No item found') }}</p>
            @endif

        </div>
    </div>

    <script>
        function uploadFile(input) {
            let file = input.files[0];
            if (!file) return;

            let formData = new FormData();
            formData.append("photo", file);
            formData.append("_token", "{{ csrf_token() }}");

            axios.post("{{ route('fileManager.storeFile') }}", formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            })
                .then(function (response) {
                    console.log(response)
                    if (response?.data?.success) {
                        toastr.success(response?.data?.message);
                    } else {
                        toastr.error(response?.data?.message);
                    }

                    loadImages();

                    input.value = ""; // reset file input
                })
                .catch(function (error) {

                    toastr.error(error.response?.data?.error_message ?? "Upload failed!");
                    input.value = "";
                });
        }


        // Auto reload images
        function loadImages() {
            axios.get("{{ route('fileManager.partial') }}")
                .then(function (res) {
                    document.querySelector(".grid").outerHTML = res.data;
                })
                .catch(function () {
                    toastr.error("Failed to refresh images");
                });
        }
    </script>


</x-layout.default>
