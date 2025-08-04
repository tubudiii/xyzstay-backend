<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use App\Models\BoardingHouse;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Notifications\Notification;

class WaitingTransactions extends BaseWidget
{
    use HasWidgetShield;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $user = auth()->user();

        // Ambil semua ID boarding house milik user
        $boardingHouseIds = BoardingHouse::where('user_id', $user->id)->pluck('id');

        return $table
            ->query(
                Transaction::query()
                    ->where('payment_status', 'waiting')
                    ->whereIn('boarding_house_id', $boardingHouseIds) // Filter tambahan
            )
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->weight(FontWeight::Bold)
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('boardingHouse.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('room.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('payment_method'),
                Tables\Columns\TextColumn::make('payment_status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'waiting' => 'gray',
                        'approved' => 'info',
                        'canceled' => 'danger',
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_price')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('transaction_date')
                    ->date()
                    ->sortable(),
            ])
            ->actions([
                Action::make('approve')
                    ->label('Approve')
                    ->color('success')
                    ->button()
                    ->requiresConfirmation()
                    ->action(function (Transaction $transaction) {
                        $transaction->update(['payment_status' => 'approved']);

                        Notification::make()
                            ->success()
                            ->title('Transaction Approved')
                            ->body('The transaction has been approved successfully.')
                            ->icon('heroicon-o-check-circle')
                            ->send();
                    })
                    ->hidden(fn(Transaction $transaction) => $transaction->payment_status !== 'waiting'),
            ]);
    }
}
