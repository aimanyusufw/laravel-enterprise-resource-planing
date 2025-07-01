<?php

namespace App\Providers\Filament\Auth;

use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Auth\Login as BaseAuth;

class CustomLogin extends BaseAuth
{
    protected function getForms(): array
    {
        return [
            "form" => $this->form(
                $this->makeForm()->schema([
                    $this->getLoginFormComponent(),
                    $this->getPasswordFormComponent(),
                    $this->getRememberFormComponent()
                ])
            )->statePath('data'),
        ];
    }

    protected function getLoginFormComponent(): Component
    {
        return TextInput::make('login')
            ->label("Email / User name")
            ->required()
            ->autocomplete()
            ->autofocus()
            ->extraInputAttributes(['tabIndex' =>  1]);
    }

    protected function getCredentialsFromFormData(array $data): array
    {
        $login_type = filter_var($data['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        return [
            $login_type => $data['login'],
            'password' => $data['password']
        ];
    }
}
