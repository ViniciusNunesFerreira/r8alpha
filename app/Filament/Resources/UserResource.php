<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use App\Models\Wallet;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    
    protected static ?string $navigationGroup = 'Gestão de Usuários';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informações Básicas')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('username')
                            ->label('Username')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('email')
                            ->label('E-mail')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('phone')
                            ->label('Telefone')
                            ->tel()
                            ->maxLength(20),
                        
                        Forms\Components\TextInput::make('password')
                            ->label('Senha')
                            ->password()
                            ->required(fn (string $context): bool => $context === 'create')
                            ->dehydrated(fn ($state) => filled($state))
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('Indicação & Status')
                    ->schema([
                        Forms\Components\TextInput::make('referral_code')
                            ->label('Código de Indicação')
                            ->disabled()
                            ->dehydrated(false),
                        
                        Forms\Components\Select::make('referred_by')
                            ->label('Indicado por')
                            ->relationship('sponsor', 'name')
                            ->searchable()
                            ->preload(),
                        
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'active' => 'Ativo',
                                'inactive' => 'Inativo',
                                'suspended' => 'Suspenso',
                            ])
                            ->default('active')
                            ->required(),
                        
                        Forms\Components\Select::make('type')
                            ->label('Tipo')
                            ->options([
                                'user' => 'Usuário',
                                'admin' => 'Administrador',
                            ])
                            ->default('user')
                            ->required(),
                        
                        Forms\Components\DateTimePicker::make('first_investment_at')
                            ->label('Primeiro Investimento')
                            ->disabled()
                            ->dehydrated(false),
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
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('username')
                    ->label('Username')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                
                Tables\Columns\TextColumn::make('referral_code')
                    ->label('Código')
                    ->sortable()
                    ->copyable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('depositWallet.balance')
                    ->label('Saldo Normal')
                    ->money('USD')
                    ->sortable()
                    ->color('success'),
                
                Tables\Columns\TextColumn::make('depositWallet.sponsored_balance')
                    ->label('Saldo Patrocinado')
                    ->money('USD')
                    ->sortable()
                    ->color('warning'),
                
                Tables\Columns\TextColumn::make('investments_count')
                    ->label('Investimentos')
                    ->counts('investments')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('active_bots_count')
                    ->label('Bots Ativos')
                    ->sortable(),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'active',
                        'warning' => 'inactive',
                        'danger' => 'suspended',
                    ]),
                
                Tables\Columns\BadgeColumn::make('type')
                    ->label('Tipo')
                    ->colors([
                        'primary' => 'admin',
                        'secondary' => 'client',
                        'secondary' => 'sponsorship',
                    ]),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Cadastro')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Ativo',
                        'inactive' => 'Inativo',
                        'suspended' => 'Suspenso',
                    ]),
                
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipo')
                    ->options([
                        'client' => 'Usuário',
                        'admin' => 'Administrador',
                        'sponsorship' => 'Patrocinado'
                    ]),
                
                Tables\Filters\Filter::make('with_investments')
                    ->label('Com Investimentos')
                    ->query(fn (Builder $query): Builder => $query->has('investments')),
            ])
            ->actions([
                Tables\Actions\Action::make('add_balance')
                    ->label('Adicionar Saldo')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->form([
                        Forms\Components\Select::make('balance_type')
                            ->label('Tipo de Saldo')
                            ->options([
                                'normal' => 'Saldo Normal (Depósito)',
                                'sponsored' => 'Saldo Patrocinado',
                            ])
                            ->required()
                            ->default('normal')
                            ->live()
                            ->helperText('Saldo patrocinado gera investimentos com rendimento diferenciado'),
                        
                        Forms\Components\TextInput::make('amount')
                            ->label('Valor (USD)')
                            ->numeric()
                            ->required()
                            ->minValue(0.01)
                            ->prefix('$')
                            ->step(0.01),
                        
                        Forms\Components\Textarea::make('description')
                            ->label('Descrição/Motivo')
                            ->required()
                            ->maxLength(500)
                            ->rows(3),
                    ])
                    ->action(function (User $record, array $data): void {
                        // Busca ou cria a wallet de depósito
                        $wallet = $record->depositWallet()->firstOrCreate(
                            ['type' => 'deposit'],
                            [
                                'balance' => 0,
                                'sponsored_balance' => 0,
                                'total_deposited' => 0,
                                'total_sponsored' => 0,
                                'total_withdrawn' => 0,
                                'total_profit' => 0,
                            ]
                        );

                        // Adiciona o saldo conforme o tipo
                        if ($data['balance_type'] === 'sponsored') {
                            $wallet->addSponsoredBalance($data['amount'], $data['description']);
                            
                            Notification::make()
                                ->success()
                                ->title('Saldo Patrocinado Adicionado')
                                ->body("$" . number_format($data['amount'], 2) . " adicionado ao saldo patrocinado de {$record->name}")
                                ->send();
                        } else {
                            $wallet->addBalance($data['amount'], $data['description']);
                            
                            Notification::make()
                                ->success()
                                ->title('Saldo Normal Adicionado')
                                ->body("$" . number_format($data['amount'], 2) . " adicionado ao saldo de {$record->name}")
                                ->send();
                        }
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Adicionar Saldo ao Usuário')
                    ->modalDescription('Esta ação adicionará saldo à carteira do usuário.')
                    ->modalSubmitActionLabel('Adicionar Saldo'),
                
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
            RelationManagers\WalletsRelationManager::class,
            RelationManagers\ReferralsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}