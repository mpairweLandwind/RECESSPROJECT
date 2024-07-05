<div>
    <!-- Live as if you were to die tomorrow. Learn as if you were to live forever. - Mahatma Gandhi -->
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
            <h2 class="text-2xl font-bold mb-4">Set Challenges</h2>

            <form action="{{ route('setChallenge') }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="mb-4">
                        <label for="title" class="block mb-2 text-sm font-medium text-gray-300">Title</label>
                        <input type="text" id="title" name="title"
                            class="block w-full text-sm text-gray-900 bg-gray-50 rounded border border-gray-300 cursor-pointer focus:outline-none">
                    </div>

                    <div class="mb-4">
                        <label for="description"
                            class="block mb-2 text-sm font-medium text-gray-300">Description</label>
                        <textarea id="description" name="description"
                            class="block w-full text-sm text-gray-900 bg-gray-50 rounded border border-gray-300 cursor-pointer focus:outline-none"></textarea>
                    </div>

                    <div class="mb-4">
                        <label for="start_date" class="block mb-2 text-sm font-medium text-gray-300">Start Date</label>
                        <input type="datetime-local" id="start_date" name="start_date"
                            class="block w-full text-sm text-gray-900 bg-gray-50 rounded border border-gray-300 cursor-pointer focus:outline-none">
                    </div>

                    <div class="mb-4">
                        <label for="end_date" class="block mb-2 text-sm font-medium text-gray-300">End Date</label>
                        <input type="datetime-local" id="end_date" name="end_date"
                            class="block w-full text-sm text-gray-900 bg-gray-50 rounded border border-gray-300 cursor-pointer focus:outline-none">
                    </div>

                    <div class="mb-4">
                        <label for="duration" class="block mb-2 text-sm font-medium text-gray-300">Duration
                            (minutes)</label>
                        <input type="number" id="duration" name="duration"
                            class="block w-full text-sm text-gray-900 bg-gray-50 rounded border border-gray-300 cursor-pointer focus:outline-none">
                    </div>

                    <div class="mb-4">
                        <label for="number_of_questions" class="block mb-2 text-sm font-medium text-gray-300">Number of
                            Questions</label>
                        <input type="number" id="number_of_questions" name="number_of_questions"
                            class="block w-full text-sm text-gray-900 bg-gray-50 rounded border border-gray-300 cursor-pointer focus:outline-none">
                    </div>
                </div>

                <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded mt-4">Set Challenge</button>
            </form>
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
    });
</script>