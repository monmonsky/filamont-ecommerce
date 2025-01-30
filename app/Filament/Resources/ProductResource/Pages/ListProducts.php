<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Product;
use App\Models\ProductVariant;
use Filament\Actions;
use Filament\Forms\Set;
use Filament\Resources\Pages\ListRecords;
use App\Helpers\SkuGenerator;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected static bool $canCreateAnother = false;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
            ->createAnother(false)
            ->modal()
            ->modalHeading('Create New Product')
            ->form([
                \Filament\Forms\Components\Select::make('category_id')
                    ->relationship(name: 'category', titleAttribute: 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->createOptionForm([
                \Filament\Forms\Components\TextInput::make('name')
                            ->required(),
                    ]),
                \Filament\Forms\Components\TextInput::make('name')
                    ->afterStateUpdated(function (Set $set, $state) {
                        $set('slug', Product::generateUniqueSlug($state));
                    })
                    ->live(onBlur: true)
                    ->required(),
                \Filament\Forms\Components\TextInput::make('slug')
                    ->required()
                    ->readOnly()
                    ->unique(table: Product::class, column: 'slug', ignoreRecord: true),
                \Filament\Forms\Components\TextInput::make('sku')
                    ->required()
                    ->unique(table: Product::class, column: 'sku', ignoreRecord: true),
                \Filament\Forms\Components\TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('Rp'),
            ])
            ->after(function (Product $product) {
                // Membuat atribut "Size" (jika belum ada)
                $attribute = Attribute::firstOrCreate(['name' => 'Size']);
                
                // Membuat value "L" untuk atribut "Size" (jika belum ada)
                $attributeValue = AttributeValue::firstOrCreate([
                    'attribute_id' => $attribute->id,
                    'value' => 'S',
                ]);

                // Membuat product variant default
                ProductVariant::create([
                    'product_id' => $product->id,
                    'attribute_value_id' => $attributeValue->id,
                    'name' => "Variant Default",
                    'sku' => SkuGenerator::generate($product->category, $product->name).'-1',
                    'price' => $product->price,
                    'stock' => 0,
                    'is_enabled' => true, 
                ]);
            })
            ->successRedirectUrl(fn (Product $record): string => ProductResource::getUrl('edit', ['record' => $record]))
        ];
    }
}
