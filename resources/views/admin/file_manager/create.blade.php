<x-layout.default>
    @section('title', $pageTitle)
    <div class="panel mt-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100 mb-6">{{ $pageTitle }}</h1>
        <div>
            <form method="POST"  action="{{ route('fileManager.store') }}"  enctype="multipart/form-data">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <!-- Banner Upload with Preview inside -->
                    <div class="mb-4">
                        <label for="image" class="block text-gray-700 font-medium mb-2">Image</label>

                        <div id="dropzone"
                            class="relative flex flex-col items-center justify-center w-full h-40 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer">

                            <input id="fileInput" name="photo" type="file" accept="image/*" class="hidden" />

                            <!-- Preview image -->
                            <img id="imagePreview"
                                class="absolute inset-0 w-full h-full object-contain bg-white" />

                            <!-- Default message -->
                            <p id="dropMessage" class="text-gray-400" @if(isset($item) && !empty($item->photo)) style="display: none;" @endif>Drag & drop or click to upload</p>
                        </div>
                    </div>
                </div>
                <div>
                    <button type="submit" class="btn btn-secondary mt-6">{{__('Upload')}}</button>
                </div>
            </form>
        </div>
    </div>

<script>
    const dropzone = document.getElementById("dropzone");
    const fileInput = document.getElementById("fileInput");
    const preview = document.getElementById("imagePreview");
    const message = document.getElementById("dropMessage");

    dropzone.addEventListener("click", () => fileInput.click());

    fileInput.addEventListener("change", (e) => {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (ev) => {
                preview.src = ev.target.result;
                preview.style.display = "block";   // make visible
                message.style.display = "none";    // hide message
            };
            reader.readAsDataURL(file);
        }
    });
</script>
</x-layout.default>
