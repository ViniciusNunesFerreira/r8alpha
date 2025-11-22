<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ReferralsRelationManager extends RelationManager
{
    protected static string $relationship = 'referrals';

    protected static ?string $title = 'Rede de Indicações';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('Indicado')
                    ->relationship('user', 'name')
                    ->required(),
                
                Forms\Components\TextInput::make('level')
                    ->label('Nível')
                    ->numeric()
                    ->required()
                    ->default(1),
                
                Forms\Components\TextInput::make('commission_earned')
                    ->label('Comissão Ganha')
                    ->numeric()
                    ->prefix('$')
                    ->disabled()
                    ->dehydrated(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('user.name')
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Indicado')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('user.email')
                    ->label('E-mail')
                    ->searchable(),
                
                Tables\Columns\BadgeColumn::make('level')
                    ->label('Nível')
                    ->colors([
                        'success' => 1,
                        'warning' => fn ($state) => $state >= 2 && $state <= 5,
                        'danger' => fn ($state) => $state > 5,
                    ]),
                
                Tables\Columns\TextColumn::make('commission_earned')
                    ->label('Comissão Total')
                    ->money('USD')
                    ->color('success'),
                
                Tables\Columns\TextColumn::make('user.investments_count')
                    ->label('Investimentos')
                    ->counts('user.investments')
                    ->badge(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime('d/m/Y'),
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