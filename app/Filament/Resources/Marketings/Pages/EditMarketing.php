<?php

namespace App\Filament\Resources\Marketings\Pages;

use App\Filament\Resources\Marketings\MarketingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMarketing extends EditRecord
{
    protected static string $resource = MarketingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
