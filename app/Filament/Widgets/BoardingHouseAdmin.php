<?php

namespace App\Filament\Widgets;

use App\Models\BoardingHouse;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class BoardingHouseAdmin extends BaseWidget
{
    use HasWidgetShield;
    protected static ?string $heading = 'Boarding Houses Management';
    protected int|string|array $columnSpan = 'full';
    public function table(Table $table): Table
    {
        $user = Auth::user();

        return $table
            ->query(BoardingHouse::query()->where('user_id', $user->id)->orderByDesc('created_at', 'DESC'))
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Boarding House Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('address')
                    ->label('Address')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable(),
            ]);
    }
}
