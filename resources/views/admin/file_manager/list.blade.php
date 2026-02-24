<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset='utf-8' />
    <title>File Upload</title>

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&display=swap"
          rel="stylesheet" />

    <script src="/assets/js/perfect-scrollbar.min.js"></script>
    <script defer src="/assets/js/popper.min.js"></script>
    <script defer src="/assets/js/tippy-bundle.umd.min.js"></script>
    <script defer src="/assets/js/sweetalert.min.js"></script>
    @vite(['resources/css/app.css'])

    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
</head>

<body >

@include('components.toastr.toastr')

    <div class="mt-8 bg-white shadow-xl rounded-2xl p-6 border border-gray-100">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-4">

            <!-- Title -->
            <h5 class="text-2xl font-bold text-gray-800 tracking-tight">
                {{ $pageTitle ?? __('Files') }}
            </h5>

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
            @include('admin.file_manager.file_data')
        </div>
    </div>

    <script>
        function selectFile(id, url) {
            window.parent.dispatchEvent(
                new CustomEvent('file-selected', {
                    detail: { id: id, url: url }
                })
            );
        }


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
<script src="/assets/js/alpine-collaspe.min.js"></script>
<script src="/assets/js/alpine-persist.min.js"></script>
<script defer src="/assets/js/alpine-ui.min.js"></script>
<script defer src="/assets/js/alpine-focus.min.js"></script>
<script defer src="/assets/js/alpine.min.js"></script>
<script src="/assets/js/custom.js"></script>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

</body>

</html>
