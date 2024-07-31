<div class="p-6 lg:p-8 bg-gray-900 text-white">
    <!-- Success Message -->
    @if (session('success'))
        <div id="success-message" class="bg-green-500 text-white px-4 py-2 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <!-- Dashboard Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold">Upload Excel Files</h1>
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

    <!-- Upload Section -->
    <div class="bg-gray-800 p-6 rounded-lg shadow-lg mt-8">
        <div class="text-xl font-bold mb-4">Upload Files</div>
        <form action="{{ route('uploadQuestions') }}" method="POST" enctype="multipart/form-data" class="mb-4">
            @csrf
            <label class="block mb-2 text-sm font-medium text-gray-300" for="questionsFile">Upload Questions
                File</label>
            <input type="file" id="questionsFile" name="questionsFile"
                class="block w-full text-sm text-gray-900 bg-gray-50 rounded border border-gray-300 cursor-pointer focus:outline-none">
            <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded mt-4">Upload Questions</button>
        </form>

        <form action="{{ route('uploadAnswers') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <label class="block mb-2 text-sm font-medium text-gray-300" for="answersFile">Upload Answers File</label>
            <input type="file" id="answersFile" name="answersFile"
                class="block w-full text-sm text-gray-900 bg-gray-50 rounded border border-gray-300 cursor-pointer focus:outline-none">
            <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded mt-4">Upload Answers</button>
        </form>
    </div>

    <!-- Additional Graph Section -->
    <div class="bg-gray-800 p-6 rounded-lg shadow-lg mt-8">
        <form action="{{ route('upload.schools') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div>
                <label class="block mb-2 text-sm font-medium text-gray-300" for="answersFile">Upload Schools
                    File</label>
                <label for="schoolsFile">Choose Excel File:</label>
                <input type="file" id="schoolsFile" name="schoolsFile" required>
            </div>
            <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded mt-4">Upload Schools</button>
        </form>
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