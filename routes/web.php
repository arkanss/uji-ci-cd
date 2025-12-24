<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [App\Http\Controllers\Auth\LoginController::class, 'show'])->name('login');
    Route::post('/login', [App\Http\Controllers\Auth\LoginController::class, 'authenticate'])->name('login.process');
});

Route::middleware('auth')->group(function () {
    Route::view('/dashboard', 'pages.dashboard')->name('dashboard');
    Route::post('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');
});

//product categories
Route::get('/categories', App\Livewire\ProductCategory\ProductCategoryIndex::class)->name('product-categories.index');

//operational costs
Route::get('/operational-costs', App\Livewire\Operational\OperationalCostIndex::class)->name('operational-costs.index');

//purchase orders
Route::get('/purchase-orders', App\Livewire\PurchaseOrder\PurchaseOrderIndex::class)->name('purchase-orders.index');

//products
Route::get('/products', App\Livewire\Product\ProductIndex::class)->name('products.index');

//admin management
Route::get('/admins', App\Livewire\Admin\AdminIndex::class)->name('admin.index');

//csv reports
Route::get('/csv-reports', App\Livewire\CsvReport\CsvReportIndex::class)->name('csv-reports.index');

//delivery 
Route::get('/delivery', App\Livewire\Delivery\DeliveryIndex::class)->name('delivery.index');

//stock
Route::get('/stock', App\Livewire\Stock\StockIndex::class)->name('stock.index');