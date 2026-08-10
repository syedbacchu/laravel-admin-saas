<x-layout.default>
    @section('title', $pageTitle)
    <div class="panel mt-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100 mb-6">{{ $pageTitle }}</h1>
        <div>
            <form method="POST" action="{{ route($formRoute ?? 'appSlider.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-common.file-manager-upload
                            name="photo"
                            :value="$item->photo ?? ''"
                            label="Slider Image"
                            helpText="Recommended: 1920x1080px"
                        />
                    </div>

                    {{-- Mobile Slider Image --}}
                    <div>
                        <x-common.file-manager-upload
                            name="mobile_banner"
                            :value="$item->mobile_banner ?? ''"
                            label="Mobile Slider Image"
                            helpText="Recommended: 375x812px"
                        />
                    </div>


                    <div class="mb-2">
                        <label for="title" class="">{{ __('Title') }}</label>
                        <input type="hidden" name="type" value="{{ $type }}">
                        @if(isset($item))
                            <input type="hidden" name="edit_id" value="{{ $item->id }}">
                        @endif
                        <div class="flex">
                            {!! defaultInputIcon() !!}
                            <input name="title" type="text" @if(isset($item)) value="{{ $item->title }}" @else
                                value="{{ old('title') }}" @endif
                                   class="form-input ltr:rounded-l-none rtl:rounded-r-none" />
                        </div>
                    </div>
                    <div class="mb-2">
                        <label for="subtitle" class="">{{ __('Sub Title') }}</label>
                        <div class="flex">
                            {!! defaultInputIcon() !!}
                            <input name="subtitle" type="text" @if(isset($item)) value="{{ $item->subtitle }}" @else
                                value="{{ old('subtitle') }}" @endif
                                   class="form-input ltr:rounded-l-none rtl:rounded-r-none" />
                        </div>
                    </div>
                    <div class="mb-2 md:col-span-2">
                        <label for="description" class="">{{ __('Description') }}</label>
                        <div class="flex">
                            {!! defaultInputIcon() !!}
                            <input name="description" type="text" @if(isset($item)) value="{{ $item->description }}" @else
                                value="{{ old('description') }}" @endif
                                   class="form-input ltr:rounded-l-none rtl:rounded-r-none" />
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div class="mb-2">
                        <label for="tagline" class="">{{ __('Tagline') }}</label>
                        <div class="flex">
                            {!! defaultInputIcon() !!}
                            <input name="tagline" type="text" @if(isset($item)) value="{{ $item->tagline }}" @else
                                value="{{ old('tagline') }}" @endif
                                   class="form-input ltr:rounded-l-none rtl:rounded-r-none" />
                        </div>
                    </div>
                    <div class="mb-2">
                        <label for="link" class="">{{ __('Link') }}</label>
                        <div class="flex">
                            {!! defaultInputIcon() !!}
                            <input name="link" type="text" @if(isset($item)) value="{{ $item->link }}" @else
                                value="{{ old('link') }}" @endif
                                   class="form-input ltr:rounded-l-none rtl:rounded-r-none" />
                        </div>
                    </div>
                    @if ($type== \App\Enums\SliderTypeEnum::WEB)
                        <div class="mb-2">
                            <label for="page" class="">{{ __('Page') }}</label>
                            <div class="flex">
                                {!! defaultInputIcon() !!}
                                <input name="page" type="text" @if(isset($item)) value="{{ $item->page }}" @else
                                    value="{{ old('page') }}" @endif
                                       class="form-input ltr:rounded-l-none rtl:rounded-r-none" />
                            </div>
                        </div>
                    @endif

                    <div class="mb-2">
                        <label for="position" class="">{{ __('Sort Order') }}</label>
                        <div class="flex">
                            {!! defaultInputIcon() !!}
                            <input name="serial" type="number" @if(isset($item)) value="{{ $item->serial }}" @else
                                value="{{ old('serial') }}" @endif
                                   class="form-input ltr:rounded-l-none rtl:rounded-r-none" />
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="video_link" class="block text-gray-700 font-medium mb-2">{{ __('Video Link') }}</label>
                        <div class="flex">
                            {!! defaultInputIcon() !!}
                            <input name="video_link" type="text" @if(isset($item)) value="{{ $item->video_link }}" @else
                                value="{{ old('video_link') }}" @endif
                                   class="flex-1 border border-gray-300 rounded-r-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" />
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="mt-6">
                        <h3 class="font-semibold text-lg mb-3">CTA Buttons</h3>

                        <div id="cta-wrapper">
                            @php
                                $ctaButtons = old('cta_button', $item->cta_button ?? []);
                                $ctaButtons = is_array($ctaButtons) ? $ctaButtons : json_decode($ctaButtons, true);
                            @endphp

                            @foreach($ctaButtons ?? [] as $index => $cta)
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-3 border p-3 rounded">
                                    <input type="text" name="cta_button[{{ $index }}][label]"
                                           value="{{ $cta['label'] ?? '' }}" placeholder="Label" class="form-input">

                                    <input type="text" name="cta_button[{{ $index }}][link]"
                                           value="{{ $cta['link'] ?? '' }}" placeholder="Link" class="form-input">

                                    <input type="text" name="cta_button[{{ $index }}][colour_code]"
                                           value="{{ $cta['colour_code'] ?? '' }}" placeholder="#000000" class="form-input">

                                    <input type="number" name="cta_button[{{ $index }}][sort_order]"
                                           value="{{ $cta['sort_order'] ?? 0 }}" placeholder="Sort" class="form-input">

                                    <button type="button"
                                            onclick="this.closest('.grid').remove()"
                                            class="btn btn-danger btn-sm">
                                        Remove
                                    </button>
                                </div>
                            @endforeach
                        </div>

                        <button type="button" onclick="addCTA()" class="btn btn-primary btn-sm">
                            + Add Button
                        </button>
                    </div>


                    <div class="mt-6">
                        <h3 class="font-semibold text-lg mb-3">Stat</h3>

                        <div id="stat-wrapper">
                            @php
                                $stats = old('stat', $item->stat ?? []);
                                $stats = is_array($stats) ? $stats : json_decode($stats, true);
                            @endphp

                            @foreach($stats ?? [] as $index => $stat)
                                <div class="grid grid-cols-1 md:grid-cols-5 gap-3 mb-3 border p-3 rounded">

                                    {{-- Stat Image --}}
                                    <div>
                                        @php
                                            $statValue = $stat['image'] ?? '';
                                        @endphp
                                        <div x-data="fileManager('{{ $statValue }}', 'stat_image_{{ $index }}')">
                                            <x-common.file-manager-upload
                                                name="stat[{{ $index }}][image]"
                                                :value="$statValue"
                                                label="Stat Image"
                                            />
                                        </div>
                                    </div>

                                    <input type="text" name="stat[{{ $index }}][title]" value="{{ $stat['title'] ?? '' }}"
                                           placeholder="Title" class="form-input">

                                    <input type="text" name="stat[{{ $index }}][subtitle]"
                                           value="{{ $stat['subtitle'] ?? '' }}" placeholder="Subtitle" class="form-input">

                                    <input type="text" name="stat[{{ $index }}][link]" value="{{ $stat['link'] ?? '' }}"
                                           placeholder="Link" class="form-input">

                                    <input type="number" name="stat[{{ $index }}][sort_order]"
                                           value="{{ $stat['sort_order'] ?? 0 }}" placeholder="Sort" class="form-input">

                                    <button type="button"
                                            onclick="this.closest('.grid').remove()"
                                            class="btn btn-danger btn-sm">
                                        Remove
                                    </button>
                                </div>
                            @endforeach
                        </div>

                        <button type="button" onclick="addStat()" class="btn btn-primary btn-sm">
                            + Add Stat
                        </button>
                    </div>
                </div>
                @if(isset($item)) @customFields($item) @else @customFields(\App\Models\Slider::class) @endif

                <div>
                    <button type="submit" class="btn btn-secondary mt-6">Submit</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let ctaIndex = {{ isset($ctaButtons) ? count($ctaButtons) : 0 }};
        let statIndex = {{ isset($stats) ? count($stats) : 0 }};

        function addCTA() {
            let html = `
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-3 border p-3 rounded">
                <input type="text" name="cta_button[${ctaIndex}][label]" placeholder="Label" class="form-input">
                <input type="text" name="cta_button[${ctaIndex}][link]" placeholder="Link" class="form-input">
                <input type="text" name="cta_button[${ctaIndex}][colour_code]" placeholder="#000000" class="form-input">
                <input type="number" name="cta_button[${ctaIndex}][sort_order]" value="0" class="form-input">
                <button type="button"
                        onclick="this.closest('.grid').remove()"
                        class="btn btn-danger btn-sm">
                    Remove
                </button>
            </div>`;
            document.getElementById('cta-wrapper').insertAdjacentHTML('beforeend', html);
            ctaIndex++;
        }

        function addStat() {
            let html = `
            <div class="grid grid-cols-1 md:grid-cols-5 gap-3 mb-3 border p-3 rounded">
                <div>
                    <div x-data="fileManager('', 'stat_image_'+statIndex)">
                        <x-common.file-manager-upload
                            name="stat[${statIndex}][image]"
                            value=""
                            label="Stat Image"
                        />
                    </div>
                </div>

                <input type="text" name="stat[${statIndex}][title]" placeholder="Title" class="form-input">
                <input type="text" name="stat[${statIndex}][subtitle]" placeholder="Subtitle" class="form-input">
                <input type="text" name="stat[${statIndex}][link]" placeholder="Link" class="form-input">
                <input type="number" name="stat[${statIndex}][sort_order]" value="0" placeholder="Sort" class="form-input">

                <button type="button"
                        onclick="this.closest('.grid').remove()"
                        class="btn btn-danger btn-sm">
                    Remove
                </button>

            </div>
            `;

            let wrapper = document.getElementById('stat-wrapper');
            wrapper.insertAdjacentHTML('beforeend', html);

            // IMPORTANT: re-init Alpine
            Alpine.initTree(wrapper);

            statIndex++;
        }
    </script>
</x-layout.default>
