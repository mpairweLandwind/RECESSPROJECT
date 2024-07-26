<div>
    <h2 class="font-semibold text-2xl text-gray-800 leading-tight mb-6">
        {{ __('Send via Email and Print Reports') }}
    </h2>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
            <div id="successAlert" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                {{ session('success') }}
            </div>
            @endif

            <div class="bg-white shadow overflow-hidden sm:rounded-lg p-6 lg:p-8">
                <h3 class="font-semibold text-lg text-gray-800 leading-tight mb-4">
                    {{ __('Participant Reports') }}
                </h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                        <thead>
                            <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                                <th class="py-3 px-6 text-left">School Name</th>
                                <th class="py-3 px-6 text-left">Participant Name</th>
                                <th class="py-3 px-6 text-left">Challenge</th>
                                <th class="py-3 px-6 text-left">Total Score</th>
                                <!-- <th class="py-3 px-6 text-left">Time Taken</th> -->
                                <th class="py-3 px-6 text-left">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 text-sm font-light">
                            @foreach($participants as $participant)
                            @foreach($challenges as $challenge)
                            <tr class="border-b border-gray-200 hover:bg-gray-100">
                                <td class="py-3 px-6">{{ $participant->school->name }}</td>
                                <td class="py-3 px-6">{{ $participant->user->firstname }} {{ $participant->user->lastname }}</td>
                                <td class="py-3 px-6">{{ $challenge->title }}</td>
                                <td class="py-3 px-6">{{ $participant->total_score }}</td>
                                <!-- <td class="py-3 px-6">{{ $participant->time_taken }}</td> -->
                                <td class="py-3 px-2">
                                    <a href="{{ route('sendemail.participant.pdf', ['participant_id' => $participant->id, 'challenge_id' => $challenge->id]) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mr-2">
                                        <i class="fa-solid fa-envelope-circle-check"></i> Send PDF via Email
                                    </a>
                                    <a href="{{ route('generateprint.participant.pdf', ['participant_id' => $participant->id, 'challenge_id' => $challenge->id]) }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                        <i class="fa-solid fa-print fa-lg"></i> Download To Print
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <h3 class="font-semibold text-lg text-gray-800 leading-tight mt-8 mb-4">
                    {{ __('School Reports') }}
                </h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                        <thead>
                            <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                                <th class="py-3 px-6 text-left">School Name</th>
                                <th class="py-3 px-6 text-left">Challenge</th>
                                <th class="py-3 px-6 text-left">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 text-sm font-light">
                            @foreach($schools as $school)
                            @foreach($challenges as $challenge)
                            <tr class="border-b border-gray-200 hover:bg-gray-100">
                                <td class="py-3 px-6">{{ $school->name }}</td>
                                <td class="py-3 px-6">{{ $challenge->title }}</td>
                                <td class="py-3 px-6">
                                    <a href="{{ route('sendemail.school.pdf', ['school_id' => $school->id, 'challenge_id' => $challenge->id]) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mr-2">
                                        <i class="fa-solid fa-envelope-circle-check"></i> Send PDF via Email
                                    </a>
                                    <a href="{{ route('generateprint.school.pdf', ['school_id' => $school->id, 'challenge_id' => $challenge->id]) }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                        <i class="fa-solid fa-print fa-lg"></i> Download To Print
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>