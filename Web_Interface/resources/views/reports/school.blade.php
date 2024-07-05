<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Report</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
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

        .content {
            margin: 20px;
        }

        .content p {
            margin-bottom: 10px;
        }

        .table-container {
            margin-top: 20px;
            border-collapse: collapse;
            width: 100%;
        }

        .table-container th,
        .table-container td {
            border: 1px solid #ddd;
            padding: 8px;
        }

        .table-container th {
            background-color: #f2f2f2;
            text-align: left;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="flex justify-center lg:col-start-2">
            <img src="{{ Vite::asset('resources/images/logo.png') }}" class="img-fluid rounded-circle" alt="Logo"
                style="width: 120px; height: 120px; border-radius: 50%;">
        </div>
        <h1>Mathematics Challenge Report</h1>
    </div>
    <div class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow-lg">
        <div class="flex items-center justify-between mb-6">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-16">
            <h1 class="text-2xl font-bold text-gray-800">School Report</h1>
        </div>
        <div class="mb-4">
            <h2 class="text-xl font-semibold text-gray-700">Report for: {{ $school->name }}</h2>
            <p class="text-gray-600">District: {{ $school->district }}</p>
            <p class="text-gray-600">Representative: {{ $school->representative->name }}</p>
        </div>
        <div class="mb-4">
            <h3 class="text-lg font-semibold text-gray-700">Challenge: {{ $challenge->title }}</h3>
            <p class="text-gray-600">Description: {{ $challenge->description }}</p>
            <p class="text-gray-600">Duration: {{ $challenge->duration }} minutes</p>
            <p class="text-gray-600">Number of Questions: {{ $challenge->number_of_questions }}</p>
        </div>
        <div>
            <h3 class="text-lg font-semibold text-gray-700">Participants</h3>
            <table class="min-w-full bg-white border border-gray-300">
                <thead>
                    <tr>
                        <th class="py-2 px-4 border-b">Name</th>
                        <th class="py-2 px-4 border-b">Total Score</th>
                        <th class="py-2 px-4 border-b">Time Taken</th>
                        <th class="py-2 px-4 border-b">Math Score</th>
                        <th class="py-2 px-4 border-b">Math Time Taken</th>
                        <th class="py-2 px-4 border-b">Completed</th>
                        <th class="py-2 px-4 border-b">Attempts Left</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($school->participants as $participant)
                        <tr class="bg-gray-100 even:bg-white">
                            <td class="py-2 px-4 border-b">{{ $participant->user->firstname }}
                                {{ $participant->user->lastname }}
                            </td>
                            <td class="py-2 px-4 border-b">{{ $participant->total_score }}</td>
                            <td class="py-2 px-4 border-b">{{ $participant->time_taken }}</td>
                            <td class="py-2 px-4 border-b">{{ $participant->math_score }}</td>
                            <td class="py-2 px-4 border-b">{{ $participant->math_time_taken }}</td>
                            <td class="py-2 px-4 border-b">{{ $participant->completed ? 'Yes' : 'No' }}</td>
                            <td class="py-2 px-4 border-b">{{ $participant->attempts_left }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @stack('modals')

    @livewireScripts
</body>

</html>