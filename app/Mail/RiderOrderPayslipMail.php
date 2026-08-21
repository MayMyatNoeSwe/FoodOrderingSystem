<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RiderOrderPayslipMail extends Mailable
{
    use Queueable, SerializesModels;

    public Order $order;
    public ?User $rider;

    /**
     * Create a new message instance.
     */
    public function __construct(Order $order, ?User $rider = null)
    {
        $this->order = $order->loadMissing(['user', 'rider', 'orderItems.menuItem']);
        $this->rider = $rider ?? $order->rider;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "🛵 Delivery Dispatch & Payslip Slip - Order #{$this->order->order_number} | " . config('app.name', 'Food Ordering System'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.rider_payslip',
            with: [
                'order' => $this->order,
                'rider' => $this->rider,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
