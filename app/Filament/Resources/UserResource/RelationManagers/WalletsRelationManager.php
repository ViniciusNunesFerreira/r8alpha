<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class WalletsRelationManager extends RelationManager
{
    protected static string $relationship = 'wallets';

    protected static ?string $title = 'Carteiras';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('type')
                    ->label('Tipo')
                    ->options([
                        'deposit' => 'Depósito',
                        'referral' => 'Indicação',
                        'profit' => 'Lucro',
                    ])
                    ->required(),
                
                Forms\Components\TextInput::make('balance')
                    ->label('Saldo Normal')
                    ->numeric()
                    ->prefix('$')
                    ->disabled()
                    ->dehydrated(false),
                
                Forms\Components\TextInput::make('sponsored_balance')
                    ->label('Saldo Patrocinado')
                    ->numeric()
                    ->prefix('$')
                    ->disabled()
                    ->dehydrated(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('type')
            ->columns([
                Tables\Columns\BadgeColumn::make('type')
                    ->label('Tipo')
                    ->colors([
                        'primary' => 'deposit',
                        'success' => 'referral',
                        'info' => 'profit',
                    ])
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'deposit' => 'Depósito',
                        'referral' => 'Indicação',
                        'profit' => 'Lucro',
                        default => $state,
                    }),
                
                Tables\Columns\TextColumn::make('balance')
                    ->label('Saldo Normal')
                    ->money('USD')
                    ->color('success'),
                
                Tables\Columns\TextColumn::make('sponsored_balance')
                    ->label('Saldo Patrocinado')
                    ->money('USD')
                    ->color('warning'),
                
                Tables\Columns\TextColumn::make('total_deposited')
                    ->label('Total Depositado')
                    ->money('USD'),
                
                Tables\Columns\TextColumn::make('total_withdrawn')
                    ->label('Total Retirado')
                    ->money('USD'),
                
                Tables\Columns\TextColumn::make('total_profit')
                    ->label('Total Lucro')
                    ->money('USD'),
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