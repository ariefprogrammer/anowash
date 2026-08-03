<?php

namespace App\Filament\Resources\SubscriptionInvoices;

use App\Filament\Resources\SubscriptionInvoices\Pages\CreateSubscriptionInvoice;
use App\Filament\Resources\SubscriptionInvoices\Pages\EditSubscriptionInvoice;
use App\Filament\Resources\SubscriptionInvoices\Pages\ListSubscriptionInvoices;
use App\Models\Owner;
use App\Models\Outlet;
use App\Models\Subscription;
use App\Models\SubscriptionInvoice;
use App\Models\SubscriptionPricingTier;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;

class SubscriptionInvoiceResource extends Resource
{
    protected static ?string $model = SubscriptionInvoice::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentCurrencyDollar;

    protected static ?string $navigationLabel = 'Subscription Invoices';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('owner_id')
                ->label('Owner')
                ->options(fn () => Owner::query()->pluck('business_name', 'id'))
                ->searchable()
                ->required()
                ->live()
                ->afterStateUpdated(function (callable $set, $state) {
                    $set('subscription_id', null);
                    $set('period_start', null);
                    $set('period_end', null);
                    $set('outlet_count', null);
                    $set('amount', null);

                    if (! $state) {
                        return;
                    }

                    $subscription = Subscription::where('owner_id', $state)
                        ->whereIn('status', ['trial', 'active'])
                        ->latest('start_date')
                        ->first();

                    if ($subscription) {
                        $set('subscription_id', $subscription->id);
                        self::fillFromSubscription($subscription, $set);
                    }
                }),

            Select::make('subscription_id')
                ->label('Subscription')
                ->options(
                    fn (callable $get) => Subscription::query()
                        ->where('owner_id', $get('owner_id'))
                        ->with('plan')
                        ->get()
                        ->mapWithKeys(fn ($s) => [
                            $s->id => ($s->plan->name ?? '-').' — '.ucfirst($s->status).' (berakhir '.$s->end_date->format('d M Y').')',
                        ])
                )
                ->required()
                ->live()
                ->afterStateUpdated(function (callable $set, $state) {
                    if (! $state) {
                        return;
                    }

                    $subscription = Subscription::find($state);

                    if ($subscription) {
                        self::fillFromSubscription($subscription, $set);
                    }
                }),

            DatePicker::make('period_start')->label('Awal Periode')->required(),
            DatePicker::make('period_end')->label('Akhir Periode')->required(),

            TextInput::make('outlet_count')
                ->label('Jumlah Outlet')
                ->numeric()
                ->required()
                ->helperText('Otomatis terisi dari jumlah outlet aktif Owner, bisa diubah manual jika perlu.'),

            TextInput::make('amount')
                ->label('Nominal (Rp)')
                ->numeric()
                ->prefix('Rp')
                ->required()
                ->helperText('Otomatis terisi dari Pricing Tier plan yang dipilih, bisa diubah manual jika perlu.'),

            Select::make('status')
                ->options([
                    'unpaid' => 'Unpaid',
                    'paid' => 'Paid',
                    'overdue' => 'Overdue',
                    'cancelled' => 'Cancelled',
                ])
                ->default('unpaid')
                ->required(),

            FileUpload::make('payment_proof_path')
                ->label('Bukti Bayar')
                ->image()
                ->directory('payment-proofs'),
        ]);
    }

    protected static function fillFromSubscription(Subscription $subscription, callable $set): void
    {
        $periodStart = Carbon::parse($subscription->end_date);
        $periodEnd = $periodStart->copy()->addMonth();

        $outletCount = Outlet::where('owner_id', $subscription->owner_id)->count();

        if ($outletCount === 0) {
            $outletCount = $subscription->current_outlet_count;
        }

        $tier = SubscriptionPricingTier::where('plan_id', $subscription->plan_id)
            ->where('is_active', true)
            ->where('min_outlet', '<=', $outletCount)
            ->where(function ($query) use ($outletCount) {
                $query->whereNull('max_outlet')
                    ->orWhere('max_outlet', '>=', $outletCount);
            })
            ->orderByDesc('min_outlet')
            ->first();

        $set('period_start', $periodStart->format('Y-m-d'));
        $set('period_end', $periodEnd->format('Y-m-d'));
        $set('outlet_count', $outletCount);

        if ($tier) {
            $set('amount', $tier->price);
        }
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('owner.business_name')
                    ->label('Owner')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('period_start')
                    ->label('Periode')
                    ->formatStateUsing(fn ($record) => $record->period_start->format('d M Y').' - '.$record->period_end->format('d M Y')),

                TextColumn::make('outlet_count')
                    ->label('Outlet'),

                TextColumn::make('amount')
                    ->label('Nominal')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'unpaid' => 'warning',
                        'overdue' => 'danger',
                        'cancelled' => 'gray',
                    }),

                TextColumn::make('verifiedBy.name')
                    ->label('Diverifikasi Oleh')
                    ->placeholder('-'),

                TextColumn::make('paid_at')
                    ->label('Dibayar')
                    ->dateTime('d M Y')
                    ->placeholder('-'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'unpaid' => 'Unpaid',
                        'paid' => 'Paid',
                        'overdue' => 'Overdue',
                        'cancelled' => 'Cancelled',
                    ]),
            ])
            ->recordActions([
                Action::make('lihatBukti')
                    ->label('Lihat Bukti')
                    ->icon(Heroicon::OutlinedPhoto)
                    ->modalHeading('Bukti Pembayaran')
                    ->modalContent(fn ($record) => view('filament.invoice-proof', ['path' => $record->payment_proof_path]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->visible(fn ($record) => filled($record->payment_proof_path)),

                Action::make('verifikasi')
                    ->label('Verifikasi')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status === 'unpaid')
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'paid',
                            'verified_by' => auth()->id(),
                            'verified_at' => now(),
                            'paid_at' => now(),
                        ]);

                        $owner = $record->owner;

                        if ($owner->referred_by_marketing_id) {
                            \App\Models\MarketingCommission::updateOrCreate(
                                ['subscription_invoice_id' => $record->id],
                                [
                                    'marketing_id' => $owner->referred_by_marketing_id,
                                    'owner_id' => $owner->id,
                                    'amount' => $record->amount * 0.2,
                                    'status' => 'unpaid',
                                ]
                            );
                        }

                        Notification::make()
                            ->title('Invoice diverifikasi & ditandai lunas')
                            ->success()
                            ->send();
                    }),

                Action::make('tolak')
                    ->label('Tolak')
                    ->icon(Heroicon::OutlinedXCircle)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status === 'unpaid')
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'overdue',
                            'verified_by' => auth()->id(),
                            'verified_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Bukti bayar ditolak')
                            ->warning()
                            ->send();
                    }),

                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSubscriptionInvoices::route('/'),
            'create' => CreateSubscriptionInvoice::route('/create'),
            'edit' => EditSubscriptionInvoice::route('/{record}/edit'),
        ];
    }
}