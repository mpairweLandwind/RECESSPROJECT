<x-guest-layout>

    <x-authentication-card>
        <x-slot name="logo">
            <img src="{{ Vite::asset('resources/images/logo.png') }}" class="img-fluid rounded-circle" alt="Logo"
                style="width: 150px; height: 150px; border-radius: 50%;" />
        </x-slot>

        <x-validation-errors class="mb-4" />

        <form id="registration-form" method="POST" action="{{ route('register') }}" enctype="multipart/form-data">
            @csrf
            <div>
                <x-label for="username" value="{{ __('username') }}" class="text-white" />
                <x-input id="username" class="block mt-1 w-full" type="text" name="username" :value="old('username')"
                    required autocomplete="username" autofocus />*
            </div>
            <div class="mt-4>
                <x-label for=" firstname" value="{{ __('First Name') }}" class="text-white" />
            <x-input id="firstname" class="block mt-1 w-full" type="text" name="firstname" :value="old('firstname')"
                required autocomplete="firstname" />
            </div>

            <div class="mt-4">
                <x-label for="lastname" value="{{ __('Last Name') }}" class="text-white" />
                <x-input id="lastname" class="block mt-1 w-full" type="text" name="lastname" :value="old('lastname')"
                    required autocomplete="lastname" />
            </div>

            <div class="mt-4">
                <x-label for="date_of_birth" value="{{ __('Date of Birth') }}" class="text-white" />
                <x-input id="date_of_birth" class="block mt-1 w-full" type="date" name="date_of_birth"
                    :value="old('date_of_birth')" required autocomplete="bday" />
            </div>

            <div class="mt-4">
                <x-label for="school_reg_no" value="{{ __('School Reg No') }}" class="text-white" />
                <x-input id="school_reg_no" class="block mt-1 w-full" type="text" name="school_reg_no"
                    :value="old('school_reg_no')" autocomplete="school_reg_no" />
            </div>

            <div class="mt-4">
                <x-label for="email" value="{{ __('Email') }}" class="text-white" />
                <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required
                    autocomplete="username" />
            </div>

            <div class="mt-4">
                <x-label for="password" value="{{ __('Password') }}" class="text-white" />
                <x-input id="password" class="block mt-1 w-full" type="password" name="password" required
                    autocomplete="new-password" />
            </div>

            <div class="mt-4">
                <x-label for="password_confirmation" value="{{ __('Confirm Password') }}" class="text-white" />
                <x-input id="password_confirmation" class="block mt-1 w-full" type="password"
                    name="password_confirmation" required autocomplete="new-password" />
            </div>

            <div class="mt-4">
                <x-label for="profile_photo" value="{{ __('Profile Photo') }}" class="text-white" />
                <x-input id="profile_photo" class="block mt-1 w-full" type="file" name="profile_photo"
                    accept="image/*" />
            </div>

            <div class="mt-4">
                <x-label for="role" value="{{ __('Role') }}" class="text-white" />
                <select id="role" name="role" class="block mt-1 w-full text-slate-600" required>
                    <option value="admin">Admin</option>
                  <option value="representative">Representative</option>
                    <option value="participant">Participant</option>
                </select>
            </div>

            @if (Laravel\Jetstream\Jetstream::hasTermsAndPrivacyPolicyFeature())
                        <div class="mt-4">
                            <x-label for="terms">
                                <div class="flex items-center">
                                    <x-checkbox name="terms" id="terms" required />

                                    <div class="ms-2">
                                        {!! __('I agree to the :terms_of_service and :privacy_policy', [
                    'terms_of_service' => '<a target="_blank" href="' . route('terms.show') . '" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">' . __('Terms of Service') . '</a>',
                    'privacy_policy' => '<a target="_blank" href="' . route('policy.show') . '" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">' . __('Privacy Policy') . '</a>',
                ]) !!}
                                    </div>
                                </div>
                            </x-label>
                        </div>
            @endif

            <div class="flex items-center justify-end mt-4">
                <a class="underline text-sm text-gray-100 hover:text-gray-500 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    href="{{ route('login') }}">
                    {{ __('Already registered?') }}
                </a>

                <x-button class="ms-4">
                    {{ __('Register') }}
                </x-button>
            </div>
        </form>
    </x-authentication-card>
</x-guest-layout>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const roleSelect = document.getElementById('role');
        const studentRegNoInput = document.getElementById('school_reg_no');

        roleSelect.addEventListener('change', function () {
            if (roleSelect.value === 'participant') {
                studentRegNoInput.setAttribute('required', 'required');
            } else {
                studentRegNoInput.removeAttribute('required');
            }
        });

        // Trigger change event to set initial state
        roleSelect.dispatchEvent(new Event('change'));
    });
</script>