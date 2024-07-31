<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Challenge;
use App\Models\School;
use App\Mail\SendPdfEmail;
use Illuminate\Support\Facades\Mail;
use PDF;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SendPdfReports extends Command
{
    protected $signature = 'reports:sendpdf';
    protected $description = 'Generate and send PDF reports for challenges ending today';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $today = Carbon::today();
        Log::info('SendPdfReports command is running.');

        $challenges = Challenge::whereDate('end_date', $today)->with('participants.school')->get();

        foreach ($challenges as $challenge) {
            foreach ($challenge->participants as $participant) {
                // Generate PDF for the participant
                $participantData = [
                    'subject' => 'Mathematics Challenge Participant Report',
                    'challenges' => [$challenge],
                    'user' => $participant->user,
                    'school' => $participant->school
                ];

                $pdfParticipant = PDF::loadView('reports.participant', $participantData)->output();
                $participantFileName = $participant->user->name . '_' . $participant->id . '_challengeResults.pdf';
                Mail::to($participant->user->email)->send(new SendPdfEmail('reports.participant', $participantData, $pdfParticipant, $participantFileName));

                // Generate PDF for the school
                $schoolData = [
                    'subject' => 'School Challenge Results',
                    'school' => $participant->school,
                    'challenges' => [$challenge]
                ];

                $pdfSchool = PDF::loadView('reports.school', $schoolData)->output();
                $schoolFileName = $participant->school->name . '_challengeResults.pdf';
                Mail::to($participant->school->email)->send(new SendPdfEmail('reports.school', $schoolData, $pdfSchool, $schoolFileName));
            }
        }

        $this->info('PDF reports sent successfully!');
    }
}
