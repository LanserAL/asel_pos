<?php

namespace App\Actions;

use App\Models\Promotion;
use App\Models\Product;

class ApplyPromotionsToCartAction
{
    /**
     * Executes the promotion engine on the given shopping cart.
     *
     * @param array  $cart
     * @param int    $tenantId
     * @param string|null $couponCode
     * @return array
     */
    public function execute(array $cart, int $tenantId, ?string $couponCode = null): array
    {
        $appliedPromotions = [];
        $totalDiscount = 0.00;

        // Calculate subtotal of raw items
        $originalSubtotal = collect($cart)->sum('total');

        // Working copy of cart quantities to track combo consumption
        $cartItems = [];
        foreach ($cart as $item) {
            $cartItems[$item['id']] = [
                'id' => $item['id'],
                'name' => $item['name'],
                'price' => (float)$item['price'],
                'quantity' => (int)$item['quantity'],
                'total' => (float)$item['total'],
                'available_quantity' => (int)$item['quantity'] // Used for combo matching
            ];
        }

        // Fetch all active promotions for this tenant
        $promotions = Promotion::where('tenant_id', $tenantId)
            ->active()
            ->with('products')
            ->get();

        // -------------------------------------------------------------
        // STEP A: Quantity/Volume Discounts (Mayoreo)
        // -------------------------------------------------------------
        $quantityPromos = $promotions->where('type', 'quantity');
        foreach ($quantityPromos as $promo) {
            foreach ($promo->products as $promoProduct) {
                if (isset($cartItems[$promoProduct->id])) {
                    $cartItem = &$cartItems[$promoProduct->id];
                    if ($cartItem['quantity'] >= $promo->min_quantity) {
                        $discountOnLine = 0.00;
                        $linePrice = $cartItem['price'];
                        $qty = $cartItem['quantity'];

                        if ($promo->discount_type === 'percentage') {
                            $discountOnLine = ($linePrice * $qty) * ($promo->discount_value / 100);
                        } elseif ($promo->discount_type === 'fixed_discount') {
                            $discountOnLine = $promo->discount_value * $qty;
                        } elseif ($promo->discount_type === 'fixed_price') {
                            $discountOnLine = max(0, ($linePrice - $promo->discount_value) * $qty);
                        }

                        if ($discountOnLine > 0) {
                            $appliedPromotions[] = [
                                'name' => "Volumen: " . $promo->name . " ({$cartItem['name']})",
                                'discount' => round($discountOnLine, 2),
                                'type' => 'quantity'
                            ];
                            $totalDiscount += $discountOnLine;
                        }
                    }
                }
            }
        }

        // -------------------------------------------------------------
        // STEP B: Combos / Bundles (Paquetes)
        // -------------------------------------------------------------
        $comboPromos = $promotions->where('type', 'combo');
        foreach ($comboPromos as $promo) {
            // Check if all combo products are in the cart with the minimum required quantity
            $canFormCombo = true;
            $possibleCombosCount = PHP_INT_MAX;

            $promoProducts = $promo->products;
            if ($promoProducts->isEmpty()) {
                continue;
            }

            foreach ($promoProducts as $reqProduct) {
                $requiredQty = $reqProduct->pivot->quantity;
                if (!isset($cartItems[$reqProduct->id]) || $cartItems[$reqProduct->id]['available_quantity'] < $requiredQty) {
                    $canFormCombo = false;
                    break;
                } else {
                    $qtyPossible = (int)($cartItems[$reqProduct->id]['available_quantity'] / $requiredQty);
                    if ($qtyPossible < $possibleCombosCount) {
                        $possibleCombosCount = $qtyPossible;
                    }
                }
            }

            if ($canFormCombo && $possibleCombosCount > 0 && $possibleCombosCount != PHP_INT_MAX) {
                // Form N combos
                $comboDiscount = 0.00;
                
                // Subtract the items consumed from the available_quantity pool
                foreach ($promoProducts as $reqProduct) {
                    $requiredQty = $reqProduct->pivot->quantity;
                    $consumedQty = $requiredQty * $possibleCombosCount;
                    $cartItems[$reqProduct->id]['available_quantity'] -= $consumedQty;
                }

                // Calculate the original cost of items consumed for the combo
                $standardComboPrice = 0.00;
                foreach ($promoProducts as $reqProduct) {
                    $standardComboPrice += $cartItems[$reqProduct->id]['price'] * $reqProduct->pivot->quantity;
                }

                if ($promo->discount_type === 'fixed_price') {
                    // Special package price (e.g. burgers + soda = $100 flat)
                    $comboDiscount = max(0, ($standardComboPrice - $promo->discount_value) * $possibleCombosCount);
                } elseif ($promo->discount_type === 'fixed_discount') {
                    // Fixed discount per combo
                    $comboDiscount = $promo->discount_value * $possibleCombosCount;
                } elseif ($promo->discount_type === 'percentage') {
                    // Percentage discount on combo items
                    $comboDiscount = ($standardComboPrice * $possibleCombosCount) * ($promo->discount_value / 100);
                }

                if ($comboDiscount > 0) {
                    $appliedPromotions[] = [
                        'name' => "Combo: " . $promo->name . " (x" . $possibleCombosCount . ")",
                        'discount' => round($comboDiscount, 2),
                        'type' => 'combo'
                    ];
                    $totalDiscount += $comboDiscount;
                }
            }
        }

        // -------------------------------------------------------------
        // STEP C: Coupons / Promo Codes (Códigos)
        // -------------------------------------------------------------
        if ($couponCode) {
            $cleanedCode = strtoupper(trim($couponCode));
            $couponPromo = Promotion::where('tenant_id', $tenantId)
                ->where('type', 'code')
                ->where('code', $cleanedCode)
                ->active()
                ->first();

            if ($couponPromo) {
                // Apply discount on the remaining taxable subtotal after volume & combo discounts
                $remainingSubtotal = max(0, $originalSubtotal - $totalDiscount);
                $couponDiscount = 0.00;

                if ($couponPromo->discount_type === 'percentage') {
                    $couponDiscount = $remainingSubtotal * ($couponPromo->discount_value / 100);
                } elseif ($couponPromo->discount_type === 'fixed_discount') {
                    $couponDiscount = min($couponPromo->discount_value, $remainingSubtotal);
                }

                if ($couponDiscount > 0) {
                    $appliedPromotions[] = [
                        'name' => "Cupón: " . $couponPromo->name . " ({$cleanedCode})",
                        'discount' => round($couponDiscount, 2),
                        'type' => 'code'
                    ];
                    $totalDiscount += $couponDiscount;
                }
            }
        }

        // recalculate grand totals
        $finalSubtotal = max(0, $originalSubtotal - $totalDiscount);
        $tax = $finalSubtotal * 0.16; // 16% IVA
        $total = $finalSubtotal + $tax;

        return [
            'applied_promotions' => $appliedPromotions,
            'total_discount' => round($totalDiscount, 2),
            'subtotal' => round($originalSubtotal, 2),
            'taxable_subtotal' => round($finalSubtotal, 2),
            'tax' => round($tax, 2),
            'total' => round($total, 2)
        ];
    }
}
