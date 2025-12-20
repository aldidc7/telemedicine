<?php

namespace App\Listeners;

use App\Events\PaymentConfirmed;
use App\Jobs\SendSMSNotification;

/**
 * Listener untuk event konfirmasi pembayaran
 * Mengirim SMS notification ke pasien
 */
class SendPaymentConfirmationSMS
{
    public function handle(PaymentConfirmed $event)
    {
        $payment = $event->payment;

        // Send SMS to patient
        if ($payment->user && $payment->user->phone_number) {
            SendSMSNotification::dispatch(
                $payment->user->id,
                $payment->user->phone_number,
                'payment_confirmed',
                [
                    'amount' => number_format($payment->amount, 0, ',', '.'),
                    'invoice_id' => $payment->invoice_id ?? $payment->id,
                ]
            );
        }
    }
}
