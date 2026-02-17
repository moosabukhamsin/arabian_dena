<?php

namespace App\Services;

use App\Models\ProductItem;
use App\Models\BackloadItem;

class ProductItemStatusService
{
    /**
     * Update the rental status of a product item based on its order items and backloads
     * 
     * @param ProductItem $productItem
     * @return void
     */
    public function updateRentalStatus(ProductItem $productItem): void
    {
        // Get all order items for this product item from active orders
        $orderItems = $productItem->orderItems()
            ->with('order.company')
            ->whereHas('order', function($query) {
                $query->where('is_active', true);
            })
            ->get();

        // If no order items exist, item is in stock
        if ($orderItems->isEmpty()) {
            $productItem->update(['status' => 'In Stock']);
            return;
        }

        // Check if any order item is currently active (not returned)
        foreach ($orderItems as $orderItem) {
            // Check if this order item has been returned in backloads
            $backloadItem = BackloadItem::where('order_item_id', $orderItem->id)->first();

            if (!$backloadItem) {
                // This order item hasn't been returned yet, so it's under rental
                $productItem->update(['status' => 'Under Rental']);
                return;
            }
        }

        // All order items have been returned (backloaded)
        $productItem->update(['status' => 'Backloaded']);
    }
}

