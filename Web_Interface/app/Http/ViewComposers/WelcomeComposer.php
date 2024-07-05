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

        $performanceChartLabels = School::select(DB::raw('EXTRACT(YEAR FROM created_at) as year'))
            ->distinct()
            ->pluck('year');

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
                $query->select('school_id', DB::raw('SUM(total_score) as total_score'))
                    ->groupBy('school_id')
                    ->orderBy('total_score', 'asc');
            }
        ])->take(10)->get();

        $bestPerformingSchools = School::with([
            'participants' => function ($query) {
                $query->select('school_id', DB::raw('SUM(total_score) as total_score'))
                    ->groupBy('school_id')
                    ->orderBy('total_score', 'desc');
            }
        ])->take(10)->get();

        $incompleteParticipants = Participant::whereHas('challenges', function ($query) {
            $query->where('challenge_participants.status', 'incomplete');
        })->get();

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
                'incompleteParticipants'
            )
        );
    }

    private function getPerformanceChartData()
    {
        $data = [];

        $schools = School::with([
            'participants' => function ($query) {
                $query->select('school_id', DB::raw('EXTRACT(YEAR FROM participants.created_at) as year'), DB::raw('SUM(total_score) as total_score'))
                    ->groupBy('school_id', 'year');
            }
        ])->get();

        foreach ($schools as $school) {
            foreach ($school->participants as $participant) {
                $year = $participant->year;
                $totalScore = $participant->total_score;

                if (!isset($data[$year])) {
                    $data[$year] = 0;
                }

                $data[$year] += $totalScore;
            }
        }

        return $data;
    }
}
