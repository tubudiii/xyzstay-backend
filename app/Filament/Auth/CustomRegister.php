<?php

namespace App\Filament\Auth;

use App\Models\User;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Auth\Register;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;


class CustomRegister extends Register
{
    protected ?User $registeredUser = null;

    protected function handleRegistration(array $data): Model
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phone_number' => $data['phone_number'],
        ]);

        $user->assignRole('admin');
        $this->registeredUser = $user;

        return $user;
    }

    protected function hasEmailVerification(): bool
    {
        return false; // pastikan tidak aktif
    }

    protected function afterRegister(): void
    {
        if ($this->registeredUser) {
            auth()->login($this->registeredUser); // auto login
        }
    }

    protected function redirectPath(): string
    {
        return route('filament.admin.pages.dashboard');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
                TextInput::make('phone_number')
                    ->label('Phone Number')
                    ->required()
                    ->tel()
                    ->maxLength(15)
                    ->placeholder('Enter your phone number'),
            ]);
    }
}
