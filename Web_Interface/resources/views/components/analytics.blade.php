<div>
    <!-- He who is contented is rich. - Laozi -->
</div>

<div class="p-6 lg:p-8 bg-gray-900 text-white">

    <!-- Success Message -->
    @if (session('success'))
    <div id="success-message" class="bg-green-500 text-white px-4 py-2 rounded mb-4">
        {{ session('success') }}
    </div>
    @endif

    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold mb-4">Analytics</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Most Correctly Answered Questions -->
                <div class="bg-gray-800 p-4 rounded shadow">
                    <h3 class="text-xl font-semibold mb-2">Most Correctly Answered Questions</h3>
                    <table class="table-auto w-full">
                        <thead>
                            <tr>
                                <th class="px-4 py-2 text-white">Question ID</th>
                                <th class="px-4 py-2 text-white">Question Text</th>
                                <th class="px-4 py-2 text-white">Correct Answers Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($mostCorrectlyAnsweredQuestions as $attemptedQuestion)
                            <tr>
                                <td class="border px-4 py-2 text-white">{{ $attemptedQuestion->question_id }}</td>
                                <td class="border px-4 py-2 text-white">{{ $attemptedQuestion->question->question_text }}</td>
                                <td class="border px-4 py-2 text-white">{{ $attemptedQuestion->count }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>


                <!-- School Rankings -->
                <div class="bg-gray-800 p-4 rounded shadow">
                    <h3 class="text-xl font-semibold mb-2">School Rankings by Total Score</h3>
                    <table class="table-auto w-full">
                        <thead>
                            <tr>
                                <th class="px-4 py-2 text-white">School Name</th>
                                <th class="px-4 py-2 text-white">Total Score</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($schoolRankings as $school)
                            <tr>
                                <td class="border px-4 py-2 text-white">{{ $school->name }}</td>
                                <td class="border px-4 py-2 text-white">{{ $school->total_score }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>


                <!-- Performance Over the Years -->
                <div class="bg-gray-800 p-4 rounded shadow">
                    <h3 class="text-xl font-semibold mb-2">Performance Over the Years</h3>
                    <canvas id="performanceChart"></canvas>
                </div>

                <!-- Question Repetition Percentage -->
                <div class="bg-gray-800 p-4 rounded shadow">
                    <h3 class="text-xl font-semibold mb-2"> Most 5 Repeated Questions and Their Percentage</h3>
                    <table class="table-auto w-full">
                        <thead>
                            <tr>
                                <th class="px-4 py-2 text-white">Question Text</th>
                                <th class="px-4 py-2 text-white">Repetition Percentage</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($questionRepetition as $attemptedQuestion)
                            <tr>
                                <td class="border px-4 py-2 text-white">{{ $attemptedQuestion->question->question_text }}</td>
                                <td class="border px-4 py-2 text-white">{{ number_format($attemptedQuestion->repetition_percentage, 2) }}%</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>


                <!-- Best Performing Schools -->
                <div class="bg-gray-800 p-4 rounded shadow">
                    <h3 class="text-xl font-semibold mb-2">Best Performing Schools</h3>
                    <table class="w-full text-gray-200">
                        <thead>
                            <tr>
                                <th class="text-left p-2">School Name</th>
                                <th class="text-left p-2">Average Score</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($bestPerformingSchools as $school)
                            <tr>
                                <td class="p-2">{{ $school->name }}</td>
                                <td class="p-2">{{ number_format($school->average_score, 2) }} points</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>



                <!-- Worst Performing Schools -->
                <div class="bg-gray-800 p-4 rounded shadow">
                    <h3 class="text-xl font-semibold mb-2">Worst Performing Schools</h3>
                    <table class="table-auto w-full">
                        <thead>
                            <tr>
                                <th class="px-4 py-2 text-white">School Name</th>
                                <th class="px-4 py-2 text-white">Total Score</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($worstPerformingSchools as $school)
                            <tr>
                                <td class="border px-4 py-2 text-white">{{ $school->name }}</td>
                                <td class="border px-4 py-2 text-white">{{ $school->total_score }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>


                <!-- Participants with Incomplete Challenges -->
                <div class="bg-gray-800 p-4 rounded shadow">
                    <h3 class="text-xl font-semibold mb-2">Participants with Incomplete Challenges</h3>
                    <ul>
                        @foreach ($incompleteParticipants as $participant)
                        <li>{{ $participant->name }}</li>
                        @endforeach
                    </ul>
                </div>

                <!-- Other Reports -->
                <div class="bg-gray-800 p-4 rounded shadow">
                    <h3 class="text-xl font-semibold mb-2">Other Reports</h3>
                    <!-- Add any other reports here -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Chart.js for the chart -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const successMessage = document.getElementById('success-message');
        if (successMessage) {
            setTimeout(() => {
                successMessage.style.display = 'none';
            }, 5000); // 5000ms = 5 seconds
        }

        // Initialize the performance chart data
        var performanceChartData = @json($performanceChartData);

        var labels = ['2024', '2025', '2026', '2027', '2028', '2029', '2030', '2031', '2032', '2033', '2034'];
        var highScores = Array(11).fill(0); // 11 years from 2024 to 2034
        var participantCounts = Array(11).fill(0);
        var schoolNames = Array(11).fill('');

        // Populate data arrays
        for (var year in performanceChartData) {
            var yearIndex = parseInt(year) - 2024;
            if (yearIndex >= 0 && yearIndex < 11) { // Ensure year is within range
                highScores[yearIndex] = performanceChartData[year].high_score || 0;
                participantCounts[yearIndex] = performanceChartData[year].participant_count || 0;
                var schools = performanceChartData[year].schools;
                schoolNames[yearIndex] = Object.keys(schools).join(', ');
            }
        }

        // Create the chart
        var ctxperformance = document.getElementById('performanceChart').getContext('2d');
        var schoolPerformanceChart = new Chart(ctxperformance, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
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
                        },
                        title: {
                            display: true,
                            text: 'Values',
                            color: '#fff'
                        }
                    },
                    x: {
                        ticks: {
                            color: '#fff'
                        },
                        title: {
                            display: true,
                            text: 'Years',
                            color: '#fff'
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            title: function(tooltipItems) {
                                var index = tooltipItems[0].dataIndex;
                                return `${labels[index]}: ${schoolNames[index]}`;
                            },
                            label: function(tooltipItem) {
                                var datasetLabel = tooltipItem.dataset.label || '';
                                return `${datasetLabel}: ${tooltipItem.parsed.y}`;
                            }
                        }
                    }
                }
            }
        });
    });
</script>