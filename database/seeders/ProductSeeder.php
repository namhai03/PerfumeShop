<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        if (Product::count() > 0) {
            return;
        }

        $brands = ['Dior','Chanel','Gucci','Versace','YSL','Tom Ford','Calvin Klein'];
        $categories = ['Nước hoa nam','Nước hoa nữ','Unisex'];
        $origins = ['Pháp','Ý','Mỹ','Tây Ban Nha','Anh'];
        $volumes = ['30ml','50ml','75ml','100ml'];

        for ($i = 1; $i <= 20; $i++) {
            $name = 'Perfume #' . $i;
            $brand = $brands[array_rand($brands)];
            $category = $categories[array_rand($categories)];
            $origin = $origins[array_rand($origins)];
            $volume = $volumes[array_rand($volumes)];
            $importPrice = rand(200, 600) * 1000; // 200k - 600k
            $sellingPrice = $importPrice + rand(150, 500) * 1000; // markup

            Product::create([
                'name' => $name,
                'description' => 'Sản phẩm demo cho seed dữ liệu.',
                'import_price' => $importPrice,
                'selling_price' => $sellingPrice,
                'category' => $category,
                'brand' => $brand,
                'sku' => 'SKU' . str_pad((string)$i, 5, '0', STR_PAD_LEFT),
                'stock' => rand(10, 100),
                'image' => null,
                'volume' => $volume,
                'concentration' => rand(0,1) ? 'EDT' : 'EDP',
                'origin' => $origin,
                'import_date' => now()->subDays(rand(1, 180))->toDateString(),
                'is_active' => true,
            ]);
        }
    }
}


