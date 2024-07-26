<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Challenge;

class ChallengeCompleted extends Mailable
{
    use Queueable, SerializesModels;

    public $challenge;

    public function __construct(Challenge $challenge)
    {
        $this->challenge = $challenge;
    }

    public function build()
    {
        return $this->subject('Challenge Completed')
                    ->view('emails.challengeCompleted')
                    ->with('challenge', $this->challenge);
    }
}
