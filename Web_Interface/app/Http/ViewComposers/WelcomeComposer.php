<?php

namespace App\Http\ViewComposers;

use App\Models\School;
use App\Models\Participant;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class WelcomeComposer
{
    public function compose(View $view)
    {
        $schoolCount = School::count();
        $activeParticipantsCount = Participant::whereHas('challenges', function ($query) {
            $query->where('challenge_participants.status', 'active');
        })->count();

        $rejectedParticipantsCount = Participant::whereHas('challenges', function ($query) {
            $query->where('challenge_participants.status', 'rejected');
        })->count();

        $mostCorrectlyAnsweredQuestions = Participant::with('attemptedQuestions')
            ->get()
            ->sortByDesc(function ($participant) {
                return $participant->attemptedQuestions->sum('marks_awarded');
            })
            ->take(10);

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

        $questionRepetition = Participant::withCount([
            'attemptedQuestions as repeated_question_count' => function ($query) {
                $query->where('is_repeated', true);
            }
        ])
            ->orderBy('repeated_question_count', 'desc')
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
        ])->take(10)->get();

        $incompleteParticipants = Participant::whereHas('challenges', function ($query) {
            $query->where('challenge_participants.status', 'incomplete');
        })->get();

        $monthlyRegistrationData = $this->getMonthlyRegistrationData();

        // Fetching the best two participants
        $topParticipants = Participant::with('school')
            ->orderBy('total_score', 'desc')
            ->take(2)
            ->get();

        $view->with(
            compact(
                'schoolCount',
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
                'monthlyRegistrationData',
                'topParticipants' // Pass the top participants to the view
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

                if (!isset($data[$month]['schools'][$school->name])) {
                    $data[$month]['schools'][$school->name] = [
                        'total_score' => 0,
                        'high_score' => 0,
                        'participant_count' => 0
                    ];
                }

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
