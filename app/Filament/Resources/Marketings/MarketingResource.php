<?php

namespace App\Filament\Resources\Marketings;

use App\Filament\Resources\Marketings\Pages\CreateMarketing;
use App\Filament\Resources\Marketings\Pages\EditMarketing;
use App\Filament\Resources\Marketings\Pages\ListMarketings;
use App\Models\Marketing;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MarketingResource extends Resource
{
    protected static ?string $model = Marketing::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;

    protected static ?string $navigationLabel = 'Marketing';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Nama Marketing')
                ->required()
                ->maxLength(150),

            TextInput::make('email')
                ->email()
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(150),

            TextInput::make('phone')
                ->label('No. Telepon')
                ->tel()
                ->maxLength(30),

            TextInput::make('password')
                ->label('Password Login')
                ->password()
                ->revealable()
                ->required(fn (string $operation): bool => $operation === 'create')
                ->minLength(8)
                ->hiddenOn('edit')
                ->dehydrated(fn (string $operation): bool => $operation === 'create')
                ->helperText('Password untuk akun login marketing ini.'),

            Toggle::make('is_active')
                ->label('Aktif')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->searchable(),

                TextColumn::make('referral_code')
                    ->label('Kode Referral')
                    ->badge()
                    ->color('info')
                    ->copyable(),

                TextColumn::make('owners_count')
                    ->label('Owner Didapat')
                    ->counts('owners'),

                TextColumn::make('commissions_sum_amount')
                    ->label('Total Komisi')
                    ->sum('commissions', 'amount')
                    ->money('IDR'),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMarketings::route('/'),
            'create' => CreateMarketing::route('/create'),
            'edit' => EditMarketing::route('/{record}/edit'),
        ];
    }
}