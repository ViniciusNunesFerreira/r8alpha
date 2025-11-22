<?php

namespace App\Filament\Resources\BotInstanceResource\Pages;

use App\Filament\Resources\BotInstanceResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewBotInstance extends ViewRecord
{
    protected static string $resource = BotInstanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
