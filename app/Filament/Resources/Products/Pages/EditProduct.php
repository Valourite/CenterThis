<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Models\Product;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->disabled(fn (Product $record): bool => ! $record->canBeDeleted())
                ->tooltip(fn (Product $record): ?string => $record->canBeDeleted() ? null : $record->deleteBlockedMessage()),
            ForceDeleteAction::make()
                ->disabled(fn (Product $record): bool => ! $record->canBeDeleted())
                ->tooltip(fn (Product $record): ?string => $record->canBeDeleted() ? null : $record->deleteBlockedMessage()),
            RestoreAction::make(),
        ];
    }
}
