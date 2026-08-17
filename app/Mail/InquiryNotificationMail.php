<?php

namespace App\Mail;

use App\Models\Inquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InquiryNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public Inquiry $inquiry;

    public function __construct(Inquiry $inquiry)
    {
        $this->inquiry = $inquiry;
    }

    public function build()
    {
        return $this
            ->subject('New Customer Inquiry: ' . $this->inquiry->name)
            ->html("
                <div style='font-family: Arial, sans-serif; color: #1B1315; max-width: 600px; margin: 0 auto; border: 1px solid #D4AF37; padding: 24px; background: #FFF;'>
                    <h2 style='color: #1B1315; border-bottom: 2px solid #C5A059; padding-bottom: 8px;'>New Customer Inquiry</h2>
                    <p style='font-size: 14px; margin: 8px 0;'><strong>Full Name:</strong> {$this->inquiry->name}</p>
                    <p style='font-size: 14px; margin: 8px 0;'><strong>Email Address:</strong> <a href='mailto:{$this->inquiry->email}'>{$this->inquiry->email}</a></p>
                    <p style='font-size: 14px; margin: 8px 0;'><strong>Phone Number:</strong> {$this->inquiry->phone}</p>
                    <p style='font-size: 14px; margin: 8px 0;'><strong>Submitted At:</strong> {$this->inquiry->created_at}</p>
                    <hr style='border: 0; border-top: 1px solid #eee; margin: 16px 0;' />
                    <h3 style='font-size: 15px; color: #C5A059;'>Message / Inquiry Details:</h3>
                    <div style='background: #F7F3F4; padding: 16px; font-size: 14px; line-height: 1.6; white-space: pre-wrap;'>{$this->inquiry->message}</div>
                </div>
            ");
    }
}
