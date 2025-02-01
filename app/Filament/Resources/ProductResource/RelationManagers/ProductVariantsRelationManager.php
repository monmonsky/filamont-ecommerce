<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\ProductVariant;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductVariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'productVariants';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                        
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                ->description(fn ($record) => $record->sku),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Product Variant')
                    ->createAnother(false)
                    ->modal()
                    ->modalWidth('sm') 
                    ->modalHeading('Add Variant')
                    ->form([
                        Group::make(function () {
                            $attributes = Attribute::all();
                            return $attributes->map(function ($attribute) {
                                return Select::make('attribute_' . $attribute->id)
                                    ->label($attribute->name)
                                    ->options(
                                        AttributeValue::where('attribute_id', $attribute->id)
                                            ->pluck('value', 'id')
                                    )
                                    ->placeholder('Select ' . $attribute->name)
                                    ->searchable()
                                    ->preload()
                                    ->required();
                            })->toArray();
                        }),
                    ])
                    ->action(function (array $data) {
                        $product = $this->ownerRecord; // Produk Induk
                    
                        $attributeValueIds = []; // Simpan ID nilai atribut dari input user
                        $attributes = Attribute::all();
                    
                        // Ambil semua ID attribute_value yang dipilih
                        foreach ($attributes as $attribute) {
                            $fieldKey = 'attribute_' . $attribute->id;
                            if (array_key_exists($fieldKey, $data)) {
                                $attributeValue = AttributeValue::find($data[$fieldKey]);
                                $attributeValueIds[] = $attributeValue->id;
                            }
                        }
                    
                        // VALIDASI: Periksa apakah kombinasi sudah ada
                        $conflictingVariants = DB::table('product_variant_attribute_value')
                        ->select(DB::raw('product_variant_id, COUNT(*) as total_count'))
                        ->whereIn('attribute_value_id', $attributeValueIds) // Filter atribut input user
                        ->groupBy('product_variant_id') // Kelompokkan berdasarkan ID varian
                        ->havingRaw('COUNT(*) = ?', [count($attributeValueIds)]) // Jumlah atribut harus pas
                        ->whereExists(function ($query) use ($product) {
                            $query->select(DB::raw(1)) // Cukup mengecek apakah ID terkait eksis
                                ->from('product_variants')
                                ->where('product_variants.id', DB::raw('product_variant_attribute_value.product_variant_id'))
                                ->where('product_variants.product_id', $product->id); // Pastikan produk persegi
                        })
                        ->exists();

                        if ($conflictingVariants) {
                            Notification::make()
                                ->title('Variant saved failed')
                                ->body('The variant has been taken.')
                                ->danger()
                                ->send();
                    
                            return; // Hentikan eksekusi jika validasi gagal
                        }
                    
                        // GENERATE SKU, SIMPAN VARIAN
                        $variantName = $product->name;
                        $variantSuffix = '';
                    
                        foreach ($attributes as $attribute) {
                            $fieldKey = 'attribute_' . $attribute->id;
                            if (array_key_exists($fieldKey, $data)) {
                                $attributeValue = AttributeValue::find($data[$fieldKey]);
                                $attributeCode = $attribute->code ?: substr(strtolower($attribute->name), 0, 1);
                                $valueCode = $attributeValue->code ?: substr(strtolower($attributeValue->value), 0, 1);
                    
                                // Tambahkan ke nama dan suffix SKU
                                $variantName .= ' - ' . $attribute->name . ' ' . $attributeValue->value;
                                $variantSuffix .= $attributeCode . $valueCode;
                            }
                        }
                    
                        $variantSKU = "{$product->sku}-{$variantSuffix}";
                    
                        $variant = $product->productVariants()->create([
                            'name' => $variantName,
                            'sku' => strtoupper($variantSKU),
                            'product_id' => $product->id,
                            'price' => 0,
                            'stock' => 0,
                            'image_path' => null,
                            'is_enabled' => false,
                        ]);
                    
                        // Simpan ke tabel pivot
                        foreach ($attributeValueIds as $attributeValueId) {
                            \App\Models\ProductVariantAttributeValue::create([
                                'product_variant_id' => $variant->id,
                                'attribute_value_id' => $attributeValueId,
                            ]);
                        }
                    
                        Notification::make()
                            ->title('Variant Added')
                            ->body('The variant has been successfully added.')
                            ->success()
                            ->send();
                    }),

                    
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Edit')
                    ->slideOver()
                    ->modalWidth('sm'),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
