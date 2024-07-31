<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;
use App\Models\Participant;
use App\Models\School;
use App\Models\Challenge;

class ReportsComposer
{
    public function compose(View $view)
    {
        $participants = Participant::with(['school', 'user'])->get();
        $schools = School::all();
        $challenges = Challenge::all();

        $view->with(compact('participants', 'schools', 'challenges'));
    }
}
