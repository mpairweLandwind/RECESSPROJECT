<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreChallengeRequest;
use App\Http\Requests\UpdateChallengeRequest;
use App\Models\AttemptedQuestion;
use App\Models\Challenge;
use App\Models\Question;
use Illuminate\Http\Request;
use App\Models\School;
use App\Models\Participant;
use Carbon\Carbon;
use DB;

class ChallengeController extends Controller
{
    /**
     * Display a listing of the resource.
     */


    public function setChallenge(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'duration' => 'required|integer|min:1',
            'number_of_questions' => 'required|integer|min:1',
            'status' => 'nullable|string',
        ]);

        // Fetch random questions based on the number provided
        $questions = Question::inRandomOrder()->limit($request->number_of_questions)->get();

        if ($questions->count() < $request->number_of_questions) {
            return back()->with('error', 'Not enough questions available to set challenges.');
        }

        // Create the challenge
        $challenge = Challenge::create([
            'title' => $request->title,
            'description' => $request->description,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'duration' => $request->duration,
            'number_of_questions' => $request->number_of_questions,
        ]);

        // Attach questions to the challenge
        // foreach ($questions as $question) {
        //     $challenge->questions()->attach($question->id);
        // }

        return back()->with('success', 'Challenge set successfully!');
    }


    public function analytics()
    {
        $mostCorrectlyAnsweredQuestions = AttemptedQuestion::withCount('marks_awarded')
            ->orderBy('marks_awarded', 'desc')
            ->limit(10)
            ->get();

        $schoolRankings = School::withSum('participants', 'score')
            ->orderBy('participants_sum_score', 'desc')
            ->limit(10)
            ->get();

        $performanceChartLabels = School::select(DB::raw('YEAR(created_at) as year'))
            ->distinct()
            ->pluck('year');

        $performanceChartData = School::select(DB::raw('YEAR(created_at) as year, SUM(score) as total_score'))
            ->groupBy('year')
            ->pluck('total_score', 'year');

        $questionRepetition = Participant::withCount('repeatedQuestions')
            ->orderBy('repeated_questions_count', 'desc')
            ->get();

        $worstPerformingSchools = School::withSum('participants', 'score')
            ->orderBy('participants_sum_score', 'asc')
            ->limit(10)
            ->get();

        $bestPerformingSchools = School::withSum('participants', 'score')
            ->orderBy('participants_sum_score', 'desc')
            ->limit(10)
            ->get();

        $incompleteParticipants = Participant::whereHas('challenges', function ($query) {
            $query->where('status', 'incomplete');
        })->get();

        return view('dashboard', [
            'mostCorrectlyAnsweredQuestions' => $mostCorrectlyAnsweredQuestions,
            'schoolRankings' => $schoolRankings,
            'performanceChartLabels' => $performanceChartLabels,
            'performanceChartData' => $performanceChartData,
            'questionRepetition' => $questionRepetition,
            'worstPerformingSchools' => $worstPerformingSchools,
            'bestPerformingSchools' => $bestPerformingSchools,
            'incompleteParticipants' => $incompleteParticipants
        ]);
    }


    private function calculatePercentageRepetition()
    {
        // Your formula to calculate percentage repetition
    }
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreChallengeRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Challenge $challenge)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Challenge $challenge)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateChallengeRequest $request, Challenge $challenge)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Challenge $challenge)
    {
        //
    }
}
