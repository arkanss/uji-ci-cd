<?php

namespace App\Observers;

use App\Models\ProductDistribution;
use App\Enums\OrderRequestEnum;
use Illuminate\Support\Facades\DB;
use App\Models\ProductStockHistory;

class ProductDistributionObserver
{
    /**
     * Handle the ProductDistribution "created" event.
     */
    public function created(ProductDistribution $productDistribution): void
    {
        //
    }

    /**
     * Handle the ProductDistribution "updated" event.
     */
    public function updated(ProductDistribution $distribution): void
    {
        if (! $distribution->isDirty('status')) {
            return;
        }

        if (
            $distribution->status === OrderRequestEnum::Delivering &&
            $distribution->getOriginal('status') !== OrderRequestEnum::Delivering
        ) {
            DB::transaction(function () use ($distribution) {

                $distribution->load([
                    'items.product.stockOverview' => fn ($q) => $q->lockForUpdate()
                ]);

                foreach ($distribution->items as $item) {

                    if ($item->approved_stock <= 0) {
                        continue;
                    }

                    $stock = $item->product->stockOverview;

                    if (! $stock) {
                        throw new \Exception(
                            "Stock overview not found for {$item->product->name}"
                        );
                    }

                    $before = $stock->stock_available;
                    $after  = $before - $item->approved_stock;

                    if ($after < 0) {
                        throw new \Exception(
                            "Stock available not enough for {$item->product->name}"
                        );
                    }

                    $stock->update([
                        'stock_available'   => $after,
                        'stock_in_delivery' => $stock->stock_in_delivery + $item->approved_stock,
                    ]);

                    ProductStockHistory::create([
                        'product_id'   => $item->product_id,
                        'merchant_id'  => null,
                        'stock'        => $item->approved_stock,
                        'stock_before' => $before,
                        'stock_after'  => $after,
                    ]);
                }
            });
        }

        if (
            $distribution->getOriginal('status') === OrderRequestEnum::Delivering &&
            $distribution->status === OrderRequestEnum::Delivered
        ) {
            DB::transaction(function () use ($distribution) {

                $distribution->load([
                    'items.product.stockOverview' => fn ($q) => $q->lockForUpdate()
                ]);

                foreach ($distribution->items as $item) {

                    if ($item->approved_stock <= 0) {
                        continue;
                    }

                    $stock = $item->product->stockOverview;

                    if (! $stock) {
                        throw new \Exception(
                            "Stock overview not found for {$item->product->name}"
                        );
                    }

                    if ($stock->stock_in_delivery < $item->approved_stock) {
                        throw new \Exception(
                            "Stock in delivery not enough for {$item->product->name}"
                        );
                    }

                    $stock->decrement('stock_in_delivery', $item->approved_stock);
                }
            });
        }
    }

    /**
     * Handle the ProductDistribution "deleted" event.
     */
    public function deleted(ProductDistribution $productDistribution): void
    {
        //
    }

    /**
     * Handle the ProductDistribution "restored" event.
     */
    public function restored(ProductDistribution $productDistribution): void
    {
        //
    }

    /**
     * Handle the ProductDistribution "force deleted" event.
     */
    public function forceDeleted(ProductDistribution $productDistribution): void
    {
        //
    }
}
