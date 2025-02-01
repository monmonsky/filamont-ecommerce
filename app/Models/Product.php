<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 
        'slug', 
        'sku',
        'stock',
        'description', 
        'price', 
        'category_id',
        'is_new',
        'is_featured',
        'is_enabled',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function productVariants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public static function generateUniqueSlug(string $name): string
    {
        // Generate a slug from the name using Laravel's Str::slug helper.
        $slug = Str::slug($name);

        // Store the original slug for use when appending numbers.
        $originalSlug = $slug;

        // Initialize a counter to add as a suffix if needed.
        $counter = 1;

        // Check the database to see if the slug already exists.
        while (self::where('slug', $slug)->exists()) {
            // If the slug exists, append the counter to the original slug.
            $slug = $originalSlug . '-' . $counter;

            // Increment the counter and continue checking until we have a unique slug.
            $counter++;
        }

        // Return the unique slug.
        return $slug;
    }

    public static function generateSKU(string $categoryName, string $productName): string
    {
        // Ambil huruf pertama dari kategori.
        $categoryPart = strtoupper(substr(explode(' ', trim($categoryName))[0], 0, 1));

        // Ambil huruf depan setiap kata dari nama produk.
        $productPart = collect(explode(' ', $productName))
            ->map(fn($word) => strtoupper(substr($word, 0, 1)))
            ->implode('');

        // Bentuk SKU dasar tanpa angka (contoh: 'PBB').
        $baseSKU = $categoryPart . $productPart;

        // Cari SKU terbesar di database untuk produk dengan format dasar yang sama.
        $lastSKU = self::where('sku', 'like', "$baseSKU-%") // Cari SKU dengan pola yang sama
            ->orderBy('sku', 'desc') // Urutkan SKU dari yang terbesar
            ->value('sku'); // Ambil nilai SKU terakhir

        // Jika ada SKU terakhir, ambil angka akhir dari SKU (contoh: '0001', '0002').
        if ($lastSKU) {
            // Ambil angka dari format SKU terakhir (contoh: '0001' dari 'PBB-0001').
            preg_match('/\d+$/', $lastSKU, $matches);
            $number = isset($matches[0]) ? (int) $matches[0] + 1 : 1;
        } else {
            // Jika tidak ada SKU sebelumnya, mulai dari angka 1.
            $number = 1;
        }

        // Format angka menjadi 4 digit (contoh: 1 -> '0001').
        $formattedNumber = str_pad($number, 4, '0', STR_PAD_LEFT);

        // Gabungkan SKU dasar dengan angka untuk membentuk SKU akhir.
        return "{$baseSKU}-{$formattedNumber}";
    }


}
