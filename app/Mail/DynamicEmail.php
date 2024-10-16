<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class DynamicEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $template;
    public $data;
    public $attachments;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($template, $data, $attachments = [])
    {
        $this->template = $template;
        $this->data = $data;
        $this->attachments = $attachments;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $email = $this->subject($this->parseTemplate($this->template->subject))
            ->html($this->parseTemplate($this->template->body));

        foreach ($this->attachments as $attachment) {
            $email->attach(Storage::path($attachment['path']), [
                'as' => $attachment['name'], // Optional: Custom filename for the attachment
                // 'mime' => $attachment['mime'], // Optional: Specify MIME type
            ]);
        }

        if (isset($this->data['cc']) && is_array($this->data['cc'])) {
            $email->cc($this->data['cc']);
        }

        if (isset($this->data['bcc']) && is_array($this->data['bcc'])) {
            $email->bcc($this->data['bcc']);
        }


        return $email;
    }

    /**
     * Replace placeholders in the template with actual data.
     *
     * @param  string  $templateBody
     * @return string
     */
    private function parseTemplate($templateBody)
    {
        foreach ($this->data as $key => $value) {
            $templateBody = str_replace("{{" . $key . "}}", $value, $templateBody);
        }

        return $templateBody;
    }
}
