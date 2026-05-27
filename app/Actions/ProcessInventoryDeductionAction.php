<?php

namespace App\Actions;

use App\Models\Inventory;
use Illuminate\Support\Facades\DB;
use Exception;

class ProcessInventoryDeductionAction
{
    public function execute(int $branchId, int $productId, int $quantityToDeduct): void
    {
        DB::transaction(function () use ($branchId, $productId, $quantityToDeduct) {
            $inventory = Inventory::where('branch_id', $branchId)
                                  ->where('product_id', $productId)
                                  ->lockForUpdate()
                                  ->first();

            if (!$inventory) {
                throw new Exception("Inventory record not found for product ID: {$productId}");
            }

            if ($inventory->stock_quantity < $quantityToDeduct) {
                throw new Exception("Insufficient stock for product ID: {$productId}. Available: {$inventory->stock_quantity}");
            }

            $inventory->stock_quantity -= $quantityToDeduct;
            $inventory->save();
        });
    }
}
