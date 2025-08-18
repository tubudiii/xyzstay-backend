<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Status Transaksi Berubah</title>
    <style>
        body {
            background: #f6f8fa;
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 480px;
            margin: 40px auto;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            padding: 32px 28px;
        }
        .header {
            text-align: center;
            margin-bottom: 24px;
        }
        .header img {
            width: 56px;
            margin-bottom: 8px;
        }
        .title {
            font-size: 1.3rem;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 12px;
        }
        .status-badge {
            display: inline-block;
            padding: 6px 18px;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 500;
            color: #fff;
            background:
                @if($transaction->transactions_status === 'approved') #38a169
                @elseif($transaction->transactions_status === 'waiting') #718096
                @else #e53e3e
                @endif;
            margin-bottom: 18px;
        }
        .content {
            font-size: 1rem;
            color: #4a5568;
            margin-bottom: 18px;
        }
        .code-box {
            background: #edf2f7;
            border-radius: 8px;
            padding: 10px 16px;
            font-size: 1.1rem;
            font-weight: 500;
            color: #2b6cb0;
            margin-bottom: 18px;
            text-align: center;
            letter-spacing: 1px;
        }
        .footer {
            text-align: center;
            font-size: 0.95rem;
            color: #a0aec0;
            margin-top: 24px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" alt="User Icon">
            <div class="title">Status Transaksi Anda Berubah</div>
        </div>
        <div class="content">
            Halo <strong>{{ $transaction->name }}</strong>,
        </div>
        <div class="content">
            Status transaksi Anda telah berubah menjadi:
        </div>
        <div class="status-badge">
            {{ ucfirst($transaction->transactions_status) }}
        </div>
        <div class="content">
            Kode transaksi Anda:
        </div>
        <div class="code-box">
            {{ $transaction->code }}
        </div>
        <div class="content">
            Terima kasih telah menggunakan layanan kami.<br>
            Silahkan melakukan pembayaran lebih lanjut. <br>
            Jika ada pertanyaan, silakan hubungi admin kami.
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} XYZStay Property. All rights reserved.
        </div>
    </div>
