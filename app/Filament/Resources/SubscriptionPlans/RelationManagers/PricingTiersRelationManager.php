<?php

namespace App\Filament\Resources\SubscriptionPlans\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PricingTiersRelationManager extends RelationManager
{
    protected static string $relationship = 'pricingTiers';

    protected static ?string $title = 'Pricing Tiers';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('min_outlet')
                ->label('Min. Outlet')
                ->numeric()
                ->required(),

            TextInput::make('max_outlet')
                ->label('Max. Outlet (kosongkan jika tak terbatas)')
                ->numeric(),

            TextInput::make('price')
                ->label('Harga (Rp)')
                ->numeric()
                ->prefix('Rp')
                ->required(),

            Toggle::make('is_active')
                ->label('Aktif')
                ->default(true),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('min_outlet')->label('Min. Outlet'),
                TextColumn::make('max_outlet')->label('Max. Outlet')->placeholder('Tak terbatas'),
                TextColumn::make('price')->label('Harga')->money('IDR'),
                IconColumn::make('is_active')->label('Aktif')->boolean(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}