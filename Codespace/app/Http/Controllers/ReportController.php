<?php

namespace App\Http\Controllers;

use PDF;
use App\Models\Participant;
use App\Models\School;
use App\Mail\SendPdfEmail;
use Illuminate\Support\Facades\Mail;

class ReportController extends Controller
{
    public function sendParticipantPdf($participant_id)
    {
        $participant = Participant::with('challenges', 'user', 'school')->findOrFail($participant_id);

        $data = [
            'subject' => 'Mathematics Challenge Participant Report',
            'challenges' => $participant->challenges,
            'user' => $participant->user,
            'participant' => $participant,
            'school' => $participant->school
        ];

        $pdf = PDF::loadView('reports.participant', $data)->setOptions(['defaultFont' => 'sans-serif']);
        $pdfContent = $pdf->output();

        Mail::to($participant->user->email)->send(
            new SendPdfEmail('reports.participant', $data, $pdfContent, $participant->user->name . '_' . $participant->id . '_challengeResults.pdf')
        );

        return redirect()->back()->with('success', 'Participant PDFs sent successfully');
    }

    public function sendSchoolPdf($school_id)
    {
        $school = School::with('participants.challenges')->findOrFail($school_id);

        $pdfs = [];
        foreach ($school->participants as $participant) {
            foreach ($participant->challenges as $challenge) {
                $pdf = PDF::loadView('reports.school', ['challenge' => $challenge,'school'=>$school])->setOptions(['defaultFont' => 'sans-serif']);
                $pdfs[] = $pdf->output();
            }
        }

        $data = [
            'subject' => 'School Challenge Results',
            'school' => $school
        ];

        Mail::to($school->email)->send(
            new SendPdfEmail('reports.school', $data, implode('', $pdfs), $school->name . '_challengeResults.pdf')
        );

        return redirect()->back()->with('success', 'School PDFs sent successfully');
    }
}
