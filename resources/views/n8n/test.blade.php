@extends('layouts.app')

@section('title', 'Test N8N Integration')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Test N8N Integration</h1>
        
        <div class="bg-white shadow-lg rounded-lg p-6 mb-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">API Endpoints</h2>
            <div class="space-y-4">
                <div class="border rounded-lg p-4">
                    <h3 class="font-medium text-gray-700 mb-2">Low Stock Products</h3>
                    <p class="text-sm text-gray-600 mb-2">GET /api/n8n/inventory/low-stock</p>
                    <a href="{{ route('api.n8n.inventory.low-stock') }}" 
                       class="inline-block bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition-colors">
                        Test API
                    </a>
                </div>
                
                <div class="border rounded-lg p-4">
                    <h3 class="font-medium text-gray-700 mb-2">Inventory Overview</h3>
                    <p class="text-sm text-gray-600 mb-2">GET /api/n8n/inventory/overview</p>
                    <a href="{{ route('api.n8n.inventory.overview') }}" 
                       class="inline-block bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 transition-colors">
                        Test API
                    </a>
                </div>
                
                <div class="border rounded-lg p-4">
                    <h3 class="font-medium text-gray-700 mb-2">Pending Orders</h3>
                    <p class="text-sm text-gray-600 mb-2">GET /api/n8n/orders/pending</p>
                    <a href="{{ route('api.n8n.orders.pending') }}" 
                       class="inline-block bg-purple-500 text-white px-4 py-2 rounded hover:bg-purple-600 transition-colors">
                        Test API
                    </a>
                </div>
            </div>
        </div>
        
        <div class="bg-white shadow-lg rounded-lg p-6 mb-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Command Line Tools</h2>
            <div class="space-y-4">
                <div class="border rounded-lg p-4">
                    <h3 class="font-medium text-gray-700 mb-2">Check Low Stock</h3>
                    <p class="text-sm text-gray-600 mb-2">Kiểm tra sản phẩm có tồn kho thấp</p>
                    <div class="bg-gray-100 p-3 rounded font-mono text-sm">
                        php artisan inventory:check-low-stock
                    </div>
                </div>
                
                <div class="border rounded-lg p-4">
                    <h3 class="font-medium text-gray-700 mb-2">Check Low Stock with Alert</h3>
                    <p class="text-sm text-gray-600 mb-2">Kiểm tra và gửi thông báo qua n8n</p>
                    <div class="bg-gray-100 p-3 rounded font-mono text-sm">
                        php artisan inventory:check-low-stock --send-alert
                    </div>
                </div>
            </div>
        </div>
        
        <div class="bg-white shadow-lg rounded-lg p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">N8N Configuration</h2>
            <div class="space-y-4">
                <div class="border rounded-lg p-4">
                    <h3 class="font-medium text-gray-700 mb-2">Environment Variables</h3>
                    <div class="bg-gray-100 p-3 rounded font-mono text-sm space-y-2">
                        <div>N8N_WEBHOOK_URL=https://your-n8n-instance.com/webhook</div>
                        <div>N8N_API_KEY=your-n8n-api-key</div>
                        <div>N8N_BASE_URL=https://your-n8n-instance.com</div>
                    </div>
                </div>
                
                <div class="border rounded-lg p-4">
                    <h3 class="font-medium text-gray-700 mb-2">Webhook URLs for N8N</h3>
                    <div class="bg-gray-100 p-3 rounded font-mono text-sm space-y-2">
                        <div>Low Stock Alert: {{ url('/api/n8n/inventory/low-stock') }}</div>
                        <div>New Order: {{ url('/api/n8n/orders/pending') }}</div>
                        <div>Daily Report: {{ url('/api/n8n/inventory/overview') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


