<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReferralCommissionResource\Pages;
use App\Models\ReferralCommission;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReferralCommissionResource extends Resource
{
    protected static ?string $model = ReferralCommission::class;

    protected static ?string $navigationIcon = 'heroicon-o-gift';
    
    protected static ?string $navigationGroup = 'Rede de Indicação';
    
    protected static ?int $navigationSort = 2;
    
    protected static ?string $modelLabel = 'Comissão';
    
    protected static ?string $pluralModelLabel = 'Comissões';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('Recebedor (Patrocinador)')
                    ->relationship('receiver', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                
                Forms\Components\Select::make('source_user_id')
                    ->label('Fonte (Indicado)')
                    ->relationship('sourceUser', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                
                Forms\Components\Select::make('type')
                    ->label('Tipo')
                    ->options([
                        'investment' => 'Investimento',
                        'profit' => 'Lucro',
                        'deposit' => 'Depósito',
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
                
                Forms\Components\TextInput::make('source_id')
                    ->label('ID da Fonte')
                    ->numeric()
                    ->helperText('ID do registro que gerou esta comissão'),
                
                Forms\Components\TextInput::make('source_type')
                    ->label('Tipo da Fonte')
                    ->helperText('Model que gerou esta comissão (ex: App\\Models\\Investment)'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('receiver.name')
                    ->label('Recebedor')
                    ->searchable()
                    ->sortable()
                    ->description(fn (ReferralCommission $record): string => 
                        "Recebe de: {$record->sourceUser->name}"
                    ),
                
                Tables\Columns\BadgeColumn::make('type')
                    ->label('Tipo')
                    ->colors([
                        'primary' => 'investment',
                        'success' => 'profit',
                        'info' => 'deposit',
                    ])
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'investment' => 'Investimento',
                        'profit' => 'Lucro',
                        'deposit' => 'Depósito',
                        default => $state,
                    }),
                
                Tables\Columns\BadgeColumn::make('level')
                    ->label('Nível')
                    ->sortable()
                    ->colors([
                        'success' => 1,
                        'warning' => fn ($state) => $state >= 2 && $state <= 5,
                        'danger' => fn ($state) => $state > 5,
                    ]),
                
                Tables\Columns\TextColumn::make('amount')
                    ->label('Valor')
                    ->money('USD')
                    ->sortable()
                    ->color('success'),
                
                Tables\Columns\TextColumn::make('source_type')
                    ->label('Fonte')
                    ->formatStateUsing(fn (string $state): string => 
                        class_basename($state)
                    )
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipo')
                    ->options([
                        'investment' => 'Investimento',
                        'profit' => 'Lucro',
                        'deposit' => 'Depósito',
                    ]),
                
                Tables\Filters\SelectFilter::make('level')
                    ->label('Nível')
                    ->options([
                        1 => 'Nível 1',
                        2 => 'Nível 2',
                        3 => 'Nível 3',
                        4 => 'Nível 4',
                        5 => 'Nível 5',
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
            'index' => Pages\ListReferralCommissions::route('/'),
            'create' => Pages\CreateReferralCommission::route('/create'),
            'view' => Pages\ViewReferralCommission::route('/{record}'),
            'edit' => Pages\EditReferralCommission::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $today = static::getModel()::whereDate('created_at', today())->sum('amount');
        return $today > 0 ? '$' . number_format($today, 2) : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}