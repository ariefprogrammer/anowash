<?php

namespace App\Filament\Resources\MarketingCommissions\Pages;

use App\Filament\Resources\MarketingCommissions\MarketingCommissionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMarketingCommission extends EditRecord
{
    protected static string $resource = MarketingCommissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
