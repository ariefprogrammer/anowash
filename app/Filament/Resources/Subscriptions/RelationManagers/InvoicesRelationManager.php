<?php

namespace App\Filament\Resources\Subscriptions\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InvoicesRelationManager extends RelationManager
{
    protected static string $relationship = 'invoices';

    protected static ?string $title = 'Riwayat Invoice';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('period_start')
                    ->label('Periode')
                    ->formatStateUsing(fn ($record) => $record->period_start->format('d M Y').' - '.$record->period_end->format('d M Y')),

                TextColumn::make('outlet_count')->label('Outlet'),

                TextColumn::make('amount')->label('Nominal')->money('IDR'),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'unpaid' => 'warning',
                        'overdue' => 'danger',
                        'cancelled' => 'gray',
                    }),

                TextColumn::make('paid_at')
                    ->label('Dibayar')
                    ->dateTime('d M Y')
                    ->placeholder('-'),
            ]);
    }
}