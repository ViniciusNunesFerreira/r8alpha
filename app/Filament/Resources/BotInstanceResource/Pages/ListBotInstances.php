<?php

namespace App\Filament\Resources\BotInstanceResource\Pages;

use App\Filament\Resources\BotInstanceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBotInstances extends ListRecords
{
    protected static string $resource = BotInstanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
