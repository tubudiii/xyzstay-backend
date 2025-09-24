<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\Transaction;

Route::get('/dashboard/export-csv', function (Request $request) {
    $startDate = $request->query('startDate');
    $endDate = $request->query('endDate');

    $query = Transaction::query();
    if ($startDate) {
        $query->whereDate('created_at', '>=', $startDate);
    }
    if ($endDate) {
        $query->whereDate('created_at', '<=', $endDate);
    }
    $transactions = $query->get();

    $csvData = [];
    $csvData[] = ['ID', 'User', 'Boarding House', 'Room', 'Amount', 'Status', 'Created At'];
    $totalAmount = 0;
    foreach ($transactions as $trx) {
        $csvData[] = [
            $trx->id,
            $trx->user->name ?? '',
            $trx->boardingHouse->name ?? '',
            $trx->room->name ?? '',
            number_format($trx->total_price, 0, ',', '.'),
            $trx->transactions_status,
            $trx->created_at,
        ];
        $totalAmount += $trx->total_price;
    }

    // Tambahkan baris kosong sebagai pemisah
    $csvData[] = ['', '', '', '', '', '', ''];
    // Tambahkan baris total di akhir CSV
    $csvData[] = ['', '', '', '', 'Total', number_format($totalAmount, 0, ',', '.'), ''];

    $filename = 'Transaksi pada periode ';
    if ($startDate && $endDate) {
        $filename .= $startDate . ' sampai ' . $endDate;
    } elseif ($startDate) {
        $filename .= $startDate . ' sampai -';
    } elseif ($endDate) {
        $filename .= '- sampai ' . $endDate;
    } else {
        $filename .= 'semua';
    }
    $filename .= '.csv';
    $handle = fopen('php://temp', 'r+');
    foreach ($csvData as $row) {
        fputcsv($handle, $row);
    }
    rewind($handle);
    $csv = stream_get_contents($handle);
    fclose($handle);

    return response($csv)
        ->header('Content-Type', 'text/csv')
        ->header('Content-Disposition', 'attachment; filename=' . $filename);
});
// ...existing code...


