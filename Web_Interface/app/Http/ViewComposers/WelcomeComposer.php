<?php

namespace App\Http\ViewComposers;

use App\Models\School;
use App\Models\Participant;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use App\Models\RejectedParticipant;
use App\Models\AttemptedQuestion;
use App\Models\Question;
use App\Models\User;



class WelcomeComposer
{
    public function compose(View $view)
    {
        $schoolCount = School::count();
        $activeParticipantsCount = Participant::whereNotNull('challenge_id')
        ->where('time_taken', '>', 0)
        ->distinct('participant_id')
        ->count('participant_id');


        $rejectedParticipantsCount = RejectedParticipant::whereNotNull('reason')->count();


        $mostCorrectlyAnsweredQuestions = AttemptedQuestion::select('question_id', DB::raw('count(*) as count'))
        ->where('marks_awarded', '>', 0)
        ->groupBy('question_id')
        ->orderByDesc('count')
        ->take(5)
        ->with('question') // Assuming the relationship method is 'question'
        ->get();

        // Fetching the best two participants
        $topParticipants = Participant::with(['school', 'user'])
        ->orderBy('total_score', 'desc')
        ->take(2)
        ->get();


        $highScores = Participant::select('challenge_id', DB::raw('MAX(total_score) as high_score'))
        ->whereIn('challenge_id', [1, 2, 3]) // Filter for specific challenges
        ->groupBy('challenge_id')
        ->pluck('high_score', 'challenge_id')
        ->toArray();


        $schoolRankings = School::withCount('participants')
            ->with([
                'participants' => function ($query) {
                    $query->select('school_id', DB::raw('SUM(total_score) as total_score'), DB::raw('MAX(total_score) as high_score'))
                        ->groupBy('school_id')
                        ->orderBy('total_score', 'desc');
                }
            ])
            ->get();

        $performanceChartLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        $performanceChartData = $this->getPerformanceChartData();

        $questionRepetition = AttemptedQuestion::select('question_id', DB::raw('COUNT(*) as total_attempts'), DB::raw('SUM(case when is_repeated = true then 1 else 0 end) as repeated_count'), DB::raw('SUM(case when is_repeated = true then 1 else 0 end) / COUNT(*) * 100 as repetition_percentage'))
        ->groupBy('question_id')
        ->orderBy('repetition_percentage', 'desc')
        ->with('question')
        ->limit(5) // Assuming the relationship method is 'question'
        ->get();

        $worstPerformingSchools = School::with([
            'participants' => function ($query) {
                $query->select('school_id', DB::raw('CAST(SUM(total_score) AS INTEGER) as total_score'))
                    ->groupBy('school_id')
                    ->orderBy('total_score', 'asc');
            }
        ])->take(10)->get();

        $bestPerformingSchools = School::with([
            'participants' => function ($query) {
                $query->select('school_id', DB::raw('CAST(SUM(total_score) AS INTEGER) as total_score'))
                    ->groupBy('school_id')
                    ->orderBy('total_score', 'desc');
            }
        ])->take(5)->get();

        $incompleteParticipants = Participant::whereHas('challenges', function ($query) {
            $query->where('challenge_participants.status', 'incomplete');
        })->get();

        $monthlyRegistrationData = $this->getMonthlyRegistrationData();

       
     

        $view->with(
            compact(
                'schoolCount',
                'highScores',
                'topParticipants',
                'activeParticipantsCount',
                'rejectedParticipantsCount',
                'mostCorrectlyAnsweredQuestions',
                'schoolRankings',
                'performanceChartLabels',
                'performanceChartData',
                'questionRepetition',
                'worstPerformingSchools',
                'bestPerformingSchools',
                'incompleteParticipants',
                'monthlyRegistrationData'
                 
            )
        );
    }

    private function getPerformanceChartData()
{
    $data = array_fill(1, 12, ['total_score' => 0, 'high_score' => 0, 'schools' => [], 'participant_count' => 0]);

    $schools = School::with([
        'participants' => function ($query) {
            $query->select('school_id', DB::raw('EXTRACT(MONTH FROM participants.created_at) as month'), DB::raw('SUM(total_score) as total_score'), DB::raw('MAX(total_score) as high_score'), DB::raw('COUNT(*) as participant_count'))
                ->groupBy('school_id', 'month');
        }
    ])->get();

    foreach ($schools as $school) {
        foreach ($school->participants as $participant) {
            $month = $participant->month;
            $totalScore = $participant->total_score;
            $highScore = $participant->high_score;
            $participantCount = $participant->participant_count;

            // Ensure the month array exists
            if (!isset($data[$month])) {
                $data[$month] = ['total_score' => 0, 'high_score' => 0, 'schools' => [], 'participant_count' => 0];
            }

            // Ensure the school array exists for the specific month
            if (!isset($data[$month]['schools'][$school->name])) {
                $data[$month]['schools'][$school->name] = [
                    'total_score' => 0,
                    'high_score' => 0,
                    'participant_count' => 0
                ];
            }

            // Update the data
            $data[$month]['total_score'] += $totalScore;
            $data[$month]['high_score'] = max($data[$month]['high_score'], $highScore);
            $data[$month]['schools'][$school->name]['total_score'] += $totalScore;
            $data[$month]['schools'][$school->name]['high_score'] = max($data[$month]['schools'][$school->name]['high_score'], $highScore);
            $data[$month]['participant_count'] += $participantCount;
        }
    }

    return $data;
}


    private function getMonthlyRegistrationData()
    {
        $schools = School::select(DB::raw('EXTRACT(MONTH FROM created_at) as month'), DB::raw('COUNT(*) as count'))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $participants = Participant::select(DB::raw('EXTRACT(MONTH FROM created_at) as month'), DB::raw('COUNT(*) as count'))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return [
            'schoolRegistrations' => $schools->pluck('count', 'month')->all(),
            'participantRegistrations' => $participants->pluck('count', 'month')->all()
        ];
    }
}
