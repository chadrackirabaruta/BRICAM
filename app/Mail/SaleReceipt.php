<?php
namespace App\Mail;

use App\Models\Sales;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SaleReceipt extends Mailable
{
    use Queueable, SerializesModels;

    public $sale;
    public $customMessage;

    public function __construct(Sales $sale, $subject, $message = null)
    {
        $this->sale = $sale;
        $this->subject = $subject;
        $this->customMessage = $message;
    }

    public function envelope()
    {
        return new Envelope(
            subject: $this->subject,
        );
    }

    public function content()
    {
        return new Content(
            markdown: 'emails.sales.receipt',
        );
    }

    public function attachments()
    {
        return [];
    }
}