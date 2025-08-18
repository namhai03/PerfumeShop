<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

Route::get('/', function () {
    return redirect()->route('products.index');
});

Route::resource('products', ProductController::class);
Route::post('products/import', [ProductController::class, 'import'])->name('products.import');
Route::get('products/export', [ProductController::class, 'export'])->name('products.export');
