<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PatientLinkVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $code
    ) {
    }

    public function build(): self
    {
        return $this
            ->subject('Код подтверждения привязки медицинской карты')
            ->view('emails.patient-link-verification')
            ->with([
                'code' => $this->code,
            ]);
    }
}
