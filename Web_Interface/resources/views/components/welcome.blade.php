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
                        <div class="text-xs text-gray-400">Repeated Questions: {{ $participant->repeated_question_count }}</div>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
<!-- School Rankings Section -->
<div class="bg-gray-800 p-6 rounded-lg shadow-lg">
    <div class="text-xl font-bold mb-4">School Rankings</div>
    <canvas id="schoolRankingsChart"></canvas>
</div>

<!-- Monthly Registrations Chart Section -->
<div class="bg-gray-800 p-6 rounded-lg shadow-lg">
    <div class="text-xl font-bold mb-4">Monthly Registrations</div>
    <canvas id="monthlyRegistrationsChart"></canvas>
    </div>
</div>

<!-- Additional Graph Section -->
<div class="bg-gray-800 p-6 rounded-lg shadow-lg mt-8">
        <div class="text-xl font-bold mb-4">Best Students in Mathematics Challenge</div>
        <table class="min-w-full bg-gray-800 text-white border-collapse">
            <thead>
                <tr>
                    <th class="py-2 px-4 border-b border-gray-700">Name</th>
                    <th class="py-2 px-4 border-b border-gray-700">School</th>
                    <th class="py-2 px-4 border-b border-gray-700">Score</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($topParticipants as $participant)
                    <tr>
                        <td class="py-2 px-4 border-b border-gray-700">{{ $participant->user->name }}</td>
                        <td class="py-2 px-4 border-b border-gray-700">{{ $participant->school->name }}</td>
                        <td class="py-2 px-4 border-b border-gray-700">{{ $participant->total_score }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>


<!-- Include Chart.js for the chart -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>



<script>

           
    // Overview Chart
    var ctxOverview = document.getElementById('overviewChart').getContext('2d');
    var overviewChart = new Chart(ctxOverview, {
        type: 'line',
        data: {
            labels: @json($performanceChartLabels),
            datasets: [{
                label: 'Total Score',
                data: @json(array_column($performanceChartData, 'total_score')),
                borderColor: 'rgba(75, 192, 192, 1)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                fill: true,
                tension: 0.4
            }, {
                    label: 'High Score',
                    data: @json(array_column($performanceChartData, 'high_score')),
                    borderColor: 'rgba(153, 102, 255, 1)',
                    backgroundColor: 'rgba(153, 102, 255, 0.2)',
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



    document.addEventListener('DOMContentLoaded', function () {
        var performanceChartData = @json($performanceChartData);

        var labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        var totalScores = Array(12).fill(0);
        var highScores = Array(12).fill(0);
        var participantCounts = Array(12).fill(0);
        var schoolNames = Array(12).fill('');

        // Populate data arrays
        for (var month in performanceChartData) {
            var monthIndex = parseInt(month) - 1;
            totalScores[monthIndex] = performanceChartData[month].total_score || 0;
            highScores[monthIndex] = performanceChartData[month].high_score || 0;
            participantCounts[monthIndex] = performanceChartData[month].participant_count || 0;
            var schools = performanceChartData[month].schools;
            schoolNames[monthIndex] = Object.keys(schools).join(', ');
        }

    // Create the chart
    var ctxSchoolRankings = document.getElementById('schoolRankingsChart').getContext('2d');
    var schoolRankingsChart = new Chart(ctxSchoolRankings, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Total Score',
                    data: totalScores,
                    borderColor: 'rgba(255, 99, 132, 1)',
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    fill: false,
                    tension: 0.4
                },
                {
                    label: 'High Score',
                    data: highScores,
                    borderColor: 'rgba(54, 162, 235, 1)',
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    fill: false,
                    tension: 0.4
                },
                {
                    label: 'Number of Participants',
                    data: participantCounts,
                    borderColor: 'rgba(255, 206, 86, 1)',
                    backgroundColor: 'rgba(255, 206, 86, 0.2)',
                    fill: false,
                    tension: 0.4
                }
            ]
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
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        title: function (tooltipItems) {
                            var index = tooltipItems[0].dataIndex;
                            return `${labels[index]}: ${schoolNames[index]}`;
                        },
                        label: function (tooltipItem) {
                            var datasetLabel = tooltipItem.dataset.label || '';
                            return `${datasetLabel}: ${tooltipItem.parsed.y}`;
                        }
                    }
                }
            }
        }
    });
    });




                        // Monthly Registrations Chart
                        var ctxMonthlyRegistrations = document.getElementById('monthlyRegistrationsChart').getContext('2d');
                        var monthlyRegistrationsChart = new Chart(ctxMonthlyRegistrations, {
                            type: 'line',
                            data: {
                                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                                datasets: [
                                    {
                                        label: 'Schools',
                                        data: @json(array_values($monthlyRegistrationData['schoolRegistrations'])),
                                        borderColor: 'rgba(75, 192, 192, 1)',
                                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                        fill: true,
                                        tension: 0.4
                                    },
                                    {
                                        label: 'Participants',
                                        data: @json(array_values($monthlyRegistrationData['participantRegistrations'])),
                                        borderColor: 'rgba(153, 102, 255, 1)',
                                        backgroundColor: 'rgba(153, 102, 255, 0.2)',
                                        fill: true,
                                        tension: 0.4
                                    }
                                ]
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