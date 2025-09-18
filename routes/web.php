<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerGroupController;
use App\Http\Controllers\CashVoucherController;
use App\Http\Controllers\CashAccountController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\ShippingController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return redirect()->route('dashboard.index');
});

// Dashboard
Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
Route::get('dashboard/kpi-data', [DashboardController::class, 'getKpiData'])->name('dashboard.kpi-data');
Route::get('dashboard/chart-data', [DashboardController::class, 'getChartData'])->name('dashboard.chart-data');
Route::get('dashboard/quick-info', [DashboardController::class, 'getQuickInfo'])->name('dashboard.quick-info');

// Test N8N Integration
Route::get('/n8n/test', function () {
    return view('n8n.test');
})->name('n8n.test');

// Đặt export/import TRƯỚC resource để không bị nuốt bởi route show products/{product}
Route::get('products/export', [ProductController::class, 'export'])->name('products.export');
Route::post('products/import', [ProductController::class, 'import'])->name('products.import');
Route::delete('products/bulk-delete', [ProductController::class, 'bulkDestroy'])->name('products.bulkDestroy');

// Ràng buộc id là số để tránh trùng path như 'export'
Route::resource('products', ProductController::class)->whereNumber('product');

// Categories
Route::resource('categories', CategoryController::class)->whereNumber('category');
Route::post('categories/{category}/add-product', [CategoryController::class, 'addProduct'])->name('categories.add-product')->whereNumber('category');
Route::delete('categories/{category}/remove-product', [CategoryController::class, 'removeProduct'])->name('categories.remove-product')->whereNumber('category');

// Inventory
Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');
Route::get('inventory/history', [InventoryController::class, 'history'])->name('inventory.history');
Route::get('inventory/export', [InventoryController::class, 'export'])->name('inventory.export');
Route::post('inventory/import', [InventoryController::class, 'import'])->name('inventory.import');
Route::get('inventory/import/template', [InventoryController::class, 'downloadImportTemplate'])->name('inventory.import.template');
Route::get('inventory/{product}/export-history', [InventoryController::class, 'exportHistory'])->name('inventory.export-history')->whereNumber('product');
Route::post('inventory/{product}/adjust', [InventoryController::class, 'adjust'])->name('inventory.adjust');
Route::get('inventory/{product}', [InventoryController::class, 'show'])->name('inventory.show')->whereNumber('product');

// Customers
Route::get('customers/export', [CustomerController::class, 'export'])->name('customers.export');
Route::post('customers/import', [CustomerController::class, 'import'])->name('customers.import');
Route::delete('customers/bulk-delete', [CustomerController::class, 'bulkDestroy'])->name('customers.bulkDestroy');
Route::resource('customers', CustomerController::class)->whereNumber('customer');

// Customer Groups
Route::resource('customer-groups', CustomerGroupController::class)->whereNumber('customer_group');

// Cashbook
Route::get('cashbook', [CashVoucherController::class, 'index'])->name('cashbook.index');
Route::get('cashbook/create', [CashVoucherController::class, 'create'])->name('cashbook.create');
Route::post('cashbook', [CashVoucherController::class, 'store'])->name('cashbook.store');
Route::get('cashbook/export', [CashVoucherController::class, 'export'])->name('cashbook.export');
Route::get('cashbook/{voucher}', [CashVoucherController::class, 'show'])->name('cashbook.show')->whereNumber('voucher');
Route::get('cashbook/{voucher}/edit', [CashVoucherController::class, 'edit'])->name('cashbook.edit')->whereNumber('voucher');
Route::put('cashbook/{voucher}', [CashVoucherController::class, 'update'])->name('cashbook.update')->whereNumber('voucher');
Route::delete('cashbook/{voucher}', [CashVoucherController::class, 'destroy'])->name('cashbook.destroy')->whereNumber('voucher');
Route::post('cashbook/{voucher}/approve', [CashVoucherController::class, 'approve'])->name('cashbook.approve')->whereNumber('voucher');
Route::post('cashbook/{voucher}/cancel', [CashVoucherController::class, 'cancel'])->name('cashbook.cancel')->whereNumber('voucher');

// Cash Accounts - Sửa để không bị nested resource
Route::get('cashbook/accounts', [CashAccountController::class, 'index'])->name('cashbook.accounts.index');
Route::get('cashbook/accounts/create', [CashAccountController::class, 'create'])->name('cashbook.accounts.create');
Route::post('cashbook/accounts', [CashAccountController::class, 'store'])->name('cashbook.accounts.store');
Route::get('cashbook/accounts/{account}', [CashAccountController::class, 'show'])->name('cashbook.accounts.show')->whereNumber('account');
Route::get('cashbook/accounts/{account}/edit', [CashAccountController::class, 'edit'])->name('cashbook.accounts.edit')->whereNumber('account');
Route::put('cashbook/accounts/{account}', [CashAccountController::class, 'update'])->name('cashbook.accounts.update')->whereNumber('account');
Route::delete('cashbook/accounts/{account}', [CashAccountController::class, 'destroy'])->name('cashbook.accounts.destroy')->whereNumber('account');

// Promotions
Route::resource('promotions', PromotionController::class)->whereNumber('promotion');
Route::post('promotions/validate', [PromotionController::class, 'validatePromotion'])->name('promotions.validate');
Route::get('promotions/active', [PromotionController::class, 'getActivePromotions'])->name('promotions.active');
Route::post('promotions/calculate', [PromotionController::class, 'calculatePromotions'])->name('promotions.calculate');

// Shipping
Route::get('shipping/overview', [ShippingController::class, 'overview'])->name('shipping.overview');
Route::get('shipping/overview-data', [ShippingController::class, 'overviewData'])->name('shipping.overview.data');
Route::get('shipments', [ShipmentController::class, 'index'])->name('shipments.index');
Route::get('shipments/create', [ShipmentController::class, 'create'])->name('shipments.create');
Route::post('shipments', [ShipmentController::class, 'store'])->name('shipments.store');
Route::post('shipments/{shipment}/status', [ShipmentController::class, 'updateStatus'])->name('shipments.updateStatus')->whereNumber('shipment');
Route::get('shipments/{shipment}', [ShipmentController::class, 'show'])->name('shipments.show')->whereNumber('shipment');
Route::get('shipments/{shipment}/edit', [ShipmentController::class, 'edit'])->name('shipments.edit')->whereNumber('shipment');
Route::put('shipments/{shipment}', [ShipmentController::class, 'update'])->name('shipments.update')->whereNumber('shipment');
Route::delete('shipments/{shipment}', [ShipmentController::class, 'destroy'])->name('shipments.destroy')->whereNumber('shipment');

// Orders
Route::get('orders/sales', [OrderController::class, 'sales'])->name('orders.sales');
Route::get('orders/returns', [OrderController::class, 'returns'])->name('orders.returns');
Route::get('orders/drafts', [OrderController::class, 'drafts'])->name('orders.drafts');
Route::resource('orders', OrderController::class)->whereNumber('order');

// Reports
Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
Route::get('reports/overview', [ReportController::class, 'overview'])->name('reports.overview');
Route::get('reports/revenue-analysis', [ReportController::class, 'revenueAnalysis'])->name('reports.revenue-analysis');
Route::get('reports/customer-analysis', [ReportController::class, 'customerAnalysis'])->name('reports.customer-analysis');
Route::get('reports/order-analysis', [ReportController::class, 'orderAnalysis'])->name('reports.order-analysis');
