<div class="p-6 lg:p-8 bg-gray-900 text-white">
    <!-- Dashboard Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold">Dashboard</h1>
            <div class="flex space-x-4 mt-4">
                <button
                    class="bg-gray-800 px-4 py-2 rounded transition duration-300 ease-in-out hover:bg-gray-700 hover:text-white focus:bg-gray-600 focus:text-white">Overview</button>
                <button
                    class="bg-gray-800 px-4 py-2 rounded transition duration-300 ease-in-out hover:bg-gray-700 hover:text-white focus:bg-gray-600 focus:text-white">Analytics</button>
                <button
                    class="bg-gray-800 px-4 py-2 rounded transition duration-300 ease-in-out hover:bg-gray-700 hover:text-white focus:bg-gray-600 focus:text-white">Reports</button>
                <button
                    class="bg-gray-800 px-4 py-2 rounded transition duration-300 ease-in-out hover:bg-gray-700 hover:text-white focus:bg-gray-600 focus:text-white">Notifications</button>
            </div>
        </div>
        <div class="flex items-center space-x-4 relative">
            <button id="calendarButton" class="bg-gray-800 text-white px-4 py-2 rounded flex items-center space-x-2">
                <i class="fas fa-calendar-alt"></i>
                <span id="selectedDate">Jan 20, 2023 - Feb 09, 2023</span>
            </button>

            <button class="bg-gray-800 text-white px-4 py-2 rounded">Download</button>
        </div>
    </div>

    <!-- Cards Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Schools Participating Card -->
        <div class="bg-gray-800 p-4 rounded-lg shadow-lg">
            <div class="text-xs text-gray-400">Schools Participating</div>
            <div class="text-2xl font-bold">{{ $schoolCount }}</div>
        </div>

        <!-- Active Participants Card -->
        <div class="bg-gray-800 p-4 rounded-lg shadow-lg">
            <div class="text-xs text-gray-400">Active Participants</div>
            <div class="text-2xl font-bold">{{ $activeParticipantsCount }}</div>
        </div>

        <!-- Rejected Participants Card -->
        <div class="bg-gray-800 p-4 rounded-lg shadow-lg">
            <div class="text-xs text-gray-400">Rejected Participants</div>
            <div class="text-2xl font-bold">{{ $rejectedParticipantsCount }}</div>
        </div>

        <!-- High Scores Card -->
        <div class="bg-gray-800 p-4 rounded-lg shadow-lg">
            <div class="text-xs text-gray-400">High Scores (Challenges 1, 2, 3)</div>
            <div class="text-2xl font-bold">1: {{ $highScores[1] ?? 'N/A' }}, 2: {{ $highScores[2] ?? 'N/A' }}, 3:
                {{ $highScores[3] ?? 'N/A' }}
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Overview Section -->
        <div class="bg-gray-800 p-6 rounded-lg shadow-lg">
            <div class="text-xl font-bold mb-4">Overview</div>
            <canvas id="overviewChart"></canvas>
        </div>

        <!-- Question Repetition Section -->
        <div class="bg-gray-800 p-6 rounded-lg shadow-lg">
            <div class="text-xl font-bold mb-4">Question Repetition</div>
            <ul>
                @foreach ($questionRepetition as $participant)
                    <li class="flex items-center mb-4">
                        <div>
                            <div class="text-sm font-bold">{{ $participant->user->name }}</div>
                            <div class="text-xs text-gray-400">Repeated Questions:
                                {{ $participant->repeated_question_count }}
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>

<!-- Additional Graph Section -->
<!-- School Rankings Section -->
<div class="bg-gray-800 p-6 rounded-lg shadow-lg mt-8">
    <div class="text-xl font-bold mb-4">School Rankings</div>
    <canvas id="schoolRankingsChart"></canvas>
</div>
</div>

<!-- Include Chart.js for the chart -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>



<script>

    document.addEventListener('DOMContentLoaded', function () {
        const calendarButton = document.getElementById('calendarButton');
        const selectedDate = document.getElementById('selectedDate');
        const calendar = document.getElementById('calendar');

        const fp = flatpickr(calendar, {
            mode: "range",
            dateFormat: "M d, Y",
            inline: true,
            onClose: function (selectedDates, dateStr, instance) {
                selectedDate.innerText = dateStr || 'Select Date';
                calendar.classList.remove('show');
            }
        });

        calendarButton.addEventListener('click', function (event) {
            event.stopPropagation();
            calendar.classList.toggle('show');
            fp.open();
        });

        document.addEventListener('click', function (event) {
            if (!calendar.contains(event.target) && !calendarButton.contains(event.target)) {
                calendar.classList.remove('show');
            }
        });
    });

    // Overview Chart
    var ctxOverview = document.getElementById('overviewChart').getContext('2d');
    var overviewChart = new Chart(ctxOverview, {
        type: 'line',
        data: {
            labels: @json($performanceChartLabels),
            datasets: [{
                label: 'Total Score',
                data: @json(array_values($performanceChartData)),
                borderColor: 'rgba(75, 192, 192, 1)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        color: '#fff'
                    }
                },
                x: {
                    ticks: {
                        color: '#fff'
                    }
                }
            }
        }
    });

    // School Rankings Chart
    var ctxSchoolRankings = document.getElementById('schoolRankingsChart').getContext('2d');
    var schoolRankingsChart = new Chart(ctxSchoolRankings, {
        type: 'bar',
        data: {
            labels: @json($schoolRankings->pluck('name')),
            datasets: [{
                label: 'Total Score',
                data: @json($schoolRankings->pluck('participants.*.total_score')->collapse()),
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1
            }, {
                label: 'High Score',
                data: @json($schoolRankings->pluck('participants.*.high_score')->collapse()),
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }, {
                label: 'Number of Participants',
                data: @json($schoolRankings->pluck('participants.*.participant_count')->collapse()),
                backgroundColor: 'rgba(255, 206, 86, 0.2)',
                borderColor: 'rgba(255, 206, 86, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        color: '#fff'
                    }
                },
                x: {
                    ticks: {
                        color: '#fff'
                    }
                }
            }
        }
    });

</script>