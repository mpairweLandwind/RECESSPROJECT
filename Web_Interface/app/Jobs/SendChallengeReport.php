<?php

namespace App\Jobs;

use App\Models\Challenge;
use App\Models\School;
use App\Services\ReportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendChallengeReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $challenge;
    protected $reportService;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Challenge $challenge, ReportService $reportService)
    {
        $this->challenge = $challenge;
        $this->reportService = $reportService;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $participants = $this->challenge->participants;

        foreach ($participants as $participant) {
            $pdf = $this->reportService->generateParticipantReport($participant, $this->challenge);

            Mail::send('emails.participantReport', ['participant' => $participant], function ($message) use ($participant, $pdf) {
                $message->to($participant->user->email)
                    ->subject('Challenge Report')
                    ->attachData($pdf, 'participant_report.pdf');
            });
        }

        $schools = School::whereHas('participants', function ($query) {
            $query->where('challenge_id', $this->challenge->id);
        })->get();

        foreach ($schools as $school) {
            $pdf = $this->reportService->generateSchoolReport($school, $this->challenge);

            Mail::send('emails.schoolReport', ['school' => $school], function ($message) use ($school, $pdf) {
                $message->to($school->email)
                    ->subject('School Challenge Report')
                    ->attachData($pdf, 'school_report.pdf');
            });
        }
    }
}
