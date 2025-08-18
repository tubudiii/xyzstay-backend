<?php

namespace App\Filament\Auth;

use Filament\Forms\Form;
use Filament\Pages\Auth\Register;
use Illuminate\Database\Eloquent\Model;

class CustomRegister extends Register
{
    protected function handleRegistration(array $data): Model
    {
        $user = parent::handleRegistration($data);
        // Assign default role 'admin' after registration
        $user->assignRole('admin');
        return $user;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
                // Tambahkan komponen lain jika diperlukan
            ]);
    }
}
