<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
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
Route::get('/operational-costs', App\Livewire\Finance\OperationalCost\OperationalCostIndex::class)->name('operational-costs.index');

//purchase orders
Route::get('/purchase-orders', App\Livewire\PurchaseOrder\PurchaseOrderIndex::class)->name('purchase-orders.index');

//products
Route::get('/products', App\Livewire\Product\ProductIndex::class)->name('products.index');

//product rewards
Route::get('/product-rewards', App\Livewire\Product\ProductRewardsIndex::class)->name('product-rewards.index');

//admin management
Route::get('/admins', App\Livewire\Admin\AdminIndex::class)->name('admin.index');

//csv reports
Route::get('/csv-reports', App\Livewire\CsvReport\CsvReportIndex::class)->name('csv-reports.index');

//delivery 
Route::get('/delivery', App\Livewire\Delivery\DeliveryIndex::class)->name('delivery.index');

//stock
Route::get('/stock', App\Livewire\Stock\StockIndex::class)->name('stock.index');

//merchants
Route::get('/merchants', App\Livewire\Merchant\MerchantIndex::class)->name('merchants.index');

//product stock histories
Route::get('/product-stock-histories', App\Livewire\ProductStockHistory\ProductStockHistoryIndex::class)->name('product-stock-history.index');

//product distribution
Route::get('/product-distribution', App\Livewire\ProductDistribution\ProductDistributionIndex::class)->name('product-distribution.index');

//challenges
Route::get('/challenges', App\Livewire\ChallengeManagement\ChallengeManagementIndex::class)->name('challenges.index');

// receive po
Route::get('/receive-po', App\Livewire\ReceivePO\ReceivePOIndex::class)->name('receive-po.index');

//point management
Route::get('/point-management', App\Livewire\PointManagement\PointManagementIndex::class)->name('point-management.index');

//dp item request
Route::get('/dp-item-requests', App\Livewire\DPItemRequest\DPItemRequestIndex::class)->name('dp-item-requests.index');
Route::get('/dp-item-requests/{id}', App\Livewire\DPItemRequest\DPItemRequestShow::class)->name('dp-item-requests.show');
Route::get('/dp-item-requests/{id}/edit', App\Livewire\DPItemRequest\DPItemRequestEdit::class)->name('dp-item-requests.edit');

Route::get('/searchable-select-demo', App\Livewire\SearchableSelectDemo::class)->name('searchable-select-demo');

Route::group(['prefix' => 'finance'], function () {
    Route::group(['prefix' => 'purchase-order'], function () {
        Route::get('/', App\Livewire\Finance\PurchaseOrder\PurchaseOrderIndex::class)->name('finance.purchase-order.index');
        Route::get('/create', App\Livewire\Finance\PurchaseOrder\PurchaseOrderForm::class)->name('finance.purchase-order.create');
        Route::get('/{id}/edit', App\Livewire\Finance\PurchaseOrder\PurchaseOrderForm::class)->name('finance.purchase-order.edit');
    });

    Route::group(['prefix' => 'operational-cost'], function () {
        Route::get('/', App\Livewire\Finance\OperationalCost\OperationalCostIndex::class)->name('finance.operational-cost.index');
    });
});
