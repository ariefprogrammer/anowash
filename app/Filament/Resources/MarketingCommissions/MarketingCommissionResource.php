<?php

namespace App\Filament\Resources\MarketingCommissions;

use App\Filament\Resources\MarketingCommissions\Pages\ListMarketingCommissions;
use App\Models\MarketingCommission;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MarketingCommissionResource extends Resource
{
    protected static ?string $model = MarketingCommission::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?string $navigationLabel = 'Komisi Marketing';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('marketing.name')
                    ->label('Marketing')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('owner.business_name')
                    ->label('Owner')
                    ->searchable(),

                TextColumn::make('invoice.period_start')
                    ->label('Periode Invoice')
                    ->formatStateUsing(fn ($record) => $record->invoice->period_start->format('d M').' - '.$record->invoice->period_end->format('d M Y')),

                TextColumn::make('amount')
                    ->label('Komisi (20%)')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'paid' ? 'success' : 'warning'),

                TextColumn::make('paid_at')
                    ->label('Dibayar')
                    ->dateTime('d M Y')
                    ->placeholder('-'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'unpaid' => 'Belum Dibayar',
                        'paid' => 'Sudah Dibayar',
                    ]),
                SelectFilter::make('marketing_id')
                    ->label('Marketing')
                    ->relationship('marketing', 'name'),
            ])
            ->recordActions([
                Action::make('tandaiDibayar')
                    ->label('Tandai Dibayar')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status === 'unpaid')
                    ->action(function ($record) {
                        $record->update(['status' => 'paid', 'paid_at' => now()]);

                        Notification::make()
                            ->title('Komisi ditandai sudah dibayar')
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMarketingCommissions::route('/'),
        ];
    }
}