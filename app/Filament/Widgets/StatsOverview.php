<?php

namespace App\Filament\Widgets;

use App\Models\BoardingHouse;
use App\Models\Transaction;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Number;

class StatsOverview extends BaseWidget
{
    use HasWidgetShield;


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

        // Ambil semua ID boarding house milik user
        $boardingHouseIds = BoardingHouse::where('user_id', $user->id)->pluck('id');

        // Transaksi bulan ini (yang sudah approved dan milik boarding house user)
        $newTransactionQuery = Transaction::where('transactions_status', 'approved')
            ->whereIn('boarding_house_id', $boardingHouseIds)
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year);

        // Transaksi bulan lalu (yang sudah approved dan milik boarding house user)
        $prevTransactionQuery = Transaction::where('transactions_status', 'approved')
            ->whereIn('boarding_house_id', $boardingHouseIds)
            ->whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->whereYear('created_at', Carbon::now()->subMonth()->year);

        $newTransactionCount = $newTransactionQuery->count();
        $prevTransactionCount = $prevTransactionQuery->count();

        $newRevenue = $newTransactionQuery->sum('total_price');
        $prevRevenue = $prevTransactionQuery->sum('total_price');

        $transactionPercentage = $this->getPercentage($prevTransactionCount, $newTransactionCount);
        $revenuePercentage = $this->getPercentage($prevRevenue, $newRevenue);

        return [
            Stat::make(
                'New Boarding House of the month',
                BoardingHouse::where('user_id', $user->id)
                    ->whereMonth('created_at', Carbon::now()->month)
                    ->whereYear('created_at', Carbon::now()->year)
                    ->count()
            ),
            Stat::make('Transactions of the month', $newTransactionCount)
                ->description($transactionPercentage > 0 ? "{$transactionPercentage}% Increased" : "{$transactionPercentage}% Decreased")
                ->descriptionIcon($transactionPercentage > 0 ? 'heroicon-o-arrow-trending-up' : 'heroicon-o-arrow-trending-down')
                ->color($transactionPercentage > 0 ? 'success' : 'danger'),
            Stat::make('Revenue of the month', Number::currency($newRevenue, 'IDR'))
                ->description($revenuePercentage > 0 ? "{$revenuePercentage}% Increased" : "{$revenuePercentage}% Decreased")
                ->descriptionIcon($revenuePercentage > 0 ? 'heroicon-o-arrow-trending-up' : 'heroicon-o-arrow-trending-down')
                ->color($revenuePercentage > 0 ? 'success' : 'danger'),
        ];
    }

}
