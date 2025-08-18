<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Pembayaran Berhasil</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f7f7f7;
            margin: 0;
            padding: 0;
        }
        .container {
            background: #fff;
            max-width: 500px;
            margin: 40px auto;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.08);
            padding: 32px 24px;
        }
        h2 {
            color: #2dbe60;
            margin-bottom: 16px;
        }
        .details {
            background: #f0f8f4;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 24px;
        }
        .details strong {
            color: #333;
        }
        .footer {
            color: #888;
            font-size: 14px;
            margin-top: 32px;
        }
        .code {
            font-size: 18px;
            color: #2dbe60;
            letter-spacing: 2px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Pembayaran Anda Berhasil!</h2>
        <p>Halo <strong>{{ optional(optional($payment->transaction)->user)->name ?? 'Pelanggan' }}</strong>,</p>
        <p>Terima kasih telah melakukan pembayaran di <strong>XYZ Stay</strong>.</p>
        <div class="details">
            <p>
                <strong>Kode Transaksi:</strong> <span class="code">{{ $payment->transaction->code ?? '-' }}</span><br>
                {{-- <strong>ID Transaksi:</strong> {{ $payment->transaction_code ?? '-' }}<br> --}}
                <strong>Total Pembayaran:</strong> Rp{{ number_format($payment->total_price, 0, ',', '.') }}<br>
                <strong>Tanggal Pembayaran:</strong> {{ $payment->payment_date ?? now()->format('d-m-Y H:i') }}<br>
                <strong>Tanggal Booking:</strong>
                {{ optional($payment->transaction)->start_date ? \Carbon\Carbon::parse($payment->transaction->start_date)->format('d-m-Y') : '-' }}
                s/d
                {{ optional($payment->transaction)->end_date ? \Carbon\Carbon::parse($payment->transaction->end_date)->format('d-m-Y') : '-' }}<br>
                <strong>Total Hari:</strong>
                {{ optional($payment->transaction)->start_date && optional($payment->transaction)->end_date
                    ? \Carbon\Carbon::parse($payment->transaction->start_date)->diffInDays(\Carbon\Carbon::parse($payment->transaction->end_date)) + 1
                    : '-' }}
            </p>
        </div>
        <p>Jika ada pertanyaan, silakan hubungi kami kapan saja.</p>
        <div class="footer">
            Salam,<br>
            Tim XYZ Stay
        </div>
    </div>
</body>
</html>
