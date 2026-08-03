<?php

namespace App\Filament\Resources\Owners;

use App\Filament\Resources\Owners\Pages\CreateOwner;
use App\Filament\Resources\Owners\Pages\EditOwner;
use App\Filament\Resources\Owners\Pages\ListOwners;
use App\Models\Owner;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Support\Icons\Heroicon;

class OwnerResource extends Resource
{
    protected static ?string $model = Owner::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingStorefront;

    protected static ?string $navigationLabel = 'Owners';

    protected static ?string $recordTitleAttribute = 'business_name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('referral_code_input')
                ->label('Kode Referral Marketing (opsional)')
                ->maxLength(20)
                ->helperText('Isi jika Owner ini didapat lewat marketing tertentu.')
                ->dehydrated(fn (string $operation): bool => $operation === 'create')
                ->hiddenOn('edit'),

            TextInput::make('referredByMarketing.name')
                ->label('Direferensikan oleh Marketing')
                ->disabled()
                ->dehydrated(false)
                ->visibleOn('edit'),

            TextInput::make('business_name')
                ->label('Nama Usaha')
                ->required()
                ->maxLength(150),

            TextInput::make('owner_name')
                ->label('Nama Pemilik')
                ->required()
                ->maxLength(150),

            TextInput::make('email')
                ->email()
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(150),

            TextInput::make('password')
                ->label('Password Login')
                ->password()
                ->revealable()
                ->required(fn (string $operation): bool => $operation === 'create')
                ->minLength(8)
                ->maxLength(255)
                ->hiddenOn('edit')
                ->dehydrated(fn (string $operation): bool => $operation === 'create')
                ->helperText('Password ini akan dipakai untuk akun login Owner. Tidak bisa diubah dari sini setelah dibuat.')
                ->columnSpanFull(),

            TextInput::make('phone')
                ->label('No. Telepon')
                ->tel()
                ->maxLength(30),

            Textarea::make('address')
                ->label('Alamat')
                ->rows(3)
                ->columnSpanFull(),

            FileUpload::make('logo_path')
                ->label('Logo')
                ->image()
                ->directory('owner-logos'),

            Select::make('status')
                ->options([
                    'active' => 'Active',
                    'inactive' => 'Inactive',
                    'suspended' => 'Suspended',
                ])
                ->default('active')
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('business_name')
                    ->label('Nama Usaha')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('owner_name')
                    ->label('Pemilik')
                    ->searchable(),

                TextColumn::make('email')
                    ->searchable(),

                TextColumn::make('phone')
                    ->label('Telepon'),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'gray',
                        'suspended' => 'danger',
                    }),

                TextColumn::make('outlets_count')
                    ->label('Jumlah Outlet')
                    ->counts('outlets'),

                TextColumn::make('created_at')
                    ->label('Terdaftar')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'suspended' => 'Suspended',
                    ]),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOwners::route('/'),
            'create' => CreateOwner::route('/create'),
            'edit' => EditOwner::route('/{record}/edit'),
        ];
    }
}