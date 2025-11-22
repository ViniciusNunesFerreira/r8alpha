<?php

namespace App\Filament\Resources\BotInstanceResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class TradesRelationManager extends RelationManager
{
    protected static string $relationship = 'trades';

    protected static ?string $title = 'Histórico de Trades';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('type')
                    ->label('Tipo')
                    ->options([
                        'buy' => 'Compra',
                        'sell' => 'Venda',
                    ])
                    ->required(),
                
                Forms\Components\TextInput::make('symbol')
                    ->label('Par')
                    ->placeholder('BTC/USDT')
                    ->required(),
                
                Forms\Components\TextInput::make('amount')
                    ->label('Quantidade')
                    ->numeric()
                    ->required(),
                
                Forms\Components\TextInput::make('price')
                    ->label('Preço')
                    ->numeric()
                    ->prefix('$')
                    ->required(),
                
                Forms\Components\TextInput::make('profit')
                    ->label('Lucro')
                    ->numeric()
                    ->prefix('$'),
                
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pendente',
                        'executed' => 'Executado',
                        'cancelled' => 'Cancelado',
                        'failed' => 'Falhou',
                    ])
                    ->required(),
                
                Forms\Components\Textarea::make('notes')
                    ->label('Observações')
                    ->rows(3),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                
                Tables\Columns\BadgeColumn::make('type')
                    ->label('Tipo')
                    ->colors([
                        'success' => 'buy',
                        'danger' => 'sell',
                    ])
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'buy' => 'Compra',
                        'sell' => 'Venda',
                        default => $state,
                    }),
                
                Tables\Columns\TextColumn::make('symbol')
                    ->label('Par')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('amount')
                    ->label('Quantidade')
                    ->numeric(decimalPlaces: 8),
                
                Tables\Columns\TextColumn::make('price')
                    ->label('Preço')
                    ->money('USD'),
                
                Tables\Columns\TextColumn::make('profit')
                    ->label('Lucro')
                    ->money('USD')
                    ->color(fn ($state) => $state > 0 ? 'success' : ($state < 0 ? 'danger' : 'gray')),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'executed',
                        'danger' => ['cancelled', 'failed'],
                    ])
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'pending' => 'Pendente',
                        'executed' => 'Executado',
                        'cancelled' => 'Cancelado',
                        'failed' => 'Falhou',
                        default => $state,
                    }),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipo')
                    ->options([
                        'buy' => 'Compra',
                        'sell' => 'Venda',
                    ]),
                
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pendente',
                        'executed' => 'Executado',
                        'cancelled' => 'Cancelado',
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
            ->defaultSort('created_at', 'desc');
    }
}