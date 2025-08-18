<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Mail\TransactionStatusChangedMail;
use Illuminate\Support\Facades\Mail;

class EditTransaction extends EditRecord
{
    protected static string $resource = TransactionResource::class;
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $original = $this->record->getOriginal('transactions_status');
        if ($original !== $data['transactions_status']) {
            Mail::to($data['email'])->send(new TransactionStatusChangedMail($this->record));
        }
        return $data;
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
