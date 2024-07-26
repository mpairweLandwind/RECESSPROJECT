<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Challenge;
use App\Models\Participant;
use Illuminate\Support\Facades\Mail;
use App\Mail\ChallengeCompleted;

class CheckChallenges extends Command
{
    protected $signature = 'challenges:check';
    protected $description = 'Check if challenges are completed and send email notifications';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
       
        $now = now();


        $challenges = Challenge::where('end_date', '<=', $now)
                                ->where('status', '!=', 'completed')
                                ->get();

        foreach ($challenges as $challenge) {
            
            $participants = Participant::where('challenge_id', $challenge->id)->get();
            $allCompleted = true;
            foreach ($participants as $participant) {
                if ($participant->status != 'completed') {
                    $allCompleted = false;
                    break;
                }
            }

            if ($allCompleted) {
                
                $challenge->status = 'completed';
                $challenge->save();

               
                Mail::to('eliodcurry@gmail.com')->send(new ChallengeCompleted($challenge));
            }
        }

        return 0;
    }
}
