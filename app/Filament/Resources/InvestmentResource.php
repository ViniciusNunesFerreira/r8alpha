<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvestmentResource\Pages;
use App\Filament\Resources\InvestmentResource\RelationManagers;
use App\Models\Investment;
use App\Models\InvestmentPlan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InvestmentResource extends Resource
{
    protected static ?string $model = Investment::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    
    protected static ?string $navigationGroup = 'Investimentos';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informações do Investimento')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Usuário')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabled(fn (string $context): bool => $context === 'edit'),
                        
                        Forms\Components\Select::make('investment_plan_id')
                            ->label('Plano de Investimento')
                            ->relationship('investmentPlan', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabled(fn (string $context): bool => $context === 'edit')
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $plan = InvestmentPlan::find($state);
                                    if ($plan) {
                                        $set('min_amount_info', $plan->min_amount);
                                        $set('max_amount_info', $plan->max_amount);
                                    }
                                }
                            }),
                        
                        Forms\Components\TextInput::make('amount')
                            ->label('Valor do Investimento')
                            ->numeric()
                            ->prefix('$')
                            ->required()
                            ->minValue(0.01)
                            ->disabled(fn (string $context): bool => $context === 'edit')
                            ->helperText(fn ($get) => 
                                $get('min_amount_info') && $get('max_amount_info') 
                                    ? "Min: $" . number_format($get('min_amount_info'), 2) . " - Max: $" . number_format($get('max_amount_info'), 2)
                                    : ''
                            ),
                        
                        Forms\Components\TextInput::make('current_balance')
                            ->label('Saldo Atual')
                            ->numeric()
                            ->prefix('$')
                            ->disabled()
                            ->dehydrated(false),
                    ])->columns(2),

                Forms\Components\Section::make('Status e Tipo')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Pendente',
                                'active' => 'Ativo',
                                'completed' => 'Completo',
                                'cancelled' => 'Cancelado',
                            ])
                            ->required()
                            ->default('pending'),
                        
                        Forms\Components\Toggle::make('is_sponsored')
                            ->label('Investimento Patrocinado')
                            ->helperText('Investimentos patrocinados possuem regras de rendimento diferenciadas')
                            ->inline(false)
                            ->default(false),
                        
                        Forms\Components\Select::make('payment_method')
                            ->label('Método de Pagamento')
                            ->options([
                                'wallet' => 'Carteira (Saldo)',
                                'pix' => 'PIX',
                                'crypto' => 'Criptomoeda',
                            ])
                            ->disabled(fn (string $context): bool => $context === 'edit'),
                        
                        Forms\Components\Select::make('payment_status')
                            ->label('Status do Pagamento')
                            ->options([
                                'pending' => 'Pendente',
                                'processing' => 'Processando',
                                'confirmed' => 'Confirmado',
                                'failed' => 'Falhou',
                            ])
                            ->default('pending'),
                    ])->columns(2),

                Forms\Components\Section::make('Datas')
                    ->schema([
                        Forms\Components\DateTimePicker::make('started_at')
                            ->label('Data de Início'),
                        
                        Forms\Components\DateTimePicker::make('expires_at')
                            ->label('Data de Expiração'),
                        
                        Forms\Components\DateTimePicker::make('last_profit_at')
                            ->label('Último Lucro em')
                            ->disabled()
                            ->dehydrated(false),
                    ])->columns(3),

                Forms\Components\Section::make('Estatísticas')
                    ->schema([
                        Forms\Components\TextInput::make('total_profit')
                            ->label('Lucro Total')
                            ->numeric()
                            ->prefix('$')
                            ->disabled()
                            ->dehydrated(false),
                    ])->columns(1),

                Forms\Components\Section::make('Observações Administrativas')
                    ->schema([
                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Notas do Admin')
                            ->helperText('Observações internas sobre este investimento')
                            ->rows(4)
                            ->maxLength(1000),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuário')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('investmentPlan.name')
                    ->label('Plano')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                
                Tables\Columns\TextColumn::make('amount')
                    ->label('Valor')
                    ->money('USD')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('current_balance')
                    ->label('Saldo Atual')
                    ->money('USD')
                    ->sortable()
                    ->color('info'),
                
                Tables\Columns\TextColumn::make('total_profit')
                    ->label('Lucro Total')
                    ->money('USD')
                    ->sortable()
                    ->color('success'),
                
                Tables\Columns\IconColumn::make('is_sponsored')
                    ->label('Patrocinado')
                    ->boolean()
                    ->trueIcon('heroicon-o-gift')
                    ->falseIcon('heroicon-o-banknotes')
                    ->trueColor('warning')
                    ->falseColor('success')
                    ->sortable(),
                
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
                
                Tables\Columns\BadgeColumn::make('payment_status')
                    ->label('Pagamento')
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'processing',
                        'success' => 'confirmed',
                        'danger' => 'failed',
                    ])
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('started_at')
                    ->label('Iniciado em')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Expira em')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pendente',
                        'active' => 'Ativo',
                        'completed' => 'Completo',
                        'cancelled' => 'Cancelado',
                    ]),
                
                Tables\Filters\SelectFilter::make('payment_status')
                    ->label('Status do Pagamento')
                    ->options([
                        'pending' => 'Pendente',
                        'processing' => 'Processando',
                        'confirmed' => 'Confirmado',
                        'failed' => 'Falhou',
                    ]),
                
                Tables\Filters\TernaryFilter::make('is_sponsored')
                    ->label('Investimento Patrocinado')
                    ->placeholder('Todos')
                    ->trueLabel('Apenas Patrocinados')
                    ->falseLabel('Apenas Normais'),
                
                Tables\Filters\SelectFilter::make('investment_plan_id')
                    ->label('Plano')
                    ->relationship('investmentPlan', 'name')
                    ->searchable()
                    ->preload(),
                
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('De'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Até'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
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
            RelationManagers\BotInstanceRelationManager::class,
            RelationManagers\ReferralCommissionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvestments::route('/'),
            'create' => Pages\CreateInvestment::route('/create'),
            'view' => Pages\ViewInvestment::route('/{record}'),
            'edit' => Pages\EditInvestment::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'active')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}