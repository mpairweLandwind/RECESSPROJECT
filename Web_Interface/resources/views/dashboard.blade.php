<x-app-layout>
    <x-slot name="header">
        <h2 class="font-extrabold text-2xl text-white leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            @switch($section)
                    @case('upload-questions')
                        <x-upload-questions />
                        @break
                    @case('set-challenges')
                        <x-set-challenges />
                        @break
                    @case('analytics')
                        <x-analytics />
                        @break
                    @case('settings')
                        <x-settings />
                        @break
                    @case('reports')
                        <x-reports />
                        @break
                    @default
                        <x-welcome />
                @endswitch
            </div>
        </div>
    </div>
</x-app-layout>