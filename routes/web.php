<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

Route::get('/', function () {
    return redirect()->route('products.index');
});

// Đặt export/import TRƯỚC resource để không bị nuốt bởi route show products/{product}
Route::get('products/export', [ProductController::class, 'export'])->name('products.export');
Route::post('products/import', [ProductController::class, 'import'])->name('products.import');
Route::delete('products/bulk-delete', [ProductController::class, 'bulkDestroy'])->name('products.bulkDestroy');

// Ràng buộc id là số để tránh trùng path như 'export'
Route::resource('products', ProductController::class)->whereNumber('product');
