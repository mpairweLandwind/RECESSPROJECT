<?php

namespace App\Http\Controllers;

use App\Models\Participant;
use App\Models\School;
use App\Models\Challenge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReportMail;
use  PDF; // Import PDF library
use iio\libmergepdf\Merger; // Import Merger class

class ReportController extends Controller
{
    public function sendParticipantPdf($participant_id)
    {
        $participant = Participant::with('challenges', 'user', 'school')->findOrFail($participant_id);

        $pdfs = [];
        foreach ($participant->user as $user) {
            $pdfs[] = $this->generateParticipantReport($participant, $user);
        }

        $mergedPdf = $this->mergePdfs($pdfs);

        $data = [
            'subject' => 'Your Challenge Reports',
            'participant' => $participant,
            'challenges' => $participant->user,
        ];

        Mail::to($participant->user->email)->send(new ReportMail($data, $mergedPdf, 'participant_reports.pdf'));

        return redirect()->back()->with('success', 'Participant PDFs sent successfully');
    }

    public function generateParticipantPdf($participant_id)
    {
        $participant = Participant::with('challenges', 'user', 'school')->findOrFail($participant_id);

        $pdfs = [];
        foreach ($participant->user as $user) {
            $pdfs[] = $this->generateParticipantReport($participant, $user);
        }

        $mergedPdf = $this->mergePdfs($pdfs);

        return response()->streamDownload(function () use ($mergedPdf) {
            echo $mergedPdf;
        }, 'participant_reports.pdf');
    }

    public function sendSchoolPdf($school_id)
    {
        $school = School::with('participants.user')->findOrFail($school_id);

        $pdfs = [];
        foreach ($school->participants as $participant) {
            foreach ($participant->challenge as $challenge) {
                $pdfs[] = $this->generateSchoolReport($school, $challenge);
            }
        }

        $mergedPdf = $this->mergePdfs($pdfs);

        $data = [
            'subject' => 'School Challenge Reports',
            'school' => $school,
            'challenges' => $school->participants->flatMap->challenges,
        ];

        Mail::to($school->email)->send(new ReportMail($data, $mergedPdf, 'school_reports.pdf'));

        return redirect()->back()->with('success', 'School PDFs sent successfully');
    }

    public function generateSchoolPdf($school_id)
    {
        $school = School::with('participants.challenges')->findOrFail($school_id);

        $pdfs = [];
        foreach ($school->participants as $participant) {
            foreach ($participant->challenges as $challenge) {
                $pdfs[] = $this->generateSchoolReport($school, $challenge);
            }
        }

        $mergedPdf = $this->mergePdfs($pdfs);

        return response()->streamDownload(function () use ($mergedPdf) {
            echo $mergedPdf;
        }, 'school_reports.pdf');
    }

    private function mergePdfs($pdfs)
    {
        $merger = new Merger;

        foreach ($pdfs as $pdf) {
            $merger->addRaw($pdf);
        }

        return $merger->merge();
    }

    /**
     * Generate the participant report PDF.
     */
    private function generateParticipantReport($participant, $challenge)
    {
        $data = [
            'participant' => $participant,
            'challenge' => $challenge,
        ];

        $pdf = PDF::loadView('reports.participant', $data);
        return $pdf->output();
    }

    /**
     * Generate the school report PDF.
     */
    private function generateSchoolReport($school, $challenge)
    {
        $data = [
            'school' => $school,
            'challenge' => $challenge,
        ];

        $pdf = PDF::loadView('reports.school', $data);
        return $pdf->output();
    }
}
