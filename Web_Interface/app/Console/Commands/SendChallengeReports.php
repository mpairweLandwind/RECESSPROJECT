<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Challenge;
use App\Models\Participant;
use App\Models\School;
use App\Services\ReportService;
use Illuminate\Support\Facades\Mail;
use iio\libmergepdf\Merger;
use Carbon\Carbon;

class SendChallengeReports extends Command
{
    protected $signature = 'send:challengereports';
    protected $description = 'Send challenge reports to participants and schools';

    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        parent::__construct();
        $this->reportService = $reportService;
    }

    public function handle()
    {
        $challenges = Challenge::where('end_date', '<=', Carbon::now())->get();

        foreach ($challenges as $challenge) {
            $this->sendParticipantReports($challenge);
            $this->sendSchoolReports($challenge);
        }

        $this->info('Challenge reports sent successfully.');
    }

    protected function sendParticipantReports(Challenge $challenge)
    {
        $participants = $challenge->participants;

        foreach ($participants as $participant) {
            $pdfs = [];
            $pdfs[] = $this->reportService->generateParticipantReport($participant, $challenge);

            $mergedPdf = $this->mergePdfs($pdfs);

            Mail::send([], [], function ($message) use ($participant, $mergedPdf) {
                $message->to($participant->user->email)
                    ->subject('Your Challenge Reports')
                    ->attachData($mergedPdf, 'participant_reports.pdf');
            });
        }
    }

    protected function sendSchoolReports(Challenge $challenge)
    {
        $schools = School::with('participants.challenges')->get();

        foreach ($schools as $school) {
            $pdfs = [];
            foreach ($school->participants as $participant) {
                foreach ($participant->challenges as $participantChallenge) {
                    if ($participantChallenge->id === $challenge->id) {
                        $pdfs[] = $this->reportService->generateSchoolReport($school, $challenge);
                    }
                }
            }

            $mergedPdf = $this->mergePdfs($pdfs);

            Mail::send([], [], function ($message) use ($school, $mergedPdf) {
                $message->to($school->email)
                    ->subject('School Challenge Reports')
                    ->attachData($mergedPdf, 'school_reports.pdf');
            });
        }
    }

    protected function mergePdfs($pdfs)
    {
        $merger = new Merger;

        foreach ($pdfs as $pdf) {
            $merger->addRaw($pdf);
        }

        return $merger->merge();
    }
}
