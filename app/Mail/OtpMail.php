<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public $otp;

    // Menangkap kode OTP saat email dipanggil
    public function __construct($otp)
    {
        $this->otp = $otp;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Kode Verifikasi KosFinder+', // Judul Email
        );
    }

    public function content(): Content
    {
        // Akan memanggil file view/desain email di folder resources/views/emails/otp.blade.php
        return new Content(
            view: 'emails.otp',
        );
    }
}