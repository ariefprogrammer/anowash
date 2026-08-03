<?php

namespace App\Filament\Resources\Subscriptions;

use App\Filament\Resources\Subscriptions\Pages\CreateSubscription;
use App\Filament\Resources\Subscriptions\Pages\EditSubscription;
use App\Filament\Resources\Subscriptions\Pages\ListSubscriptions;
use App\Filament\Resources\Subscriptions\RelationManagers\InvoicesRelationManager;
use App\Models\Owner;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionPricingTier;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SubscriptionResource extends Resource
{
    protected static ?string $model = Subscription::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Subscriptions';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('owner_id')
                ->label('Owner')
                ->options(fn () => Owner::query()->pluck('business_name', 'id'))
                ->searchable()
                ->required(),

            Select::make('plan_id')
                ->label('Plan')
                ->options(fn () => SubscriptionPlan::query()->pluck('name', 'id'))
                ->required()
                ->live()
                ->afterStateUpdated(fn (callable $get, callable $set) => self::recalculatePrice($get, $set)),

            Select::make('status')
                ->options([
                    'trial' => 'Trial',
                    'active' => 'Active',
                    'expired' => 'Expired',
                    'cancelled' => 'Cancelled',
                ])
                ->default('trial')
                ->required(),

            DatePicker::make('start_date')->label('Mulai')->required(),
            DatePicker::make('end_date')->label('Berakhir')->required(),

            TextInput::make('current_outlet_count')
                ->label('Jumlah Outlet Saat Ini')
                ->numeric()
                ->default(1)
                ->required()
                ->live(onBlur: true)
                ->afterStateUpdated(fn (callable $get, callable $set) => self::recalculatePrice($get, $set)),

            TextInput::make('current_price')
                ->label('Harga Berjalan (Rp)')
                ->numeric()
                ->prefix('Rp')
                ->required()
                ->helperText('Otomatis terisi dari Pricing Tier plan yang dipilih, bisa diubah manual jika perlu.'),

            Textarea::make('notes')
                ->label('Catatan')
                ->rows(2)
                ->columnSpanFull(),
        ]);
    }

    protected static function recalculatePrice(callable $get, callable $set): void
    {
        $planId = $get('plan_id');
        $outletCount = (int) ($get('current_outlet_count') ?: 1);

        if (! $planId) {
            return;
        }

        $tier = SubscriptionPricingTier::query()
            ->where('plan_id', $planId)
            ->where('is_active', true)
            ->where('min_outlet', '<=', $outletCount)
            ->where(function ($query) use ($outletCount) {
                $query->whereNull('max_outlet')
                    ->orWhere('max_outlet', '>=', $outletCount);
            })
            ->orderByDesc('min_outlet')
            ->first();

        if ($tier) {
            $set('current_price', $tier->price);
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

                TextColumn::make('plan.name')
                    ->label('Plan'),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'trial' => 'info',
                        'active' => 'success',
                        'expired' => 'danger',
                        'cancelled' => 'gray',
                    }),

                TextColumn::make('start_date')
                    ->label('Mulai')
                    ->date('d M Y'),

                TextColumn::make('end_date')
                    ->label('Berakhir')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('current_outlet_count')
                    ->label('Outlet'),

                TextColumn::make('current_price')
                    ->label('Harga')
                    ->money('IDR'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'trial' => 'Trial',
                        'active' => 'Active',
                        'expired' => 'Expired',
                        'cancelled' => 'Cancelled',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            InvoicesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSubscriptions::route('/'),
            'create' => CreateSubscription::route('/create'),
            'edit' => EditSubscription::route('/{record}/edit'),
        ];
    }
}