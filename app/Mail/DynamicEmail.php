<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DynamicEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $template;
    public $data;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($template, $data)
    {
        $this->template = $template;
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->template->subject)
            ->html($this->parseTemplate($this->template->body));
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
