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
                    <ul>
                        @foreach ($mostCorrectlyAnsweredQuestions as $question)
                            <li>{{ $question->question_text }} - {{ $question->correct_answers_count }} correct answers</li>
                        @endforeach
                    </ul>
                </div>

                <!-- School Rankings -->
                <div class="bg-gray-800 p-4 rounded shadow">
                    <h3 class="text-xl font-semibold mb-2">School Rankings</h3>
                    <ul>
                        @foreach ($schoolRankings as $school)
                            <li>{{ $school->name }} - {{ $school->score }} points</li>
                        @endforeach
                    </ul>
                </div>

                <!-- Performance Over the Years -->
                <div class="bg-gray-800 p-4 rounded shadow">
                    <h3 class="text-xl font-semibold mb-2">Performance Over the Years</h3>
                    <canvas id="performanceChart"></canvas>
                </div>

                <!-- Question Repetition Percentage -->
                <div class="bg-gray-800 p-4 rounded shadow">
                    <h3 class="text-xl font-semibold mb-2">Question Repetition Percentage</h3>
                    <ul>
                        @foreach ($questionRepetition as $participant)
                            <li>{{ $participant->name }} - {{ $participant->repetition_percentage }}% repetition</li>
                        @endforeach
                    </ul>
                </div>

                <!-- Best Performing Schools -->
                <div class="bg-gray-800 p-4 rounded shadow">
                    <h3 class="text-xl font-semibold mb-2">Best Performing Schools</h3>
                    <ul>
                        @foreach ($bestPerformingSchools as $school)
                            <li>{{ $school->name }} - {{ $school->score }} points</li>
                        @endforeach
                    </ul>
                </div>

                <!-- worst Performing Schools -->
                <div class="bg-gray-800 p-4 rounded shadow">
                    <h3 class="text-xl font-semibold mb-2">Worst Performing Schools</h3>
                    <ul>
                        @foreach ($worstPerformingSchools as $school)
                            <li>{{ $school->name }} - Total Score: {{ $school->participants->sum('total_score') }}</li>
                        @endforeach
                    </ul>
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

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const successMessage = document.getElementById('success-message');
        if (successMessage) {
            setTimeout(() => {
                successMessage.style.display = 'none';
            }, 5000); // 5000ms = 5 seconds
        }

        // Initialize the performance chart
        const ctx = document.getElementById('performanceChart').getContext('2d');
        const performanceChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($performanceChartLabels),
                datasets: [{
                    label: 'Performance',
                    data: @json($performanceChartData),
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    });
</script>