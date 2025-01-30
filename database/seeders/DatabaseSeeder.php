<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
        ]);

        $category = Category::create(['name' => 'Pakaian']);

        // Buat produk
        $product = Product::create([
            'name' => 'Kaos Polos',
            'slug' => 'kaos-polos',
            'sku' => 'kp111',
            'stock' => 100,
            'description' => 'Kaos polos dengan bahan katun',
            'price' => 100000,
            'category_id' => 1,
            'is_new' => false,
            'is_featured' => false,
            'is_enabled' => true,
        ]);

        // Buat atribut "Size" dan "Color"
        $sizeAttribute = Attribute::create(['name' => 'Size']);
        $colorAttribute = Attribute::create(['name' => 'Color']);

        // Buat nilai atribut
        $sizeM = AttributeValue::create(['attribute_id' => $sizeAttribute->id, 'value' => 'M']);
        $colorRed = AttributeValue::create(['attribute_id' => $colorAttribute->id, 'value' => 'Merah']);

        // Buat variant produk
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'attribute_value_id' => $sizeM->id, // Menggunakan ID nilai atribut "M"
            'name' => 'Variant Default',
            'price' => 100000,
            'sku' => 'KS-KPDHBK-1',
            'stock' => 50,
            'image_path' => 'images/kaos-polos-merah.jpg',
            'is_enabled' => true,
        ]);
    }
}
