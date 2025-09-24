<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Midtrans\Config as MidtransConfig;
use Midtrans\Transaction as MidtransTransaction;
use Illuminate\Http\Request;
use Midtrans\Notification;
use Midtrans\Config;
use Midtrans\Snap;
use Illuminate\Support\Facades\Mail;
use App\Mail\PaymentSuccessNotification;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Cek apakah payment ada
        $payment = Payment::find($id);
        if (!$payment) {
            return response()->json([
                'message' => 'Payment not found',
            ], 404);
        }

        // Update payment data sesuai dengan input request
        $payment->update($request->only([
            'payment_method',
            'payment_status',
            'payment_date',
            'expired_date',
            'total_price',
            'token', // 'token' bisa tetap disertakan, namun kita akan menggantinya nanti
        ]));

        // Konfigurasi Midtrans
        \Midtrans\Config::$is3ds = true;
        \Midtrans\Config::$serverKey = config('midtrans.server_key');
        \Midtrans\Config::$isProduction = config('midtrans.isProduction');
        \Midtrans\Config::$isSanitized = config('midtrans.isSanitized');
        \Midtrans\Config::$is3ds = config('midtrans.is_3ds');

        // Menyiapkan parameter untuk transaksi Midtrans
        // Buat order_id unik dengan menambahkan timestamp
        $orderId = $payment->transaction_id . '-' . now()->format('YmdHis');
        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $payment->total_price,
            ],
            'customer_details' => [
                'name' => auth()->user()->name,
                'email' => auth()->user()->email,
                'phone' => auth()->user()->phone,
            ]
        ];

        // Membuat transaksi di Midtrans dan mendapatkan URL transaksi dan token
        $midtransResponse = \Midtrans\Snap::createTransaction($params);
        $paymentUrl = $midtransResponse->redirect_url;  // URL untuk mengarahkan pengguna ke Midtrans

        // Ambil token dari respons Midtrans
        $midtransToken = $midtransResponse->token;

        // Perbarui field token dengan token dari Midtrans
        $payment->update(['token' => $midtransToken, 'expired_date' => now()->addDays(1)]);

        // Kembalikan respons dengan URL pembayaran dan informasi lainnya
        return response()->json([
            'message' => 'Payment updated successfully',
            'payment' => $payment,
            'payment_url' => $paymentUrl,  // URL untuk mengarahkan pengguna ke Midtrans
        ]);
    }

    public function webHookHandler(Request $request)
    {
        \Log::info('Midtrans webhook hit', ['payload' => $request->all()]);

        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.isProduction');
        Config::$isSanitized = config('midtrans.isSanitized');
        Config::$is3ds = config('midtrans.is_3ds');

        $notif = new Notification();

        $transactionStatus = $notif->transaction_status;
        $paymentType = $notif->payment_type;
        $orderId = $notif->order_id;

        $payment = Payment::with(['transaction.user'])
            ->where('transaction_id', $orderId)
            ->first();

        if (!$payment) {
            \Log::error('Webhook: payment not found', ['order_id' => $orderId]);
            return response()->json(['message' => 'Payment not found'], 404);
        }

        switch ($transactionStatus) {
            case 'capture':
                if ($paymentType === 'credit_card') {
                    $payment->update([
                        'payment_status' => 'success',
                        'payment_date' => now(),
                    ]);

                    $userEmail = optional(optional($payment->transaction)->user)->email;
                    if ($userEmail) {
                        try {
                            Mail::to($userEmail)->send(new PaymentSuccessNotification($payment));
                            \Log::info('Webhook capture: email sent', ['email' => $userEmail]);
                        } catch (\Exception $e) {
                            \Log::error('Webhook capture: email error', ['error' => $e->getMessage()]);
                        }
                    } else {
                        \Log::error('Webhook capture: user email not found', ['payment_id' => $payment->id]);
                    }
                }
                break;

            case 'settlement':
                $payment->update([
                    'payment_status' => 'success',
                    'payment_date' => now(),
                ]);

                $userEmail = optional(optional($payment->transaction)->user)->email;
                if ($userEmail) {
                    try {
                        Mail::to($userEmail)->send(new PaymentSuccessNotification($payment));
                        \Log::info('Webhook settlement: email sent', ['email' => $userEmail]);
                    } catch (\Exception $e) {
                        \Log::error('Webhook settlement: email error', ['error' => $e->getMessage()]);
                    }
                } else {
                    \Log::error('Webhook settlement: user email not found', ['payment_id' => $payment->id]);
                }
                break;

            case 'pending':
                $payment->update(['payment_status' => 'pending']);
                break;

            case 'deny':
                $payment->update(['payment_status' => 'failed']);
                break;

            case 'expire':
                $payment->update(['payment_status' => 'expired']);
                break;

            case 'cancel':
                $payment->update(['payment_status' => 'cancelled']);
                break;
        }

        return response()->json([
            'payment' => $payment,
            'message' => 'Webhook processed successfully',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
