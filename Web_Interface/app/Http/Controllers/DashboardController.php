<?php

namespace App\Http\Controllers;

use App\Models\Participant;
use App\Models\School;
use App\Models\Challenge;

class DashboardController extends Controller
{
    public function index($section = 'welcome')
    {
        $participants = [];
        $schools = [];
        $challenges = [];

        if ($section === 'reports') {
            // Fetch participants, schools, and challenges data
            $participants = Participant::with(['school', 'user'])->get();
            $schools = School::all();
            $challenges = Challenge::all();
        }

        return view('dashboard', compact('section', 'participants', 'schools', 'challenges'));
    }
}
