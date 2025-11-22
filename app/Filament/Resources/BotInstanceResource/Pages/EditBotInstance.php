<?php

namespace App\Filament\Resources\BotInstanceResource\Pages;

use App\Filament\Resources\BotInstanceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBotInstance extends EditRecord
{
    protected static string $resource = BotInstanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
