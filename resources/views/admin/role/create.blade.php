<x-layout.default>
    @section('title', $pageTitle)
    <div class="panel mt-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-4">
            <h5 class="text-2xl font-bold text-gray-800">{{ $pageTitle ?? __('App Slider') }}</h5>

            <a href="{{ route('role.syncPermission') }}"
               class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-blue-600 text-white font-medium rounded-lg shadow-md hover:shadow-lg hover:from-indigo-700 hover:to-blue-700 focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 4v16m8-8H4" />
                </svg>
                {{__('Sync Permission')}}
            </a>
        </div>
        <div>
            <form method="POST" action="{{ route('role.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div class="mb-2">
                        <label for="title" class="">{{ __('Name') }}</label>
                        @if(isset($item))
                            <input type="hidden" name="edit_id" value="{{ $item->id }}">
                            <input type="hidden" name="guard" value="{{$item->guard}}">
                        @else
                            <input type="hidden" name="guard" value="{{$type}}">
                        @endif

                        <div class="flex">
                            {!! defaultInputIcon() !!}
                            <input id="name" name="name" type="text" data-slug-source @if(isset($item)) value="{{ $item->name }}" @else value="{{ old('name') }}" @endif class="form-input ltr:rounded-l-none rtl:rounded-r-none" />
                        </div>
                    </div>
                    <div class="mb-2">
                        <label for="subtitle" class="">{{ __('Slug') }}</label>
                        <div class="flex">
                            {!! defaultInputIcon() !!}
                            <input id="slug" name="slug" type="text" data-slug-target @if(isset($item)) value="{{ $item->slug }}" @else value="{{ old('slug') }}" @endif class="form-input ltr:rounded-l-none rtl:rounded-r-none" />
                        </div>
                    </div>

                </div>
                <div>
                    <div>
                        <h3 class="text-bold text-xl md:text-2xl">{{__('Permission')}}</h3>
                        <p>{{__('Please click sync permission button to get updated route')}}</p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        @foreach($permissions as $module => $modulePermissions)
                            <div class="border rounded-lg p-3">
                                <h6 class="font-semibold mb-2 text-indigo-700 uppercase">
                                    {{ formatPermissionName($module) }}
                                </h6>

                                @foreach($modulePermissions as $permission)
                                    <label class="flex items-center space-x-2 mb-1">
                                        <input
                                            type="checkbox"
                                            name="permissions[]"
                                            value="{{ $permission->id }}"
                                            class="form-checkbox"
                                            @if(isset($item) && $item->permissions->contains($permission->id)) checked @endif
                                        >
                                        <span class="text-sm">
                                        {{ $permission->name }}
                                    </span>
                                    </label>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>



            @if(isset($item)) @customFields($item) @else @customFields(\App\Models\Role::class) @endif

                <div>
                    <button type="submit" class="btn btn-secondary mt-6">{{__('Submit')}}</button>
                </div>
            </form>
        </div>
    </div>

</x-layout.default>
