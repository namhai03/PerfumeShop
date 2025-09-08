<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductVariant;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Danh sách base sản phẩm (brand + tên + nhóm hương + giới tính + nồng độ)
        $base = [
            ['Dior','Sauvage','Woody','Nam','EDT'],
            ['Chanel','No5','Floral','Nữ','EDP'],
            ['Jo Malone','Lime Basil & Mandarin','Fruity','Unisex','EDC'],
            ['Versace','Eros','Oriental','Nam','EDT'],
            ['Gucci','Bloom','Floral','Nữ','EDP'],
            ['Tom Ford','Oud Wood','Woody','Unisex','Parfum'],
            ['YSL','Libre','Floral','Nữ','EDP'],
            ['Bleu de Chanel','Bleu','Woody','Nam','EDP'],
            ['Lancome','La Vie Est Belle','Fruity','Nữ','EDP'],
            ['Calvin Klein','CK One','Fruity','Unisex','EDT'],
            ['Montblanc','Explorer','Woody','Nam','EDP'],
            ['Hermes','Terre d’Hermes','Woody','Nam','EDT'],
            ['Burberry','Her','Fruity','Nữ','EDP'],
            ['Giorgio Armani','Si','Floral','Nữ','EDP'],
            ['Paco Rabanne','1 Million','Oriental','Nam','EDT'],
            ['Creed','Aventus','Woody','Nam','Parfum'],
            ['Givenchy','L’Interdit','Floral','Nữ','EDP'],
            ['Narciso Rodriguez','For Her','Floral','Nữ','EDT'],
            ['Maison Margiela','Jazz Club','Oriental','Unisex','EDT'],
            ['Valentino','Donna Born In Roma','Floral','Nữ','EDP'],
        ];

        $volumes = [10, 30, 50, 100];

        foreach ($base as $idx => $b) {
            [$brand, $short, $family, $gender, $conc] = $b;

            $name = ($gender === 'Nam' ? 'Nước hoa nam ' : ($gender === 'Nữ' ? 'Nước hoa nữ ' : 'Nước hoa unisex ')) . $brand . ' ' . $short;
            $skuBase = strtoupper(preg_replace('/[^A-Z0-9]+/i', '-', substr($brand,0,6) . '-' . substr($short,0,10))) . '-' . str_pad((string)($idx+1), 3, '0', STR_PAD_LEFT);

            $import = 700000 + ($idx * 20000);
            $sell = $import + 500000 + ($idx * 10000);
            $stock = 5 + ($idx % 12);

            $productData = [
                'name' => $name,
                'description' => 'Mẫu demo cho cửa hàng. Hương ' . strtolower($family) . ' phù hợp ' . strtolower($gender) . '.',
                'import_price' => $import,
                'selling_price' => $sell,
                'category' => null, // dùng bảng pivot category_product, cột này để trống
                'brand' => $brand,
                'sku' => $skuBase,
                'stock' => $stock,
                'volume' => '100ml',
                'concentration' => $conc,
                'fragrance_family' => $family,
                'top_notes' => null,
                'heart_notes' => null,
                'base_notes' => null,
                'gender' => $gender,
                'style' => null,
                'season' => null,
                'origin' => 'Pháp',
                'import_date' => now()->subDays(rand(10,60))->format('Y-m-d'),
                'is_active' => true,
                'sales_channel' => rand(0,1) ? 'Online' : 'Offline',
                'tags' => 'demo,' . strtolower($family),
                'product_type' => 'Nước hoa',
                'product_form' => 'Lỏng',
                'expiry_date' => now()->addYears(3)->format('Y-m-d'),
                'branch_price' => json_encode(['HN'=>$sell,'HCM'=>$sell+20000]),
                'customer_group_price' => json_encode(['VIP'=>$sell-50000,'Thường'=>$sell]),
                'created_date' => now()->subDays(rand(30,120))->format('Y-m-d'),
                'low_stock_threshold' => 5,
            ];

            $product = Product::updateOrCreate(['sku' => $productData['sku']], $productData);

            foreach ($volumes as $v) {
                $variantSku = $skuBase . '-' . $v . 'ml';
                ProductVariant::updateOrCreate(
                    ['sku' => $variantSku],
                    [
                        'product_id' => $product->id,
                        'volume_ml' => $v,
                        'import_price' => $import + ($v * 800),
                        'selling_price' => $sell + ($v * 1200),
                        'stock' => max(0, $stock - (int)($v/30) + ($idx % 3)),
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
