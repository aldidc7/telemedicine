<?php

namespace App\Events;

use App\Models\Payment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Payment Confirmed Event
 * Fired when payment is confirmed
 */
class PaymentConfirmed
{
    use Dispatchable, SerializesModels;

    public Payment $payment;

    /**
     * Create a new event instance.
     */
    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
    }
}
