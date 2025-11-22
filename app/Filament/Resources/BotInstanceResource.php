<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BotInstanceResource\Pages;
use App\Filament\Resources\BotInstanceResource\RelationManagers;
use App\Models\BotInstance;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BotInstanceResource extends Resource
{
    protected static ?string $model = BotInstance::class;

    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';
    
    protected static ?string $navigationGroup = 'Bots & Trading';
    
    protected static ?string $modelLabel = 'Instância de Bot';
    
    protected static ?string $pluralModelLabel = 'Instâncias de Bots';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('Usuário')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                
                Forms\Components\Select::make('investment_id')
                    ->label('Investimento')
                    ->relationship('investment', 'id')
                    ->searchable()
                    ->preload()
                    ->required(),
                
                Forms\Components\TextInput::make('instance_id')
                    ->label('Instance ID')
                    ->disabled()
                    ->dehydrated(false),
                
                Forms\Components\Toggle::make('is_active')
                    ->label('Bot Ativo')
                    ->default(false),
                
                Forms\Components\KeyValue::make('config')
                    ->label('Configurações')
                    ->keyLabel('Chave')
                    ->valueLabel('Valor'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('instance_id')
                    ->label('Instance ID')
                    ->searchable()
                    ->copyable(),
                
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuário')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                
                Tables\Columns\TextColumn::make('total_trades')
                    ->label('Total de Trades')
                    ->sortable()
                    ->badge(),
                
                Tables\Columns\TextColumn::make('successful_trades')
                    ->label('Trades com Sucesso')
                    ->sortable()
                    ->badge()
                    ->color('success'),
                
                Tables\Columns\TextColumn::make('success_rate')
                    ->label('Taxa de Sucesso')
                    ->suffix('%')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('total_profit')
                    ->label('Lucro Total')
                    ->money('USD')
                    ->sortable()
                    ->color('success'),
                
                Tables\Columns\TextColumn::make('last_trade_at')
                    ->label('Último Trade')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('Todos')
                    ->trueLabel('Apenas Ativos')
                    ->falseLabel('Apenas Inativos'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBotInstances::route('/'),
            'create' => Pages\CreateBotInstance::route('/create'),
            'view' => Pages\ViewBotInstance::route('/{record}'),
            'edit' => Pages\EditBotInstance::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\TradesRelationManager::class,
            RelationManagers\ArbitrageOpportunitiesRelationManager::class,
        ];
    }
}