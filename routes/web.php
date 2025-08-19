<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\InventoryController;

Route::get('/', function () {
    return redirect()->route('products.index');
});

// Đặt export/import TRƯỚC resource để không bị nuốt bởi route show products/{product}
Route::get('products/export', [ProductController::class, 'export'])->name('products.export');
Route::post('products/import', [ProductController::class, 'import'])->name('products.import');
Route::delete('products/bulk-delete', [ProductController::class, 'bulkDestroy'])->name('products.bulkDestroy');

// Ràng buộc id là số để tránh trùng path như 'export'
Route::resource('products', ProductController::class)->whereNumber('product');

// Categories
Route::resource('categories', CategoryController::class)->whereNumber('category');

// Inventory
Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');
Route::get('inventory/history', [InventoryController::class, 'history'])->name('inventory.history');
Route::post('inventory/{product}/adjust', [InventoryController::class, 'adjust'])->name('inventory.adjust');
Route::get('inventory/{product}', [InventoryController::class, 'show'])->name('inventory.show')->whereNumber('product');
