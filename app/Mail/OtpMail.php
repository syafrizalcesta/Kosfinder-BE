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

    public function __construct(public string $otpCode) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Kode Verifikasi KosFinder+');
    }

    public function content(): Content
    {
        return new Content(
            htmlString: "
            <div style='font-family: sans-serif; max-width: 480px; margin: auto; padding: 40px; background: #f9fafb; border-radius: 16px;'>
                <h2 style='color: #1d4ed8; margin-bottom: 8px;'>KosFinder+</h2>
                <p style='color: #374151;'>Berikut kode OTP untuk verifikasi akun kamu:</p>
                <div style='font-size: 40px; font-weight: bold; letter-spacing: 12px; color: #1d4ed8; text-align: center; padding: 24px; background: #eff6ff; border-radius: 12px; margin: 24px 0;'>
                    {$this->otpCode}
                </div>
                <p style='color: #6b7280; font-size: 14px;'>Kode berlaku selama <strong>5 menit</strong>. Jangan bagikan kode ini ke siapapun.</p>
            </div>
            ",
        );
    }
}