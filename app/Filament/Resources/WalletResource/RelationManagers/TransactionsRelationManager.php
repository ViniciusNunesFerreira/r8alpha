<?php

namespace App\Filament\Resources\WalletResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class TransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'transactions';

    protected static ?string $title = 'Histórico de Transações';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('type')
                    ->label('Tipo')
                    ->options([
                        'deposit' => 'Depósito',
                        'sponsored_deposit' => 'Depósito Patrocinado',
                        'withdrawal' => 'Saque',
                        'investment' => 'Investimento',
                        'profit' => 'Lucro',
                        'referral_commission' => 'Comissão de Indicação',
                        'transfer' => 'Transferência',
                    ])
                    ->required(),
                
                Forms\Components\TextInput::make('amount')
                    ->label('Valor')
                    ->numeric()
                    ->prefix('$')
                    ->required(),
                
                Forms\Components\TextInput::make('balance_after')
                    ->label('Saldo Após')
                    ->numeric()
                    ->prefix('$')
                    ->required(),
                
                Forms\Components\Textarea::make('description')
                    ->label('Descrição')
                    ->rows(3)
                    ->maxLength(500),
                
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pendente',
                        'completed' => 'Completo',
                        'cancelled' => 'Cancelado',
                        'failed' => 'Falhou',
                    ])
                    ->default('completed')
                    ->required(),
                
                Forms\Components\KeyValue::make('metadata')
                    ->label('Metadados')
                    ->keyLabel('Chave')
                    ->valueLabel('Valor'),
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
                        'success' => ['deposit', 'profit', 'referral_commission'],
                        'warning' => 'sponsored_deposit',
                        'danger' => 'withdrawal',
                        'info' => ['investment', 'transfer'],
                    ])
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'deposit' => 'Depósito',
                        'sponsored_deposit' => 'Depósito Patrocinado',
                        'withdrawal' => 'Saque',
                        'investment' => 'Investimento',
                        'profit' => 'Lucro',
                        'referral_commission' => 'Comissão',
                        'transfer' => 'Transferência',
                        default => $state,
                    }),
                
                Tables\Columns\TextColumn::make('amount')
                    ->label('Valor')
                    ->money('USD')
                    ->color(fn ($record) => 
                        in_array($record->type, ['deposit', 'sponsored_deposit', 'profit', 'referral_commission']) 
                            ? 'success' 
                            : 'danger'
                    ),
                
                Tables\Columns\TextColumn::make('balance_after')
                    ->label('Saldo Após')
                    ->money('USD'),
                
                Tables\Columns\TextColumn::make('description')
                    ->label('Descrição')
                    ->limit(50)
                    ->searchable(),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'completed',
                        'danger' => ['cancelled', 'failed'],
                    ])
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'pending' => 'Pendente',
                        'completed' => 'Completo',
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
                        'deposit' => 'Depósito',
                        'sponsored_deposit' => 'Depósito Patrocinado',
                        'withdrawal' => 'Saque',
                        'investment' => 'Investimento',
                        'profit' => 'Lucro',
                        'referral_commission' => 'Comissão',
                        'transfer' => 'Transferência',
                    ]),
                
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pendente',
                        'completed' => 'Completo',
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