<?php

namespace App\Filament\Resources\InvestmentResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ReferralCommissionsRelationManager extends RelationManager
{
    protected static string $relationship = 'referralCommissionSource';

    protected static ?string $title = 'Comissões de Indicação';

    protected static ?string $inverseRelationship = 'source';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('Recebedor')
                    ->relationship('receiver', 'name')
                    ->searchable()
                    ->required(),
                
                Forms\Components\Select::make('source_user_id')
                    ->label('Indicado')
                    ->relationship('sourceUser', 'name')
                    ->searchable()
                    ->required(),
                
                Forms\Components\Select::make('type')
                    ->label('Tipo')
                    ->options([
                        'investment' => 'Investimento',
                        'profit' => 'Lucro',
                    ])
                    ->required(),
                
                Forms\Components\TextInput::make('level')
                    ->label('Nível')
                    ->numeric()
                    ->required()
                    ->minValue(1),
                
                Forms\Components\TextInput::make('amount')
                    ->label('Valor')
                    ->numeric()
                    ->prefix('$')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('receiver.name')
                    ->label('Recebedor')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('sourceUser.name')
                    ->label('Indicado')
                    ->searchable(),
                
                Tables\Columns\BadgeColumn::make('type')
                    ->label('Tipo')
                    ->colors([
                        'primary' => 'investment',
                        'success' => 'profit',
                    ])
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'investment' => 'Investimento',
                        'profit' => 'Lucro',
                        default => $state,
                    }),
                
                Tables\Columns\BadgeColumn::make('level')
                    ->label('Nível')
                    ->colors([
                        'success' => 1,
                        'warning' => fn ($state) => $state >= 2 && $state <= 5,
                        'danger' => fn ($state) => $state > 5,
                    ]),
                
                Tables\Columns\TextColumn::make('amount')
                    ->label('Valor')
                    ->money('USD')
                    ->color('success'),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data')
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