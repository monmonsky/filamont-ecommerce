<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Back') 
                ->color('secondary') 
                ->url(ProductResource::getUrl('index')) 
                ->outlined(),

            Action::make('save')
                ->label('Save Product') 
                ->color('primary') 
                ->outlined()
                ->action(fn () => $this->saveChanges()), 

             
        ];
    }

    protected function getFormActions(): array
    {
        return []; // Mengembalikan array kosong untuk menghapus tombol di footer
    }

    protected function getActions(): array
    {
        // Mengembalikan array kosong untuk menghapus tombol default pada footer
        return [];
    }

    public function saveChanges(): void
    {
        $this->save(); // Simpan perubahan ke database

        $this->getSavedNotification();
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Product updated')
            ->body('The product has been updated.');
    }
}
