<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name' => 'Nước hoa nam Dior Sauvage',
                'description' => 'Nước hoa nam cao cấp với hương thơm tươi mát, nam tính',
                'import_price' => 1200000,
                'selling_price' => 1800000,
                'category' => 'Nước hoa nam',
                'brand' => 'Dior',
                'sku' => 'DIOR-SAV-001',
                'stock' => 15,
                'volume' => '100ml',
                'concentration' => 'EDT',
                'origin' => 'Pháp',
                'import_date' => '2025-08-15',
                'is_active' => true,
                'sales_channel' => 'Shopee',
                'tags' => 'nam, cao cấp, tươi mát',
                'product_type' => 'Nước hoa',
                'product_form' => 'Lỏng',
                'expiry_date' => '2028-08-15',
                'branch_price' => json_encode([
                    'Hà Nội' => 1800000,
                    'TP.HCM' => 1850000,
                    'Đà Nẵng' => 1820000
                ]),
                'customer_group_price' => json_encode([
                    'VIP' => 1700000,
                    'Thường' => 1800000,
                    'Đại lý' => 1600000
                ]),
                'created_date' => '2025-08-15',
            ],
            [
                'name' => 'Nước hoa nữ Chanel N°5',
                'description' => 'Nước hoa nữ huyền thoại với hương thơm hoa hồng và vani',
                'import_price' => 1500000,
                'selling_price' => 2200000,
                'category' => 'Nước hoa nữ',
                'brand' => 'Chanel',
                'sku' => 'CHANEL-N5-001',
                'stock' => 12,
                'volume' => '50ml',
                'concentration' => 'EDP',
                'origin' => 'Pháp',
                'import_date' => '2025-08-10',
                'is_active' => true,
                'sales_channel' => 'Tiktok Shop',
                'tags' => 'nữ, huyền thoại, hoa hồng',
                'product_type' => 'Nước hoa',
                'product_form' => 'Lỏng',
                'expiry_date' => '2028-08-10',
                'branch_price' => json_encode([
                    'Hà Nội' => 2200000,
                    'TP.HCM' => 2250000,
                    'Đà Nẵng' => 2220000
                ]),
                'customer_group_price' => json_encode([
                    'VIP' => 2100000,
                    'Thường' => 2200000,
                    'Đại lý' => 2000000
                ]),
                'created_date' => '2025-08-10',
            ],
            [
                'name' => 'Nước hoa unisex Jo Malone',
                'description' => 'Nước hoa unisex với hương thơm tự nhiên, nhẹ nhàng',
                'import_price' => 800000,
                'selling_price' => 1200000,
                'category' => 'Nước hoa unisex',
                'brand' => 'Jo Malone',
                'sku' => 'JOMALONE-UNI-001',
                'stock' => 8,
                'volume' => '30ml',
                'concentration' => 'EDC',
                'origin' => 'Anh',
                'import_date' => '2025-08-12',
                'is_active' => true,
                'sales_channel' => 'Shopee',
                'tags' => 'unisex, tự nhiên, nhẹ nhàng',
                'product_type' => 'Nước hoa',
                'product_form' => 'Lỏng',
                'expiry_date' => '2028-08-12',
                'branch_price' => json_encode([
                    'Hà Nội' => 1200000,
                    'TP.HCM' => 1250000,
                    'Đà Nẵng' => 1220000
                ]),
                'customer_group_price' => json_encode([
                    'VIP' => 1100000,
                    'Thường' => 1200000,
                    'Đại lý' => 1000000
                ]),
                'created_date' => '2025-08-12',
            ],
            [
                'name' => 'fsfdfs',
                'description' => 'Sản phẩm test',
                'import_price' => 100000,
                'selling_price' => 150000,
                'category' => 'Test',
                'brand' => null,
                'sku' => 'TEST-001',
                'stock' => 0,
                'volume' => '50ml',
                'concentration' => 'EDT',
                'origin' => 'Việt Nam',
                'import_date' => '2025-08-19',
                'is_active' => false,
                'sales_channel' => 'Offline',
                'tags' => 'test, mẫu',
                'product_type' => 'Test',
                'product_form' => 'Lỏng',
                'expiry_date' => null,
                'branch_price' => json_encode([
                    'Hà Nội' => 150000,
                    'TP.HCM' => 155000,
                    'Đà Nẵng' => 152000
                ]),
                'customer_group_price' => json_encode([
                    'VIP' => 140000,
                    'Thường' => 150000,
                    'Đại lý' => 130000
                ]),
                'created_date' => '2025-08-19',
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
