<!DOCTYPE html>
<html>
<head>
    <title>School Report</title>
    <style>
        /* Add some basic styling */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .header, .content {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: #f5f5f5;
            text-align: center;
        }
        .content {
            background: #ffffff;
            padding: 20px;
            border: 1px solid #ddd;
            margin-bottom: 20px;
        }
        .content h2, .content h3 {
            margin: 0 0 10px;
        }
        .content p {
            margin: 0 0 10px;
        }
        .participants table {
            width: 100%;
            border-collapse: collapse;
        }
        .participants th, .participants td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .participants th {
            background: #f5f5f5;
        }
        .participants tr:nth-child(even) {
            background: #f9f9f9;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ Vite::asset('resources/images/logo.png') }}" alt="Logo" style="width: 120px; height: 120px; border-radius: 50%;">
        <h1>Mathematics Challenge Report</h1>
    </div>
    <div class="content">
        <h2>School Report</h2>
        <p><strong>School:</strong> {{ $school->name }}</p>
        <p><strong>District:</strong> {{ $school->district }}</p>
        <p><strong>Representative:</strong> {{ $school->representative_name }}</p>
        <h3>Challenges</h3>
        @foreach ($school->participants as $participant)
            <h4>Participant: {{ $participant->user->firstname }} {{ $participant->user->lastname }}</h4>
            @foreach ($participant->challenges as $challenge)
                <div class="challenge">
                    <p><strong>Challenge Title:</strong> {{ $challenge->title }}</p>
                    <p><strong>Description:</strong> {{ $challenge->description }}</p>
                    <p><strong>Duration:</strong> {{ $challenge->duration }} minutes</p>
                    <p><strong>Number of Questions:</strong> {{ $challenge->number_of_questions }}</p>
                </div>
            @endforeach
        @endforeach
        <h3>Participants</h3>
        <div class="participants">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Total Score</th>
                        <th>Time Taken</th>
                        <th>Math Score</th>
                        <th>Math Time Taken</th>
                        <th>Completed</th>
                        <th>Attempts Left</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($school->participants as $participant)
                        <tr>
                            <td>{{ $participant->user->firstname }} {{ $participant->user->lastname }}</td>
                            <td>{{ $participant->total_score }}</td>
                            <td>{{ $participant->time_taken }}</td>
                            <td>{{ $participant->math_score }}</td>
                            <td>{{ $participant->math_time_taken }}</td>
                            <td>{{ $participant->completed ? 'Yes' : 'No' }}</td>
                            <td>{{ $participant->attempts_left }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
