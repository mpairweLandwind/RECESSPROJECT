<x-app-layout>
    <x-slot name="header">
        <h2 class="font-extrabold text-2xl text-white leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                @if (!isset($section) || $section === 'welcome')
                    <x-welcome />
                @elseif ($section === 'upload-questions')
                    <x-upload-questions />
                @elseif ($section === 'set-challenges')
                    <x-set-challenges />
                @elseif ($section === 'analytics')  
                    <x-analytics />
                @elseif ($section === 'settings')
                    <x-settings />
                @elseif ($section === 'reports')
                    <x-reports :participants="$participants" :schools="$schools" :challenge="$challenge" />

                @else
                    <x-welcome />
                @endif
            </div>
        </div>
    </div>
</x-app-layout>