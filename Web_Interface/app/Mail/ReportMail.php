<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $content;
    public string $pdfContent;
    public string $pdfFileName;

    /**
     * Create a new message instance.
     *
     * @param array $content Email content and data
     * @param string $pdfContent PDF content as a string
     * @param string $pdfFileName Desired filename for the PDF attachment
     */
    public function __construct(array $content, string $pdfContent, string $pdfFileName)
    {
        $this->content = $content;
        $this->pdfContent = $pdfContent;
        $this->pdfFileName = $pdfFileName;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->content['subject'])
                    ->view('emails.blank')
                    ->attachData($this->pdfContent, $this->pdfFileName, [
                        'mime' => 'application/pdf',
                    ]);
    }
}
