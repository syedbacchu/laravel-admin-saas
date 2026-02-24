<div
    x-data="fileManagerModal()"
    x-show="open"
    x-cloak
    class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-[9999]"
>
    <div
        class="bg-white
               w-[95%] md:w-[90%] lg:w-[80%]
               h-[95vh] md:h-[90vh]
               rounded-2xl shadow-2xl
               relative overflow-hidden
               transform transition-all duration-300"
        x-transition.opacity
        x-transition.scale.origin.center
    >

        <!-- Close Button -->
        <button
            class="absolute top-4 right-4 text-gray-700 text-2xl font-bold hover:text-black z-50"
            @click="close()"
        >
            ✕
        </button>

        <!-- File Manager Iframe -->
        <iframe
            src="{{ route('fileManager.all') }}"
            class="absolute inset-0 w-full h-full border-0"
        ></iframe>

    </div>
</div>


<script>
    function fileManagerModal() {
        return {
            open: false,
            callback: null,

            init() {
                // Listen for open event
                window.addEventListener('open-file-manager', (e) => {
                    this.callback = e.detail.callback;
                    this.open = true;
                });

                // Listen for selected file coming from iframe
                window.addEventListener('file-selected', (e) => {
                    if (this.callback) {
                        window.dispatchEvent(new CustomEvent(this.callback, { detail: e.detail }));
                    }
                    this.close();
                });
            },

            close() {
                this.open = false;
            }
        }
    }
</script>
