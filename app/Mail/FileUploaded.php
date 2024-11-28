<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FileUploaded extends Mailable
{
    use Queueable, SerializesModels;

    public $uploadedBy;
    public $fileName;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($uploadedBy, $fileName)
    {
        $this->uploadedBy = $uploadedBy;
        $this->fileName = $fileName;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.file_uploaded'); // Create this view
    }
}
