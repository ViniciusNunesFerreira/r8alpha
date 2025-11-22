<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReferralResource\Pages;
use App\Models\Referral;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ReferralResource extends Resource
{
    protected static ?string $model = Referral::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    
    protected static ?string $navigationGroup = 'Rede de Indicação';
    
    protected static ?int $navigationSort = 1;
    
    protected static ?string $modelLabel = 'Indicação';
    
    protected static ?string $pluralModelLabel = 'Indicações';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('sponsor_id')
                    ->label('Patrocinador')
                    ->relationship('sponsor', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                
                Forms\Components\Select::make('user_id')
                    ->label('Indicado')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                
                Forms\Components\TextInput::make('level')
                    ->label('Nível')
                    ->numeric()
                    ->required()
                    ->minValue(1)
                    ->maxValue(10)
                    ->default(1),
                
                Forms\Components\TextInput::make('commission_earned')
                    ->label('Comissão Ganha')
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
                
                Tables\Columns\TextColumn::make('sponsor.name')
                    ->label('Patrocinador')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Indicado')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\BadgeColumn::make('level')
                    ->label('Nível')
                    ->sortable()
                    ->colors([
                        'success' => 1,
                        'warning' => fn ($state) => $state >= 2 && $state <= 5,
                        'danger' => fn ($state) => $state > 5,
                    ]),
                
                Tables\Columns\TextColumn::make('commission_earned')
                    ->label('Comissão Total')
                    ->money('USD')
                    ->sortable()
                    ->color('success'),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('level')
                    ->label('Nível')
                    ->options([
                        1 => 'Nível 1',
                        2 => 'Nível 2',
                        3 => 'Nível 3',
                        4 => 'Nível 4',
                        5 => 'Nível 5',
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
            'index' => Pages\ListReferrals::route('/'),
            'create' => Pages\CreateReferral::route('/create'),
            'view' => Pages\ViewReferral::route('/{record}'),
            'edit' => Pages\EditReferral::route('/{record}/edit'),
        ];
    }
}