<?php

namespace App\Filament\Resources\InvestmentPlanResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class InvestmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'investments';

    protected static ?string $title = 'Investimentos neste Plano';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('Usuário')
                    ->relationship('user', 'name')
                    ->searchable()
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
                
                Forms\Components\DateTimePicker::make('started_at')
                    ->label('Data de Início'),
                
                Forms\Components\DateTimePicker::make('expires_at')
                    ->label('Data de Expiração'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID'),
                
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuário')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('amount')
                    ->label('Valor')
                    ->money('USD')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('current_balance')
                    ->label('Saldo Atual')
                    ->money('USD')
                    ->color('info'),
                
                Tables\Columns\TextColumn::make('total_profit')
                    ->label('Lucro')
                    ->money('USD')
                    ->color('success'),
                
                Tables\Columns\IconColumn::make('is_sponsored')
                    ->label('Patrocinado')
                    ->boolean()
                    ->trueIcon('heroicon-o-gift')
                    ->trueColor('warning'),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'active',
                        'info' => 'completed',
                        'danger' => 'cancelled',
                    ])
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'pending' => 'Pendente',
                        'active' => 'Ativo',
                        'completed' => 'Completo',
                        'cancelled' => 'Cancelado',
                        default => $state,
                    }),
                
                Tables\Columns\TextColumn::make('started_at')
                    ->label('Início')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pendente',
                        'active' => 'Ativo',
                        'completed' => 'Completo',
                        'cancelled' => 'Cancelado',
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