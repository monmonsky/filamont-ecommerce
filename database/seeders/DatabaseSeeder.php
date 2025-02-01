<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\ProductVariant;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Buat admin
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
        ]);

        // Buat kategori
        $category = Category::create(['name' => 'Pakaian']);

        // Buat produk
        $product = Product::create([
            'name' => 'Kaos Polos',
            'slug' => 'kaos-polos',
            'sku' => 'kp111',
            'stock' => 100,
            'description' => 'Kaos polos dengan bahan katun',
            'price' => 100000,
            'category_id' => $category->id, // Gunakan ID kategori secara dinamis
            'is_new' => false,
            'is_featured' => false,
            'is_enabled' => true,
        ]);

        // Buat atribut "Size" dan nilai-nilainya
        $sizeAttribute = Attribute::create(['name' => 'Size']);
        $sizeM = AttributeValue::create(['attribute_id' => $sizeAttribute->id, 'value' => 'M']);
        $sizeL = AttributeValue::create(['attribute_id' => $sizeAttribute->id, 'value' => 'L']);

        // Buat atribut "Color" dan nilainya
        $colorAttribute = Attribute::create(['name' => 'Color']);
        $colorRed = AttributeValue::create(['attribute_id' => $colorAttribute->id, 'value' => 'Merah']);
        $colorBlue = AttributeValue::create(['attribute_id' => $colorAttribute->id, 'value' => 'Biru']);

        // Kombinasi untuk varian produk
        $combinations = [
            [$sizeM, $colorRed],  // Kombinasi: Size M + Color Red
            [$sizeL, $colorBlue], // Kombinasi: Size L + Color Blue
        ];

        // Buat varian untuk setiap kombinasi atribut
        foreach ($combinations as $index => $attributes) {
            // Buat nama varian dari kombinasi atribut
            $variantName = "Kaos Polos Size {$attributes[0]->value}, Color {$attributes[1]->value}";

            // Tambahkan varian ke database
            $variant = ProductVariant::create([
                'product_id' => $product->id,
                'name' => $variantName,
                'price' => 100000 + ($index * 10000), // Tambahkan 10.000 ke harga untuk setiap varian
                'sku' => 'KP-' . strtoupper($attributes[0]->value) . '-' . strtoupper($attributes[1]->value),
                'stock' => 50,
                'image_path' => "images/kaos-polos-{$attributes[1]->value}.jpg", // Gambar sesuai warna
                'is_enabled' => true,
            ]);

            // Hubungkan atribut ke varian melalui pivot table
            $variant->attributeValues()->attach([
                $attributes[0]->id, // Size
                $attributes[1]->id, // Color
            ]);
        }
    }
}
