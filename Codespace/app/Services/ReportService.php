<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;

class ReportService
{
    public function generateParticipantReport($participant, $challenge)
    {
        return PDF::loadView('reports.participant', [
            'participant' => $participant,
            'challenge' => $challenge,
        ])->output();
    }

    public function generateSchoolReport($school, $challenge)
    {
        return PDF::loadView('reports.school', [
            'school' => $school,
            'challenge' => $challenge,
        ])->output();
    }
}
