<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use App\Models\BoardingHouse;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class MonthlyTransactionsChart extends ChartWidget
{
    use HasWidgetShield;

    protected static ?int $sort = 1;
    protected static ?string $heading = 'Chart';

    protected function getData(): array
    {
        $user = auth()->user();

        // Ambil semua ID boarding house milik user (admin pemilik kos/villa)
        $boardingHouseIds = BoardingHouse::where('user_id', $user->id)->pluck('id');

        // Filter data transaksi berdasarkan boarding_house_id
        $filteredQuery = Transaction::whereIn('boarding_house_id', $boardingHouseIds);

        $data = Trend::query($filteredQuery)
            ->between(now()->startOfMonth(), now()->endOfMonth())
            ->perDay()
            ->count();
        return [
            'datasets' => [
                [
                    'label' => 'Monthly Transactions',
                    'data' => $data->map(fn(TrendValue $value) => $value->aggregate),
                ],
            ],
            'labels' => $data->map(fn(TrendValue $value) => $value->date),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    public function getDescription(): ?string
    {
        return 'This chart displays the monthly transactions for your boarding houses.';
    }
}
