<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class SkuHelper
{
    /**
     * Generate SKU berdasarkan kategori dan nama produk.
     *
     * @param string $category
     * @param string $productName
     * @return string
     */
    public static function generate(string $category, string $productName): string
    {
        // Ambil prefix kategori (huruf pertama)
        $categoryPrefix = Str::upper(substr($category, 0, 1));

        // Ambil prefix nama produk (2 huruf pertama, tanpa spasi)
        $productNamePrefix = Str::upper(substr(str_replace(' ', '', $productName), 0, 2));

        // Generate angka acak 3 digit
        $randomNumber = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);

        // Gabungkan untuk membuat SKU
        return $categoryPrefix . $productNamePrefix . '-' . $randomNumber;
    }
}