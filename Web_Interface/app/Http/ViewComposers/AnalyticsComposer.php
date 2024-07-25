<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;
use App\Models\School;
use App\Models\Participant;
use Illuminate\Support\Facades\DB;
use App\Models\AttemptedQuestion;

class AnalyticsComposer
{
    public function compose(View $view)
    {
        $mostCorrectlyAnsweredQuestions = AttemptedQuestion::select('question_id', DB::raw('count(*) as count'))
        ->where('marks_awarded', '>', 0)
        ->groupBy('question_id')
        ->orderByDesc('count')
        ->take(5)
            ->with('question') // Assuming the relationship method is 'question'
            ->get();

        $schoolRankings = School::select('schools.id', 'schools.name', DB::raw('SUM(participants.total_score) as total_score'))
        ->join('participants', 'schools.id', '=', 'participants.school_id')
        ->groupBy('schools.id', 'schools.name')
        ->orderBy('total_score', 'desc')
        ->take(5)
            ->get();

        $performanceChartLabels = School::select(DB::raw('EXTRACT(YEAR FROM created_at) as year'))
        ->distinct()
            ->pluck('year');

        $performanceChartData = $this->getPerformanceChartData();

        $questionRepetition = AttemptedQuestion::select('question_id', DB::raw('COUNT(*) as total_attempts'), DB::raw('SUM(case when is_repeated = true then 1 else 0 end) as repeated_count'), DB::raw('SUM(case when is_repeated = true then 1 else 0 end) / COUNT(*) * 100 as repetition_percentage'))
        ->groupBy('question_id')
        ->orderBy('repetition_percentage', 'desc')
        ->with('question')
        ->limit(5)
        ->get();

        $worstPerformingSchools = School::select('schools.id', 'schools.name', DB::raw('AVG(participants.total_score) as average_score'))
        ->join('participants', 'schools.id', '=', 'participants.school_id')
        ->groupBy('schools.id', 'schools.name')
        ->orderBy('average_score', 'asc')
        ->take(2)
        ->get();


        $bestPerformingSchools = School::select('schools.id', 'schools.name', DB::raw('AVG(participants.total_score) as average_score'))
        ->join('participants', 'schools.id', '=', 'participants.school_id')
        ->groupBy('schools.id', 'schools.name')
        ->orderBy('average_score', 'desc')
        ->take(5)
        ->get();

        $incompleteParticipants = Participant::whereHas('challenges', function ($query) {
            $query->where('challenge_participants.status', 'incomplete');
        })->get();

        $view->with(
            compact(
                'mostCorrectlyAnsweredQuestions',
                'schoolRankings',
                'performanceChartData',
                'questionRepetition',
                'worstPerformingSchools',
                'bestPerformingSchools',
                'incompleteParticipants'

            )
        );
    }
 
    

    private function getPerformanceChartData()
    {
        // Initialize data for years 2024 to 2034
        $years = range(2024, 2034);
        $data = [];
        foreach ($years as $year) {
            $data[$year] = [
                'total_score' => 0,
                'high_score' => 0,
                'schools' => [],
                'participant_count' => 0
            ];
        }

        // Retrieve schools with participants and their scores
        $schools = School::with([
            'participants' => function ($query) {
                $query->select('school_id', DB::raw('EXTRACT(YEAR FROM participants.created_at) as year'), DB::raw('SUM(total_score) as total_score'), DB::raw('MAX(total_score) as high_score'), DB::raw('COUNT(*) as participant_count'))
                    ->groupBy('school_id', 'year');
            }
        ])->get();

        foreach ($schools as $school) {
            foreach ($school->participants as $participant) {
                $year = $participant->year;
                $totalScore = $participant->total_score;
                $highScore = $participant->high_score;
                $participantCount = $participant->participant_count;

                // Ensure the year array exists
                if (!isset($data[$year])) {
                    $data[$year] = ['total_score' => 0, 'high_score' => 0, 'schools' => [], 'participant_count' => 0];
                }

                // Ensure the school array exists for the specific year
                if (!isset($data[$year]['schools'][$school->name])) {
                    $data[$year]['schools'][$school->name] = [
                        'total_score' => 0,
                        'high_score' => 0,
                        'participant_count' => 0
                    ];
                }

                // Update the data
                $data[$year]['total_score'] += $totalScore;
                $data[$year]['high_score'] = max($data[$year]['high_score'], $highScore);
                $data[$year]['schools'][$school->name]['total_score'] += $totalScore;
                $data[$year]['schools'][$school->name]['high_score'] = max($data[$year]['schools'][$school->name]['high_score'], $highScore);
                $data[$year]['participant_count'] += $participantCount;
            }
        }

        return $data;
    }

}