<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvestmentPlanResource\Pages;
use App\Filament\Resources\InvestmentPlanResource\RelationManagers;
use App\Models\InvestmentPlan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class InvestmentPlanResource extends Resource
{
    protected static ?string $model = InvestmentPlan::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    
    protected static ?string $navigationGroup = 'Investimentos';
    
    protected static ?int $navigationSort = 2;
    
    protected static ?string $modelLabel = 'Plano de Investimento';
    
    protected static ?string $pluralModelLabel = 'Planos de Investimento';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informações do Plano')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nome do Plano')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\Textarea::make('description')
                            ->label('Descrição')
                            ->required()
                            ->rows(4)
                            ->maxLength(1000),
                    ]),

                Forms\Components\Section::make('Valores de Investimento')
                    ->schema([
                        Forms\Components\TextInput::make('min_amount')
                            ->label('Valor Mínimo')
                            ->numeric()
                            ->prefix('$')
                            ->required()
                            ->minValue(0.01),
                        
                        Forms\Components\TextInput::make('max_amount')
                            ->label('Valor Máximo')
                            ->numeric()
                            ->prefix('$')
                            ->required()
                            ->minValue(0.01),
                    ])->columns(2),

                Forms\Components\Section::make('Retornos e Duração')
                    ->schema([
                        Forms\Components\TextInput::make('daily_return_min')
                            ->label('Retorno Diário Mínimo (%)')
                            ->numeric()
                            ->suffix('%')
                            ->required()
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(0.01),
                        
                        Forms\Components\TextInput::make('daily_return_max')
                            ->label('Retorno Diário Máximo (%)')
                            ->numeric()
                            ->suffix('%')
                            ->required()
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(0.01),
                        
                        Forms\Components\TextInput::make('duration_days')
                            ->label('Duração (dias)')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->helperText('Duração total do plano em dias'),
                    ])->columns(3),

                Forms\Components\Section::make('Configurações')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Plano Ativo')
                            ->helperText('Apenas planos ativos aparecem para os usuários')
                            ->default(true)
                            ->inline(false),
                        
                        Forms\Components\Toggle::make('is_capital_back')
                            ->label('Devolve Capital')
                            ->helperText('Se ativo, o valor investido retorna ao final do período')
                            ->default(true)
                            ->inline(false),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                
                Tables\Columns\TextColumn::make('min_amount')
                    ->label('Min.')
                    ->money('USD')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('max_amount')
                    ->label('Max.')
                    ->money('USD')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('daily_return_min')
                    ->label('Retorno Min.')
                    ->suffix('%')
                    ->sortable()
                    ->color('success'),
                
                Tables\Columns\TextColumn::make('daily_return_max')
                    ->label('Retorno Max.')
                    ->suffix('%')
                    ->sortable()
                    ->color('success'),
                
                Tables\Columns\TextColumn::make('duration_days')
                    ->label('Duração')
                    ->suffix(' dias')
                    ->sortable(),
                
                Tables\Columns\IconColumn::make('is_capital_back')
                    ->label('Devolve Capital')
                    ->boolean()
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Ativo')
                    ->boolean()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('investments_count')
                    ->label('Investimentos')
                    ->counts('investments')
                    ->sortable()
                    ->badge()
                    ->color('info'),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('Todos')
                    ->trueLabel('Apenas Ativos')
                    ->falseLabel('Apenas Inativos'),
                
                Tables\Filters\TernaryFilter::make('is_capital_back')
                    ->label('Devolução de Capital')
                    ->placeholder('Todos')
                    ->trueLabel('Com Devolução')
                    ->falseLabel('Sem Devolução'),
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

    public static function getRelations(): array
    {
        return [
            RelationManagers\InvestmentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvestmentPlans::route('/'),
            'create' => Pages\CreateInvestmentPlan::route('/create'),
            'view' => Pages\ViewInvestmentPlan::route('/{record}'),
            'edit' => Pages\EditInvestmentPlan::route('/{record}/edit'),
        ];
    }
}