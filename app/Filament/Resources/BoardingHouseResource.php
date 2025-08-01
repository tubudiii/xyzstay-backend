<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BoardingHouseResource\Pages;
use App\Filament\Resources\BoardingHouseResource\RelationManagers;
use App\Models\BoardingHouse;
use Filament\Forms;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Str;

class BoardingHouseResource extends Resource
{
    protected static ?string $model = BoardingHouse::class;

    protected static ?string $navigationIcon = 'heroicon-o-home-modern';

    protected static ?string $navigationGroup = 'Boarding Houses Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Tabs')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Informasi Umum')
                            ->schema([
                                Forms\Components\FileUpload::make('thumbnail')
                                    ->required()
                                    ->directory('boarding-houses')
                                    ->image(),
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(fn(Set $set, ?string $state) => $set('slug', Str::slug($state)))
                                    ->live(debounce: 200)
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('slug')
                                    ->required()
                                    ->disabled(),
                                Forms\Components\Select::make('city_id')
                                    ->relationship('city', 'name')
                                    ->required(),
                                Forms\Components\Select::make('category_id')
                                    ->relationship('category', 'name')
                                    ->required(),
                                Forms\Components\RichEditor::make('description')
                                    ->required()
                                    ->columnSpanFull(),
                                Forms\Components\Textarea::make('address')
                                    ->required()
                                    ->columnSpanFull(),
                            ]),
                        Forms\Components\Tabs\Tab::make('Kamar')
                            ->schema([
                                Forms\Components\Repeater::make('rooms')
                                    ->relationship('rooms')
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('room_type')
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('square_feet')
                                            ->required()
                                            ->numeric()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('capacity')
                                            ->required()
                                            ->numeric()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('price_per_day')
                                            ->required()
                                            ->prefix('IDR')
                                            ->numeric(),
                                        Forms\Components\Repeater::make('images')
                                            ->relationship('images')
                                            ->schema([
                                                Forms\Components\FileUpload::make('image')
                                                    ->required()
                                                    ->directory('boarding-houses/rooms')
                                                    ->image()
                                            ]),
                                        Forms\Components\Toggle::make('is_available')
                                            ->required(),

                                    ])
                                    ->columns(2)
                                    ->columnSpanFull(),
                            ]),
                    ])->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('city.name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->searchable(),
                Tables\Columns\ImageColumn::make('thumbnail')
                    ->searchable(),
                // Menampilkan semua jenis room_type dalam satu boarding house
                Tables\Columns\TextColumn::make('rooms_summary')
                    ->label('Room Types')
                    ->getStateUsing(function ($record) {
                        return $record->rooms->pluck('room_type')->unique()->implode(', ');
                    }),
                // Menampilkan rentang harga dari kamar-kamar dalam boarding house
                Tables\Columns\TextColumn::make('price_range')
                    ->label('Price per Day')
                    ->getStateUsing(function ($record) {
                        $prices = $record->rooms->pluck('price_per_day')->sort()->values();
                        if ($prices->isEmpty())
                            return '-';
                        if ($prices->count() === 1)
                            return 'Rp ' . number_format($prices->first(), 0, ',', '.');
                        return 'Rp ' . number_format($prices->first(), 0, ',', '.') . ' - Rp ' . number_format($prices->last(), 0, ',', '.');
                    }),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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

        // Super Admin bisa melihat semua data
        if ($user->hasRole('super_admin')) {
            return parent::getEloquentQuery();
        }

        // Admin hanya melihat miliknya sendiri
        return parent::getEloquentQuery()
            ->where('user_id', $user->id);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBoardingHouses::route('/'),
            'create' => Pages\CreateBoardingHouse::route('/create'),
            'edit' => Pages\EditBoardingHouse::route('/{record}/edit'),
        ];
    }
}
