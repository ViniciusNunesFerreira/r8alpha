<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WalletResource\Pages;
use App\Filament\Resources\WalletResource\RelationManagers;
use App\Models\Wallet;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WalletResource extends Resource
{
    protected static ?string $model = Wallet::class;

    protected static ?string $navigationIcon = 'heroicon-o-wallet';
    
    protected static ?string $navigationGroup = 'Financeiro';
    
    protected static ?int $navigationSort = 2;
    
    protected static ?string $modelLabel = 'Carteira';
    
    protected static ?string $pluralModelLabel = 'Carteiras';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('Usuário')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->disabled(fn (string $context): bool => $context === 'edit'),
                
                Forms\Components\Select::make('type')
                    ->label('Tipo de Carteira')
                    ->options([
                        'deposit' => 'Depósito',
                        'referral' => 'Indicação',
                        'profit' => 'Lucro',
                    ])
                    ->required()
                    ->disabled(fn (string $context): bool => $context === 'edit'),
                
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
                
                Forms\Components\TextInput::make('total_deposited')
                    ->label('Total Depositado')
                    ->numeric()
                    ->prefix('$')
                    ->disabled()
                    ->dehydrated(false),
                
                Forms\Components\TextInput::make('total_sponsored')
                    ->label('Total Patrocinado')
                    ->numeric()
                    ->prefix('$')
                    ->disabled()
                    ->dehydrated(false),
                
                Forms\Components\TextInput::make('total_withdrawn')
                    ->label('Total Retirado')
                    ->numeric()
                    ->prefix('$')
                    ->disabled()
                    ->dehydrated(false),
                
                Forms\Components\TextInput::make('total_profit')
                    ->label('Total de Lucro')
                    ->numeric()
                    ->prefix('$')
                    ->disabled()
                    ->dehydrated(false),
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
                    ->sortable()
                    ->color('success'),
                
                Tables\Columns\TextColumn::make('sponsored_balance')
                    ->label('Saldo Patrocinado')
                    ->money('USD')
                    ->sortable()
                    ->color('warning'),
                
                Tables\Columns\TextColumn::make('total_deposited')
                    ->label('Total Depositado')
                    ->money('USD')
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('total_sponsored')
                    ->label('Total Patrocinado')
                    ->money('USD')
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('total_withdrawn')
                    ->label('Total Retirado')
                    ->money('USD')
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('total_profit')
                    ->label('Total de Lucro')
                    ->money('USD')
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criada em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipo')
                    ->options([
                        'deposit' => 'Depósito',
                        'referral' => 'Indicação',
                        'profit' => 'Lucro',
                    ]),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWallets::route('/'),
            'create' => Pages\CreateWallet::route('/create'),
            'view' => Pages\ViewWallet::route('/{record}'),
            'edit' => Pages\EditWallet::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\TransactionsRelationManager::class,
        ];
    }
}