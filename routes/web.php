<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;

Route::middleware(['web', 'auth'])->get('/dashboard/export-csv', function (Request $request) {
    $user = Auth::user();

    // sanitize & validate (simple)
    $startDate = $request->query('startDate');
    $endDate = $request->query('endDate');
    if ($startDate && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate))
        $startDate = null;
    if ($endDate && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate))
        $endDate = null;

    $query = Transaction::ownedByAuth()->with(['user', 'boardingHouse', 'room']);
    if ($startDate) {
        $query->whereDate('created_at', '>=', $startDate);
    }
    if ($endDate) {
        $query->whereDate('created_at', '<=', $endDate);
    }
    $transactions = $query->orderBy('created_at')->get();

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

    $filename = 'Transaksi';
    if ($startDate && $endDate) {
        $filename .= ' ' . $startDate . ' sampai ' . $endDate;
    } elseif ($startDate) {
        $filename .= ' sejak ' . $startDate;
    } elseif ($endDate) {
        $filename .= ' hingga ' . $endDate;
    } else {
        $filename .= ' semua';
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


