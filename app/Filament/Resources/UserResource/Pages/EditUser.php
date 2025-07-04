<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\Role;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function afterSave(): void
    {
        $user = $this->record;
        $roles = $user->getRoleNames()->toArray();

        if (!empty($roles)) {
            activity()
                ->event("attach roles")
                ->performedOn($user)
                ->causedBy(auth()->user())
                ->withProperties([
                    'user' => $user->username,
                    'roles' => $roles
                ])
                ->log('User roles updated');
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
