<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Participant Report</title>
     <!-- Fonts -->
     <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Styles -->
    @livewireStyles
    <style>
        body {
            font-family: 'Nunito', sans-serif;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header img {
            width: 100px;
            height: auto;
        }

        .header h1 {
            font-size: 2rem;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="header">
    <div class="flex justify-center lg:col-start-2">
                        <img src="{{ Vite::asset('resources/images/logo.png') }}" class="img-fluid rounded-circle"
                            alt="Logo" style="width: 120px; height: 120px; border-radius: 50%;">
                    </div>
        <h1>Mathematics Challenge Report</h1>
    </div>

    <div class="max-w-3xl mx-auto bg-white p-6 rounded-lg shadow-lg">
        <div class="flex items-center justify-between mb-6">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-16">
            <h1 class="text-2xl font-bold text-gray-800">Participant Report</h1>
        </div>
        <div class="mb-4">
            
            <h2 class="text-xl font-semibold text-gray-700">Report for: {{ $participant->user->firstname }}
                {{ $participant->user->lastname }}</h2>
            <p class="text-gray-600">School: {{ $participant->school->name }}</p>
            <p class="text-gray-600">Challenge: {{ $challenge->title }}</p>
        </div>
        <div class="mb-4">
            <h3 class="text-lg font-semibold text-gray-700">Performance</h3>
            <p class="text-gray-600">Total Score: {{ $participant->total_score }}</p>
            <p class="text-gray-600">Time Taken: {{ $participant->time_taken }}</p>
        </div>
        <div class="mb-4">
            <h3 class="text-lg font-semibold text-gray-700">Mathematics Challenge</h3>
            <p class="text-gray-600">Score: {{ $participant->math_score }}</p>
            <p class="text-gray-600">Time Taken: {{ $participant->math_time_taken }}</p>
        </div>
        <div>
            <h3 class="text-lg font-semibold text-gray-700">Other Details</h3>
            <p class="text-gray-600">Completed: {{ $participant->completed ? 'Yes' : 'No' }}</p>
            <p class="text-gray-600">Attempts Left: {{ $participant->attempts_left }}</p>
        </div>
    </div>

    @stack('modals')

@livewireScripts
</body>

</html>