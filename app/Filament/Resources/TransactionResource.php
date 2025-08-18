<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\RelationManagers;
use App\Models\Payment;
use App\Models\Transaction;
use Auth;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Mail\TransactionStatusChangedMail;
use Illuminate\Support\Facades\Mail;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationGroup = 'Boarding Houses Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('boarding_house_id')
                    ->label('Boarding House')
                    ->relationship('boardingHouse', 'name')
                    ->required()
                    ->reactive(),

                Forms\Components\Select::make('room_id')
                    ->label('Room')
                    ->options(function (callable $get) {
                        $boardingHouseId = $get('boarding_house_id');

                        if (!$boardingHouseId)
                            return [];

                        return \App\Models\Room::where('boarding_house_id', $boardingHouseId)
                            ->pluck('name', 'id');
                    })
                    ->required()
                    ->searchable(),
                // ->dependsOn('boarding_house_id'),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone_number')
                    ->tel()
                    ->required()
                    ->maxLength(255),
                // Forms\Components\Select::make('payment_method')
                //     ->options([
                //         'down_payment' => 'Down Payment',
                //         'full_payment' => 'Full Payment',
                //     ])
                //     ->required(),
                Forms\Components\Select::make('transactions_status')
                    ->options([
                        'waiting' => 'Waiting',
                        'approved' => 'Approved',
                        'canceled' => 'Canceled',
                    ])
                    ->required(),
                Forms\Components\DatePicker::make('start_date')
                    ->required(),
                Forms\Components\DatePicker::make('end_date')
                    ->required(),
                // Forms\Components\TextInput::make('duration')
                //     ->required()
                //     ->numeric(),
                Forms\Components\TextInput::make('total_price')
                    ->prefix('IDR')
                    ->numeric(),
                // Forms\Components\DatePicker::make('transaction_date')
                //     ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
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
                // Tables\Columns\TextColumn::make('payment_method'),
                Tables\Columns\TextColumn::make('transactions_status')
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
                // Tables\Columns\TextColumn::make('transaction_date')
                //     ->date()
                //     ->sortable(),
            ])
            ->filters([
                SelectFilter::make('transactions_status') // <-- corrected key
                    ->options([
                        'waiting' => 'Waiting',
                        'approved' => 'Approved',
                        'canceled' => 'Canceled',
                    ])
            ])
            ->actions([
                Action::make('approve')
                    ->label('Approve')
                    ->color('success')
                    ->button()
                    ->requiresConfirmation()
                    ->action(function (Transaction $transaction) {
                        // Update status transaksi
                        $transaction->update(['transactions_status' => 'approved']);

                        // Membuat entri di tabel payments
                        Payment::create([
                            'transaction_id' => $transaction->id,
                            'payment_status' => 'pending',  // Status pembayaran awal
                            'total_price' => $transaction->total_price,  // Mengambil total harga dari transaksi
                        ]);
                        // Kirim email ke user
                        Mail::to($transaction->email)->send(new TransactionStatusChangedMail($transaction));
                        // Kirim notifikasi
                        Notification::make()
                            ->success()
                            ->title('Transaction Approved')
                            ->body('The transaction has been approved successfully.')
                            ->icon('heroicon-o-check-circle')
                            ->send();
                    })
                    ->hidden(fn(Transaction $transaction) => $transaction->transactions_status !== 'waiting'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();

        // Super Admin bisa melihat semua transaksi
        if ($user->hasRole('super_admin')) {
            return parent::getEloquentQuery();
        }

        // Admin melihat transaksi untuk boarding house yang dia buat
        return parent::getEloquentQuery()
            ->whereHas('boardingHouse', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            });
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }
}
