<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class InvestmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'investments';

    protected static ?string $title = 'Investimentos';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('investment_plan_id')
                    ->label('Plano')
                    ->relationship('investmentPlan', 'name')
                    ->required(),
                
                Forms\Components\TextInput::make('amount')
                    ->label('Valor')
                    ->numeric()
                    ->prefix('$')
                    ->required(),
                
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pendente',
                        'active' => 'Ativo',
                        'completed' => 'Completo',
                        'cancelled' => 'Cancelado',
                    ])
                    ->required(),
                
                Forms\Components\Toggle::make('is_sponsored')
                    ->label('Patrocinado'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID'),
                
                Tables\Columns\TextColumn::make('investmentPlan.name')
                    ->label('Plano'),
                
                Tables\Columns\TextColumn::make('amount')
                    ->label('Valor')
                    ->money('USD'),
                
                Tables\Columns\TextColumn::make('current_balance')
                    ->label('Saldo Atual')
                    ->money('USD'),
                
                Tables\Columns\IconColumn::make('is_sponsored')
                    ->label('Patrocinado')
                    ->boolean(),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'active',
                        'info' => 'completed',
                        'danger' => 'cancelled',
                    ]),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
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