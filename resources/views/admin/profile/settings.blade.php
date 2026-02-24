<x-layout.default>

    <div>
        <ul class="flex space-x-2 rtl:space-x-reverse">
            <li>
                <a href="javascript:;" class="text-primary hover:underline">{{__('Users')}}</a>
            </li>
            <li class="before:content-['/'] ltr:before:mr-1 rtl:before:ml-1">
                <span>{{__('Update Profile')}}</span>
            </li>
        </ul>
        <div class="pt-5">

            <div x-data="{ tab: 'home' }">
                <ul
                    class="sm:flex font-semibold border-b border-[#ebedf2] dark:border-[#191e3a] mb-5 whitespace-nowrap overflow-y-auto">
                    <li class="inline-block">
                        <a href="javascript:;"
                           class="flex gap-2 p-4 border-b border-transparent hover:border-primary hover:text-primary"
                           :class="{ '!border-primary text-primary': tab == 'home' }" @click="tab='home'">

                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                 xmlns="http://www.w3.org/2000/svg" class="w-5 h-5">
                                <path opacity="0.5"
                                      d="M2 12.2039C2 9.91549 2 8.77128 2.5192 7.82274C3.0384 6.87421 3.98695 6.28551 5.88403 5.10813L7.88403 3.86687C9.88939 2.62229 10.8921 2 12 2C13.1079 2 14.1106 2.62229 16.116 3.86687L18.116 5.10812C20.0131 6.28551 20.9616 6.87421 21.4808 7.82274C22 8.77128 22 9.91549 22 12.2039V13.725C22 17.6258 22 19.5763 20.8284 20.7881C19.6569 22 17.7712 22 14 22H10C6.22876 22 4.34315 22 3.17157 20.7881C2 19.5763 2 17.6258 2 13.725V12.2039Z"
                                      stroke="currentColor" stroke-width="1.5" />
                                <path d="M12 15L12 18" stroke="currentColor" stroke-width="1.5"
                                      stroke-linecap="round" />
                            </svg>
                            {{__("Profile")}}
                        </a>
                    </li>
                    <li class="inline-block">
                        <a href="javascript:;"
                           class="flex gap-2 p-4 border-b border-transparent hover:border-primary hover:text-primary"
                           :class="{ '!border-primary text-primary': tab == 'preferences' }"
                           @click="tab='preferences'">

                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                 xmlns="http://www.w3.org/2000/svg" class="w-5 h-5">
                                <circle cx="12" cy="6" r="4" stroke="currentColor"
                                        stroke-width="1.5" />
                                <ellipse opacity="0.5" cx="12" cy="17" rx="7" ry="4"
                                         stroke="currentColor" stroke-width="1.5" />
                            </svg>
                            {{__('Password & Security')}}
                        </a>
                    </li>
                </ul>
                <template x-if="tab === 'home'">
                    <div>
                        <form method="POST"  action="{{ route('updateProfile') }}" enctype="multipart/form-data"
                            class="border border-[#ebedf2] dark:border-[#191e3a] rounded-md p-4 mb-5 bg-white dark:bg-[#0e1726]">
                            @csrf
                            <h6 class="text-lg font-bold mb-5">{{ __("Basic Information") }}</h6>
                            <div class="flex flex-col sm:flex-row">
                                <div
                                    x-data="fileManager('{{ userImage($data['data']->image) }}', 'image')"
                                    class="space-y-2 ltr:sm:mr-4 rtl:sm:ml-4 w-full sm:w-2/12 mb-5"
                                >
                                    <!-- Trigger File Manager -->
                                    <button
                                        type="button"
                                        @click="$dispatch('open-file-manager', { callback: callbackName })"
                                        class="px-3 py-1.5 bg-gray-200 text-gray-800 text-sm rounded hover:bg-gray-300 transition"
                                    >
                                        Choose Image
                                    </button>

                                    <!-- Hidden input -->
                                    <input type="hidden" name="image" x-model="fileUrl">

                                    <!-- Preview -->
                                    <template x-if="filePreview">
                                        <div class="mt-2">
                                            <img
                                                :src="filePreview"
                                                class="rounded border object-cover"
                                                width="150"
                                            >
                                        </div>
                                    </template>
                                </div>

                                <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-5">
                                    <div class="mb-2">
                                        <label for="name" class="">{{ __('Name') }}</label>
                                        <div class="flex">
                                            {!! defaultInputIcon() !!}
                                            <input name="name" type="text" value="{{ old('name', $data['data']->name ?? '') }}" class="form-input ltr:rounded-l-none rtl:rounded-r-none" />
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <label for="gender" class="block text-gray-700 font-medium mb-2">{{ __('Gender') }}</label>
                                        <div class="flex">
                                            {!! defaultInputIcon() !!}
                                            <select name="gender" id="gender" class="form-select">
                                                <option value="">{{__('Select')}}</option>
                                                @foreach(\App\Enums\GenderEnum::getGenderArray() as $value => $label)
                                                    <option
                                                        value="{{ $value }}"
                                                        {{ old('gender', $data['data']->gender ?? '') == $value ? 'selected' : '' }}
                                                    >
                                                    {{ __($label) }}
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <label for="date_of_birth" class="">{{ __('Date Of Birth') }}</label>
                                        <div class="flex">
                                            {!! defaultInputIcon() !!}
                                            <input name="date_of_birth" type="date" value="{{ old('date_of_birth', $data['data']->date_of_birth ?? '') }}" class="form-input ltr:rounded-l-none rtl:rounded-r-none" />
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <label for="blood_group" class="">{{ __('Blood Group') }}</label>
                                        <div class="flex">
                                            {!! defaultInputIcon() !!}
                                            <input name="blood_group" type="text" value="{{ old('blood_group', $data['data']->blood_group ?? '') }}" class="form-input ltr:rounded-l-none rtl:rounded-r-none" />
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <label for="address" class="">{{ __('Address') }}</label>
                                        <div class="flex">
                                            {!! defaultInputIcon() !!}
                                            <input name="address" type="text" value="{{ old('address', $data['data']->address ?? '') }}" class="form-input ltr:rounded-l-none rtl:rounded-r-none" />
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <label for="status" class="block text-gray-700 font-medium mb-2">{{ __('Account Deactive') }}</label>
                                        <div class="flex">
                                            {!! defaultInputIcon() !!}
                                            <select name="status" id="status" class="form-select">
                                                <option value="">{{__('Select')}}</option>
                                                @foreach(\App\Enums\StatusEnum::getDeactiveArray() as $value => $label)
                                                    <option
                                                        value="{{ $value }}"
                                                        {{ old('status', $data['data']->status ?? '') == $value ? 'selected' : '' }}
                                                    >
                                                    {{ __($label) }}
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="sm:col-span-2 mt-3">
                                        <button type="submit" class="btn btn-secondary">{{__('Update')}}</button>
                                    </div>
                                </div>
                            </div>
                        </form>

                    </div>
                </template>

                <template x-if="tab === 'preferences'">
                    <div>
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-5">
                            <div>
                                <form method="POST"  action="{{ route('updatePassword') }}" enctype="multipart/form-data"
                                      class="border border-[#ebedf2] dark:border-[#191e3a] rounded-md p-4 mb-5 bg-white dark:bg-[#0e1726]">
                                    @csrf
                                    <h6 class="text-lg font-bold mb-5">{{ __("Change Password") }}</h6>
                                    <div class="flex flex-col sm:flex-row">
                                        <div class="flex-1 grid grid-cols-1  gap-5">
                                            <div class="mb-2">
                                                <label for="current_password" class="">{{ __('Current Password') }}</label>
                                                <div class="flex">
                                                    <input name="current_password" type="password"  class="form-input ltr:rounded-l-none rtl:rounded-r-none" />
                                                </div>
                                            </div>
                                            <div class="mb-2">
                                                <label for="new_password" class="">{{ __('New Password') }}</label>
                                                <div class="flex">
                                                    <input name="new_password" type="password"  class="form-input ltr:rounded-l-none rtl:rounded-r-none" />
                                                </div>
                                            </div>
                                            <div class="mb-2">
                                                <label for="confirm_password" class="">{{ __('Old Password') }}</label>
                                                <div class="flex">
                                                    <input name="confirm_password" type="password"  class="form-input ltr:rounded-l-none rtl:rounded-r-none" />
                                                </div>
                                            </div>

                                            <div class="sm:col-span-2 mt-3">
                                                <button type="submit" class="btn btn-secondary">{{__('Change')}}</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>

                        </div>

                    </div>
                </template>

            </div>
        </div>
    </div>

</x-layout.default>
