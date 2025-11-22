<?php

namespace App\Filament\Resources\InvestmentResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class BotInstanceRelationManager extends RelationManager
{
    protected static string $relationship = 'botInstance';

    protected static ?string $title = 'Bot Instance';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('instance_id')
                    ->label('Instance ID')
                    ->disabled()
                    ->dehydrated(false),
                
                Forms\Components\Toggle::make('is_active')
                    ->label('Bot Ativo')
                    ->required(),
                
                Forms\Components\KeyValue::make('config')
                    ->label('Configurações')
                    ->keyLabel('Chave')
                    ->valueLabel('Valor'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('instance_id')
            ->columns([
                Tables\Columns\TextColumn::make('instance_id')
                    ->label('Instance ID')
                    ->copyable(),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                
                Tables\Columns\TextColumn::make('total_trades')
                    ->label('Total Trades')
                    ->badge(),
                
                Tables\Columns\TextColumn::make('successful_trades')
                    ->label('Sucesso')
                    ->badge()
                    ->color('success'),
                
                Tables\Columns\TextColumn::make('success_rate')
                    ->label('Taxa de Sucesso')
                    ->suffix('%')
                    ->color('info'),
                
                Tables\Columns\TextColumn::make('total_profit')
                    ->label('Lucro Total')
                    ->money('USD')
                    ->color('success'),
                
                Tables\Columns\TextColumn::make('last_trade_at')
                    ->label('Último Trade')
                    ->dateTime('d/m/Y H:i'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}