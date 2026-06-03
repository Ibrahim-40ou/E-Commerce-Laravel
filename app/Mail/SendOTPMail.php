<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendOTPMail extends Mailable
{
    use Queueable, SerializesModels;

    // This public property is automatically visible to your HTML body string below
    public string $code;

    public function __construct(string $code)
    {
        $this->code = $code;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Shop Verification Code',
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: "
                <div style='font-family: sans-serif; padding: 20px; color: #333;'>
                    <h2>Welcome to E-Commerce!</h2>
                    <p>Your one-time verification code is:</p>
                    <div style='background: #f4f4f4; padding: 15px; font-size: 24px; font-weight: bold; letter-spacing: 2px; text-align: center; display: inline-block; border-radius: 5px; color: #000;'>
                        {$this->code}
                    </div>
                    <p style='margin-top: 20px; font-size: 12px; color: #777;'>This code will expire in 10 minutes.</p>
                </div>
            "
        );
    }
}