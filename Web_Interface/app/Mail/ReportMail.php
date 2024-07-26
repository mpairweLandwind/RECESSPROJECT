<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use PDF; // Import PDF library

class ReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public $viewData;
    public $pdfContent;
    public $pdfFileName;

    /**
     * Create a new message instance.
     *
     * @param array $viewData Data for the email view
     * @param string $pdfContent PDF content as a string
     * @param string $pdfFileName Desired filename for the PDF attachment
     */
    public function __construct(array $viewData, string $pdfContent, string $pdfFileName)
    {
        $this->viewData = $viewData;
        $this->pdfContent = $pdfContent;
        $this->pdfFileName = $pdfFileName;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->viewData['subject'],
        );
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // Determine the type of report and generate the corresponding PDF
        if (isset($this->viewData['participant']) && isset($this->viewData['challenge'])) {
            // Generate participant report
            $pdfContent = $this->generateParticipantReport($this->viewData['participant'], $this->viewData['challenge']);
            $pdfFileName = 'participant_report.pdf';
        } elseif (isset($this->viewData['school']) && isset($this->viewData['challenge'])) {
            // Generate school report
            $pdfContent = $this->generateSchoolReport($this->viewData['school'], $this->viewData['challenge']);
            $pdfFileName = 'school_report.pdf';
        } else {
            throw new \Exception('Invalid data for generating report');
        }

        return $this->subject($this->viewData['subject'])
            ->view('emails.blank') // Use a minimal or blank view
            ->attachData($pdfContent, $pdfFileName, [
                'mime' => 'application/pdf',
            ]);
    }

    /**
     * Generate the participant report PDF.
     */
    public static function generateParticipantReport($participant, $challenge)
    {
        return PDF::loadView('reports.participant', [
            'participant' => $participant,
            'challenge' => $challenge,
        ])->output();
    }

    /**
     * Generate the school report PDF.
     */
    public static function generateSchoolReport($school, $challenge)
    {
        return PDF::loadView('reports.school', [
            'school' => $school,
            'challenge' => $challenge,
        ])->output();
    }
}
