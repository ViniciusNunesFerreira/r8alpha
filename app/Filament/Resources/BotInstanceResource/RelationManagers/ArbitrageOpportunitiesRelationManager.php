<?php

namespace App\Filament\Resources\BotInstanceResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ArbitrageOpportunitiesRelationManager extends RelationManager
{
    protected static string $relationship = 'arbitrageOpportunities';

    protected static ?string $title = 'Oportunidades de Arbitragem';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('symbol')
                    ->label('Par')
                    ->placeholder('BTC/USDT')
                    ->required(),
                
                Forms\Components\TextInput::make('exchange_from')
                    ->label('Exchange de Origem')
                    ->required(),
                
                Forms\Components\TextInput::make('exchange_to')
                    ->label('Exchange de Destino')
                    ->required(),
                
                Forms\Components\TextInput::make('price_from')
                    ->label('Preço Origem')
                    ->numeric()
                    ->prefix('$')
                    ->required(),
                
                Forms\Components\TextInput::make('price_to')
                    ->label('Preço Destino')
                    ->numeric()
                    ->prefix('$')
                    ->required(),
                
                Forms\Components\TextInput::make('profit_percentage')
                    ->label('% de Lucro')
                    ->numeric()
                    ->suffix('%')
                    ->required(),
                
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'detected' => 'Detectada',
                        'executed' => 'Executada',
                        'expired' => 'Expirada',
                        'failed' => 'Falhou',
                    ])
                    ->required(),
                
                Forms\Components\DateTimePicker::make('detected_at')
                    ->label('Detectada em')
                    ->required(),
                
                Forms\Components\DateTimePicker::make('executed_at')
                    ->label('Executada em'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('symbol')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('symbol')
                    ->label('Par')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                
                Tables\Columns\TextColumn::make('exchange_from')
                    ->label('De')
                    ->badge()
                    ->color('primary'),
                
                Tables\Columns\TextColumn::make('exchange_to')
                    ->label('Para')
                    ->badge()
                    ->color('success'),
                
                Tables\Columns\TextColumn::make('price_from')
                    ->label('Preço Origem')
                    ->money('USD'),
                
                Tables\Columns\TextColumn::make('price_to')
                    ->label('Preço Destino')
                    ->money('USD'),
                
                Tables\Columns\TextColumn::make('profit_percentage')
                    ->label('% Lucro')
                    ->suffix('%')
                    ->color('success')
                    ->weight('bold'),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'info' => 'detected',
                        'success' => 'executed',
                        'warning' => 'expired',
                        'danger' => 'failed',
                    ])
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'detected' => 'Detectada',
                        'executed' => 'Executada',
                        'expired' => 'Expirada',
                        'failed' => 'Falhou',
                        default => $state,
                    }),
                
                Tables\Columns\TextColumn::make('detected_at')
                    ->label('Detectada em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'detected' => 'Detectada',
                        'executed' => 'Executada',
                        'expired' => 'Expirada',
                        'failed' => 'Falhou',
                    ]),
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
            ])
            ->defaultSort('detected_at', 'desc');
    }
}