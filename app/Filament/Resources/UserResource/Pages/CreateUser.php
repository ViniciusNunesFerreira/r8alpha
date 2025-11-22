<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Gerar referral code único se não existir
        if (empty($data['referral_code'])) {
            $data['referral_code'] = strtoupper(Str::random(8));
        }

        return $data;
    }
}