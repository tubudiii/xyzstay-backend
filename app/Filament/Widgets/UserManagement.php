<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class UserManagement extends BaseWidget
{
    public function table(Table $table): Table
    {
        return $table
            ->query(
                // Assuming you have a User model and want to display user data
                \App\Models\User::query()->orderByDesc('created_at', 'DESC')
            )
            ->columns([
                // ...
            ]);
    }
}
