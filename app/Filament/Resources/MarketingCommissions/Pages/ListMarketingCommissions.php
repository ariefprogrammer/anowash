<?php

namespace App\Filament\Resources\MarketingCommissions\Pages;

use App\Filament\Resources\MarketingCommissions\MarketingCommissionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMarketingCommissions extends ListRecords
{
    protected static string $resource = MarketingCommissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
