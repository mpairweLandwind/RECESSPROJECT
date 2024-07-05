<nav x-data="{ open: false }" class="bg-slate-500 border-b border-gray-800">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <!-- Logo -->
                <div class="shrink-0 flex items-center w-20">
                    <a href="{{ route('dashboard') }}">
                        <img src="{{ Vite::asset('resources/images/logo.png') }}" class="img-fluid rounded-circle" alt="Logo" style="width: 65px; height: 65px; border-radius: 50%;">
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex  text-center font-extrabold text-xl">
                    <a href="{{ route('dashboard', ['section' => 'welcome']) }}" class="text-white hover:text-gray-400"></a>
                    <a href="{{ route('dashboard', ['section' => 'upload-questions']) }}" class="text-white hover:text-gray-400">Upload  Excel Files</a>
                    <a href="{{ route('dashboard', ['section' => 'set-challenges']) }}" class="text-white hover:text-gray-400">Set Challenges</a>
                    <a href="{{ route('dashboard', ['section' => 'analytics']) }}" class="text-white hover:text-gray-400">Analytics</a>
                    <a href="{{ route('dashboard', ['section' => 'settings']) }}" class="text-white hover:text-gray-400">Reports and Emails</a>
                  
                </div>
            </div>

            <div class="flex items-center">
                <!-- Search Bar -->
                <div class="relative">
                    <input type="text" placeholder="Search..." class="bg-gray-200 text-gray-800 rounded-full px-3 py-2 focus:outline-none focus:ring-2 focus:ring-gray-600">
                </div>

                <!-- User Dropdown -->
                <div class="relative ml-3" x-data="{ open: false }">
                    <button type="button" class="inline-flex items-center px-3 py-2 text-sm leading-4 font-medium text-white focus:outline-none transition ease-in-out duration-150" @click="open = !open">
                        <span class="text-xl font-bold">Welcome {{ auth()->user()->firstname }} {{ auth()->user()->lastname }}</span>
                        <svg class="ml-2  h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                        </svg>
                    </button>

                    <!-- Dropdown Menu -->
                    <div x-show="open" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 ring-1 ring-black ring-opacity-5 focus:outline-none" role="menu" aria-orientation="vertical" aria-labelledby="user-menu-button" tabindex="-1">
                        <!-- Account Management -->
                        <a href="{{ route('profile.show') }}" class="block px-4 py-2 text-sm text-gray-700" role="menuitem" tabindex="-1" id="user-menu-item-0">{{ __('Profile') }}</a>
                        @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                            <a href="{{ route('api-tokens.index') }}" class="block px-4 py-2 text-sm text-gray-700" role="menuitem" tabindex="-1" id="user-menu-item-1">{{ __('API Tokens') }}</a>
                        @endif

                        <div class="border-t border-gray-100"></div>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}" x-data>
                            @csrf
                            <a href="{{ route('logout') }}" @click.prevent="$root.submit();" class="block px-4 py-2 text-sm text-gray-700" role="menuitem" tabindex="-1" id="user-menu-item-2">{{ __('Log Out') }}</a>
                        </form>
                    </div>
                </div>

                <!-- Profile Image -->
                <!-- Profile Image -->
<div class="ml-3 relative  flex">
    
        <img class="h-14 w-16 rounded-full object-cover" src="{{ Auth::user()->profile_photo ? Storage::url(Auth::user()->profile_photo) : Vite::asset('resources/images/user.png') }}" alt="{{ Auth::user()->firstname }}" />

</div>

            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <a href="{{ route('dashboard') }}" class="block text-white hover:text-gray-400">Overview</a>
            <a href="{{ route('dashboard') }}" class="block text-white hover:text-gray-400">Customers</a>
            <a href="{{ route('dashboard')}}" class="block text-white hover:text-gray-400">Products</a>
            <a href="{{ route('dashboard')}}" class="block text-white hover:text-gray-400">Settings</a>
        </div>
    </div>
</nav>
