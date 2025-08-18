<?php

namespace App\Mail;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentSuccessNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $payment;
    public $recipientName;

    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
        $this->recipientName = optional(optional($payment->transaction)->user)->name ?? 'Pelanggan';
    }

    public function build()
    {
        return $this->subject('Pembayaran Berhasil')
            ->view('emails.payment_success')
            ->with([
                'recipientName' => $this->recipientName,
            ]);
    }
}

