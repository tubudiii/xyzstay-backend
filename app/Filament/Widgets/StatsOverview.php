<?php

namespace App\Filament\Widgets;

use App\Models\BoardingHouse;
use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Number;

class StatsOverview extends BaseWidget
{
    private function getPercentage(int $form, int $to)
    {
        return $to - $form / ($to + $form / 2) * 100;
    }
    protected function getStats(): array
    {
        $newBoardingHouseCount = BoardingHouse::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)->count();
        $newTransactionCount = Transaction::wherePaymentStatus('approved')
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year);
        $prevTransactionCount = Transaction::wherePaymentStatus('approved')
            ->whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->whereYear('created_at', Carbon::now()->subMonth()->year);
        $transactionPercentage = $this->getPercentage($prevTransactionCount->count(), $newTransactionCount->count());
        $revenuePercentage = $this->getPercentage($prevTransactionCount->sum('total_price'), $newTransactionCount->count());

        return [
            Stat::make('New Boarding House of the month', $newBoardingHouseCount),
            Stat::make('Transactions of the month', $newTransactionCount->count())
                ->description($transactionPercentage > 0 ? "{$transactionPercentage}% Increased" : "{$transactionPercentage}% Decreased")
                ->descriptionIcon($transactionPercentage > 0 ? 'heroicon-o-arrow-trending-up' : 'heroicon-o-arrow-tranding-down')
                ->color($transactionPercentage > 0 ? 'success' : 'danger'),
            Stat::make('Revenue of the month', Number::currency($newTransactionCount->sum('total_price'), 'IDR'))
                ->description($revenuePercentage > 0 ? "{$revenuePercentage}% Increased" : "{$revenuePercentage}% Decreased")
                ->descriptionIcon($revenuePercentage > 0 ? 'heroicon-o-arrow-trending-up' : 'heroicon-o-arrow-tranding-down')
                ->color($revenuePercentage > 0 ? 'success' : 'danger'),
        ];
    }
}
