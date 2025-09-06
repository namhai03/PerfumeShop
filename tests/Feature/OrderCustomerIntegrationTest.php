<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Product;

class OrderCustomerIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_order_creates_customer_automatically()
    {
        // Tạo sản phẩm test
        $product = Product::create([
            'name' => 'Test Product',
            'sku' => 'TEST001',
            'import_price' => 80000,
            'selling_price' => 100000,
            'category' => 'Test Category',
            'stock' => 10,
            'is_active' => true,
        ]);

        // Dữ liệu đơn hàng
        $orderData = [
            'customer_name' => 'Nguyễn Văn A',
            'phone' => '0123456789',
            'type' => 'sale',
            'status' => 'unpaid',
            'order_date' => now()->format('Y-m-d'),
            'delivery_address' => '123 Test Street',
            'payment_method' => 'cash',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                    'unit_price' => 100000,
                    'custom_notes' => 'Test note'
                ]
            ]
        ];

        // Gửi request tạo đơn hàng
        $response = $this->post(route('orders.store'), $orderData);

        // Kiểm tra response
        $response->assertRedirect(route('orders.index'));
        $response->assertSessionHas('success');

        // Kiểm tra đơn hàng được tạo
        $this->assertDatabaseHas('orders', [
            'customer_name' => 'Nguyễn Văn A',
            'phone' => '0123456789',
        ]);

        // Kiểm tra khách hàng được tạo tự động
        $this->assertDatabaseHas('customers', [
            'name' => 'Nguyễn Văn A',
            'phone' => '0123456789',
            'address' => '123 Test Street',
            'customer_type' => 'walkin',
            'source' => 'offline',
        ]);

        // Kiểm tra đơn hàng có customer_id
        $order = Order::where('customer_name', 'Nguyễn Văn A')->first();
        $this->assertNotNull($order->customer_id);

        // Kiểm tra thống kê khách hàng được cập nhật
        $customer = Customer::where('name', 'Nguyễn Văn A')->first();
        $this->assertEquals(1, $customer->total_orders);
        $this->assertEquals(200000, $customer->total_spent);
    }

    public function test_create_order_updates_existing_customer()
    {
        // Tạo khách hàng có sẵn
        $existingCustomer = Customer::create([
            'name' => 'Nguyễn Văn B',
            'phone' => '0987654321',
            'address' => 'Old Address',
            'customer_type' => 'vip',
            'source' => 'online',
            'total_orders' => 5,
            'total_spent' => 500000,
        ]);

        // Tạo sản phẩm test
        $product = Product::create([
            'name' => 'Test Product 2',
            'sku' => 'TEST002',
            'import_price' => 120000,
            'selling_price' => 150000,
            'category' => 'Test Category 2',
            'stock' => 5,
            'is_active' => true,
        ]);

        // Dữ liệu đơn hàng với thông tin mới
        $orderData = [
            'customer_name' => 'Nguyễn Văn B',
            'phone' => '0987654321',
            'type' => 'sale',
            'status' => 'paid',
            'order_date' => now()->format('Y-m-d'),
            'delivery_address' => 'New Address Updated',
            'payment_method' => 'bank_transfer',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'unit_price' => 150000,
                ]
            ]
        ];

        // Gửi request tạo đơn hàng
        $response = $this->post(route('orders.store'), $orderData);

        // Kiểm tra response
        $response->assertRedirect(route('orders.index'));

        // Kiểm tra đơn hàng được tạo với customer_id đúng
        $order = Order::where('customer_name', 'Nguyễn Văn B')->latest()->first();
        $this->assertEquals($existingCustomer->id, $order->customer_id);

        // Kiểm tra thông tin khách hàng được cập nhật
        $existingCustomer->refresh();
        $this->assertEquals('New Address Updated', $existingCustomer->address);
        $this->assertEquals(6, $existingCustomer->total_orders); // 5 + 1
        $this->assertEquals(650000, $existingCustomer->total_spent); // 500000 + 150000
    }

    public function test_order_search_works_with_customer_relationship()
    {
        // Tạo khách hàng và đơn hàng
        $customer = Customer::create([
            'name' => 'Test Customer',
            'phone' => '0123456789',
            'customer_type' => 'walkin',
        ]);

        $order = Order::create([
            'order_number' => 'DH20250101001',
            'customer_id' => $customer->id,
            'customer_name' => 'Test Customer',
            'status' => 'unpaid',
            'type' => 'sale',
            'total_amount' => 100000,
            'final_amount' => 100000,
            'order_date' => now(),
        ]);

        // Test tìm kiếm theo tên khách hàng
        $response = $this->get(route('orders.index', ['search' => 'Test Customer']));
        $response->assertSee('Test Customer');
        $response->assertSee('DH20250101001');

        // Test tìm kiếm theo số điện thoại
        $response = $this->get(route('orders.index', ['search' => '0123456789']));
        $response->assertSee('Test Customer');
        $response->assertSee('DH20250101001');
    }
}