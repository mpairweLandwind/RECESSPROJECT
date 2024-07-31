<div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0"
    style="background: linear-gradient(to right, rgba(255, 0, 0, 0.5), rgba(128, 128, 128, 0.5)); backdrop-filter: blur(50px);">

    <div>
        {{ $logo }}
    </div>

    <div class="w-full text-white font-extrabold sm:max-w-md mt-6 px-6 py-4  shadow-md overflow-hidden sm:rounded-lg"
        style="background: linear-gradient(to right, rgba(255, 0, 0, 0.5), rgba(128, 128, 128, 0.5)); backdrop-filter: blur(50px);">
        {{ $slot }}
    </div>
</div>