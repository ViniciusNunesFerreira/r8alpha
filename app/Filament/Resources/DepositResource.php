<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DepositResource\Pages;
use App\Models\Deposit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class DepositResource extends Resource
{
    protected static ?string $model = Deposit::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    
    protected static ?string $navigationGroup = 'Financeiro';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informações do Depósito')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Usuário')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabled(fn (string $context): bool => $context === 'edit'),
                        
                        Forms\Components\TextInput::make('transaction_id')
                            ->label('ID da Transação')
                            ->disabled()
                            ->dehydrated(false),
                        
                        Forms\Components\Select::make('payment_method')
                            ->label('Método de Pagamento')
                            ->options([
                                'pix' => 'PIX',
                                'crypto' => 'Criptomoeda',
                            ])
                            ->required()
                            ->disabled(fn (string $context): bool => $context === 'edit'),
                        
                        Forms\Components\Select::make('gateway')
                            ->label('Gateway')
                            ->options([
                                'nowpayments' => 'NowPayments',
                                'mercadopago' => 'Mercado Pago',
                                'manual' => 'Manual',
                            ])
                            ->required()
                            ->disabled(fn (string $context): bool => $context === 'edit'),
                    ])->columns(2),

                Forms\Components\Section::make('Valores')
                    ->schema([
                        Forms\Components\TextInput::make('amount_usd')
                            ->label('Valor em USD')
                            ->numeric()
                            ->prefix('$')
                            ->required()
                            ->disabled(fn (string $context): bool => $context === 'edit'),
                        
                        Forms\Components\TextInput::make('amount_brl')
                            ->label('Valor em BRL')
                            ->numeric()
                            ->prefix('R$')
                            ->disabled(fn (string $context): bool => $context === 'edit'),
                        
                        Forms\Components\TextInput::make('amount_crypto')
                            ->label('Valor em Cripto')
                            ->numeric()
                            ->disabled(fn (string $context): bool => $context === 'edit'),
                        
                        Forms\Components\TextInput::make('crypto_currency')
                            ->label('Criptomoeda')
                            ->disabled(fn (string $context): bool => $context === 'edit'),
                    ])->columns(2),

                Forms\Components\Section::make('Status e Datas')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Pendente',
                                'processing' => 'Processando',
                                'paid' => 'Pago',
                                'completed' => 'Completo',
                                'failed' => 'Falhou',
                                'expired' => 'Expirado',
                                'cancelled' => 'Cancelado',
                            ])
                            ->required()
                            ->disabled(),
                        
                        Forms\Components\DateTimePicker::make('expires_at')
                            ->label('Expira em')
                            ->disabled(),
                        
                        Forms\Components\DateTimePicker::make('paid_at')
                            ->label('Pago em')
                            ->disabled(),
                        
                        Forms\Components\DateTimePicker::make('confirmed_at')
                            ->label('Confirmado em')
                            ->disabled(),
                    ])->columns(2),

                Forms\Components\Section::make('Observações')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->label('Notas')
                            ->rows(3)
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
                
                Tables\Columns\TextColumn::make('transaction_id')
                    ->label('Transaction ID')
                    ->searchable()
                    ->copyable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuário')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Método')
                    ->badge()
                    ->colors([
                        'primary' => 'pix',
                        'success' => 'crypto',
                    ])
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'pix' => 'PIX',
                        'crypto' => 'Cripto',
                        default => $state,
                    }),
                
                Tables\Columns\TextColumn::make('amount_usd')
                    ->label('Valor USD')
                    ->money('USD')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('amount_brl')
                    ->label('Valor BRL')
                    ->money('BRL')
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'processing',
                        'success' => ['paid', 'completed'],
                        'danger' => ['failed', 'expired', 'cancelled'],
                    ])
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'pending' => 'Pendente',
                        'processing' => 'Processando',
                        'paid' => 'Pago',
                        'completed' => 'Completo',
                        'failed' => 'Falhou',
                        'expired' => 'Expirado',
                        'cancelled' => 'Cancelado',
                        default => $state,
                    }),
                
                Tables\Columns\TextColumn::make('gateway')
                    ->label('Gateway')
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('paid_at')
                    ->label('Pago em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pendente',
                        'processing' => 'Processando',
                        'paid' => 'Pago',
                        'completed' => 'Completo',
                        'failed' => 'Falhou',
                        'expired' => 'Expirado',
                        'cancelled' => 'Cancelado',
                    ]),
                
                Tables\Filters\SelectFilter::make('payment_method')
                    ->label('Método de Pagamento')
                    ->options([
                        'pix' => 'PIX',
                        'crypto' => 'Criptomoeda',
                    ]),
                
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
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('confirm')
                        ->label('Confirmar Pagamento')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn (Deposit $record): bool => $record->status === 'pending')
                        ->requiresConfirmation()
                        ->modalHeading('Confirmar Pagamento Manual')
                        ->modalDescription(fn (Deposit $record) => 
                            "Tem certeza que deseja confirmar o pagamento de {$record->formatted_amount_usd} para {$record->user->name}? O saldo será creditado automaticamente."
                        )
                        ->form([
                            Forms\Components\Textarea::make('admin_note')
                                ->label('Observação do Admin')
                                ->helperText('Detalhe o motivo da confirmação manual')
                                ->required()
                                ->rows(3),
                        ])
                        ->action(function (Deposit $record, array $data): void {
                            try {
                                $record->markAsConfirmed();
                                
                                // Adicionar nota
                                $note = ($record->notes ?? '') . "\n\n[" . now()->format('d/m/Y H:i') . "] Confirmado manualmente pelo admin: " . $data['admin_note'];
                                $record->update(['notes' => $note]);
                                
                                Notification::make()
                                    ->success()
                                    ->title('Depósito Confirmado')
                                    ->body("Pagamento de {$record->formatted_amount_usd} confirmado com sucesso!")
                                    ->send();
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->danger()
                                    ->title('Erro ao Confirmar')
                                    ->body('Erro: ' . $e->getMessage())
                                    ->send();
                            }
                        }),
                    
                    Tables\Actions\Action::make('mark_as_failed')
                        ->label('Marcar como Falhou')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(fn (Deposit $record): bool => $record->status === 'pending')
                        ->requiresConfirmation()
                        ->modalHeading('Marcar Pagamento como Falhou')
                        ->form([
                            Forms\Components\Textarea::make('failure_reason')
                                ->label('Motivo da Falha')
                                ->required()
                                ->rows(3),
                        ])
                        ->action(function (Deposit $record, array $data): void {
                            $record->markAsFailed($data['failure_reason']);
                            
                            Notification::make()
                                ->warning()
                                ->title('Depósito Marcado como Falhou')
                                ->body('O depósito foi marcado como falhou.')
                                ->send();
                        }),
                    
                    Tables\Actions\Action::make('mark_as_expired')
                        ->label('Marcar como Expirado')
                        ->icon('heroicon-o-clock')
                        ->color('warning')
                        ->visible(fn (Deposit $record): bool => $record->status === 'pending')
                        ->requiresConfirmation()
                        ->modalHeading('Marcar Pagamento como Expirado')
                        ->modalDescription('Esta ação marcará o pagamento como expirado.')
                        ->action(function (Deposit $record): void {
                            $record->markAsExpired();
                            
                            Notification::make()
                                ->info()
                                ->title('Depósito Expirado')
                                ->body('O depósito foi marcado como expirado.')
                                ->send();
                        }),
                    
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make()
                        ->visible(fn (Deposit $record): bool => $record->status === 'pending'),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()?->type === 'admin'),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDeposits::route('/'),
            'create' => Pages\CreateDeposit::route('/create'),
            'view' => Pages\ViewDeposit::route('/{record}'),
            'edit' => Pages\EditDeposit::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $count = static::getModel()::where('status', 'pending')->count();
        return $count > 0 ? 'warning' : null;
    }
}