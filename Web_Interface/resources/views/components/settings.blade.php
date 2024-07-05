<div>
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Send via Email and Print Reports') }}
    </h2>
    
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div id="successAlert" class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
    
            <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                <h3 class="font-semibold text-lg text-gray-800 leading-tight">
                    {{ __('Participant Reports') }}
                </h3>
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th scope="col">School Name</th>
                            <th scope="col">Participant Name</th>
                            <th scope="col">Challenge</th>
                            <th scope="col">Total Score</th>
                            <th scope="col">Time Taken</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($participants as $participant)
                            @foreach($challenges as $challenge)
                                <tr class="table-default">
                                    <td>{{ $participant->school->name }}</td>
                                    <td>{{ $participant->user->firstname }} {{ $participant->user->lastname }}</td>
                                    <td>{{ $challenge->title }}</td>
                                    <td>{{ $participant->total_score }}</td>
                                    <td>{{ $participant->time_taken }}</td>
                                    <td>
                                        <a href="{{ route('sendemail.participant.pdf', ['participant_id' => $participant->id, 'challenge_id' => $challenge->id]) }}"
                                            class="btn btn-primary">
                                            <i class="fa-solid fa-envelope-circle-check"></i> Send PDF via Email
                                        </a>
                                        <a href="{{ route('generateprint.participant.pdf', ['participant_id' => $participant->id, 'challenge_id' => $challenge->id]) }}"
                                            class="btn btn-success">
                                            <i class="fa-solid fa-print fa-lg"></i> Download To Print
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
    
                <h3 class="font-semibold text-lg text-gray-800 leading-tight mt-8">
                    {{ __('School Reports') }}
                </h3>
                <table class="table table-hover mt-4">
                    <thead>
                        <tr>
                            <th scope="col">School Name</th>
                            <th scope="col">Challenge</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($schools as $school)
                            @foreach($challenges as $challenge)
                                <tr class="table-default">
                                    <td>{{ $school->name }}</td>
                                    <td>{{ $challenge->title }}</td>
                                    <td>
                                        <a href="{{ route('sendemail.school.pdf', ['school_id' => $school->id, 'challenge_id' => $challenge->id]) }}"
                                            class="btn btn-primary">
                                            <i class="fa-solid fa-envelope-circle-check"></i> Send PDF via Email
                                        </a>
                                        <a href="{{ route('generateprint.school.pdf', ['school_id' => $school->id, 'challenge_id' => $challenge->id]) }}"
                                            class="btn btn-success">
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