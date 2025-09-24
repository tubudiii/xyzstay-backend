<?php

namespace App\Filament\Widgets;

use App\Models\BoardingHouse;
use App\Models\Transaction;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Carbon\Carbon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Number;

class StatsOverview extends BaseWidget
{
    use HasWidgetShield;

    use InteractsWithPageFilters;
    private function getPercentage(int|float $form, int|float $to): float
    {
        $denominator = $to + ($form / 2);

        if ($denominator == 0) {
            return 0;
        }

        return round(($to - $form) / $denominator * 100, 2);
    }

    protected function getStats(): array
    {

        $user = auth()->user();

        $startDate = !is_null($this->filters['startDate'] ?? null) ? Carbon::parse($this->filters['startDate'])->startOfDay() : null;
        $endDate = !is_null($this->filters['endDate'] ?? null) ? Carbon::parse($this->filters['endDate'])->endOfDay() : now();

        // Ambil semua ID boarding house milik user
        $boardingHouseIds = BoardingHouse::where('user_id', $user->id)->pluck('id');

        // Query Boarding House baru sesuai filter
        // Hitung jumlah boarding house baru sesuai filter
        $boardingHouseCountQuery = BoardingHouse::where('user_id', $user->id);
        if ($startDate && $endDate) {
            $boardingHouseCountQuery = $boardingHouseCountQuery->whereBetween('created_at', [$startDate, $endDate]);
        } else {
            $boardingHouseCountQuery = $boardingHouseCountQuery->whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year);
        }
        $boardingHouseCount = $boardingHouseCountQuery->count();

        // Query transaksi baru sesuai filter
        $newTransactionQuery = Transaction::where('transactions_status', 'approved')
            ->whereIn('boarding_house_id', $boardingHouseIds);
        if ($startDate && $endDate) {
            $newTransactionQuery->whereBetween('created_at', [$startDate, $endDate]);
        } else {
            $newTransactionQuery->whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year);
        }

        // Query transaksi sebelumnya (periode sebelum filter atau bulan lalu)
        if ($startDate && $endDate) {
            $prevStartDate = Carbon::parse($startDate)->subDays($endDate->diffInDays($startDate) + 1)->startOfDay();
            $prevEndDate = Carbon::parse($startDate)->subDay()->endOfDay();
            $prevTransactionQuery = Transaction::where('transactions_status', 'approved')
                ->whereIn('boarding_house_id', $boardingHouseIds)
                ->whereBetween('created_at', [$prevStartDate, $prevEndDate]);
        } else {
            $prevTransactionQuery = Transaction::where('transactions_status', 'approved')
                ->whereIn('boarding_house_id', $boardingHouseIds)
                ->whereMonth('created_at', Carbon::now()->subMonth()->month)
                ->whereYear('created_at', Carbon::now()->subMonth()->year);
        }

        $newTransactionCount = $newTransactionQuery->count();
        $prevTransactionCount = $prevTransactionQuery->count();

        $newRevenue = $newTransactionQuery->sum('total_price');
        $prevRevenue = $prevTransactionQuery->sum('total_price');

        $transactionPercentage = $this->getPercentage($prevTransactionCount, $newTransactionCount);
        $revenuePercentage = $this->getPercentage($prevRevenue, $newRevenue);

        return [
            Stat::make(
                'New Boarding House',
                $boardingHouseCount
            ),
            Stat::make('Transactions', $newTransactionCount)
                ->description($transactionPercentage > 0 ? "{$transactionPercentage}% Increased" : "{$transactionPercentage}% Decreased")
                ->descriptionIcon($transactionPercentage > 0 ? 'heroicon-o-arrow-trending-up' : 'heroicon-o-arrow-trending-down')
                ->color($transactionPercentage > 0 ? 'success' : 'danger'),
            Stat::make('Revenue', Number::currency($newRevenue, 'IDR'))
                ->description($revenuePercentage > 0 ? "{$revenuePercentage}% Increased" : "{$revenuePercentage}% Decreased")
                ->descriptionIcon($revenuePercentage > 0 ? 'heroicon-o-arrow-trending-up' : 'heroicon-o-arrow-trending-down')
                ->color($revenuePercentage > 0 ? 'success' : 'danger'),
        ];
    }

}
