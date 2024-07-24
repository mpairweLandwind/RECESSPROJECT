<?php

namespace App\Http\Controllers;

use App\Models\Participant;
use App\Models\School;
use App\Models\Challenge;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use iio\libmergepdf\Merger; // Import Merger class

class ReportController extends Controller
{
    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function sendParticipantPdf($participant_id)
    {
        $participant = Participant::with('challenges')->findOrFail($participant_id);

        $pdfs = [];
        foreach ($participant->challenges as $challenge) {
            $pdfs[] = $this->reportService->generateParticipantReport($participant, $challenge);
        }

        $mergedPdf = $this->mergePdfs($pdfs);

        Mail::send([], [], function ($message) use ($participant, $mergedPdf) {
            $message->to($participant->user->email)
                ->subject('Your Challenge Reports')
                ->attachData($mergedPdf, 'participant_reports.pdf');
        });

        return redirect()->back()->with('success', 'Participant PDFs sent successfully');
    }

    public function generateParticipantPdf($participant_id)
    {
        $participant = Participant::with('challenges')->findOrFail($participant_id);

        $pdfs = [];
        foreach ($participant->challenges as $challenge) {
            $pdfs[] = $this->reportService->generateParticipantReport($participant, $challenge);
        }

        $mergedPdf = $this->mergePdfs($pdfs);

        return response()->streamDownload(function () use ($mergedPdf) {
            echo $mergedPdf;
        }, 'participant_reports.pdf');
    }

    public function sendSchoolPdf($school_id)
    {
        $school = School::with('participants.challenges')->findOrFail($school_id);

        $pdfs = [];
        foreach ($school->participants as $participant) {
            foreach ($participant->challenges as $challenge) {
                $pdfs[] = $this->reportService->generateSchoolReport($school, $challenge);
            }
        }

        $mergedPdf = $this->mergePdfs($pdfs);

        Mail::send([], [], function ($message) use ($school, $mergedPdf) {
            $message->to($school->email)
                ->subject('School Challenge Reports')
                ->attachData($mergedPdf, 'school_reports.pdf');
        });

        return redirect()->back()->with('success', 'School PDFs sent successfully');
    }

    public function generateSchoolPdf($school_id)
    {
        $school = School::with('participants.challenges')->findOrFail($school_id);

        $pdfs = [];
        foreach ($school->participants as $participant) {
            foreach ($participant->challenges as $challenge) {
                $pdfs[] = $this->reportService->generateSchoolReport($school, $challenge);
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
}
