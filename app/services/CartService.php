<?php

namespace App\Services;

use App\Models\MenuItemSizePrice;
use App\Models\User;
use App\OrderSize;

class CartService
{
    // ======= Get Cart =======
    public function getCart(User $user): array
    {
        $items = $user->cart()
                      ->with(['menuItem.sizePrices'])
                      ->get();

        $formattedItems = $items->map(function ($cartItem) {
            $unitPrice = $this->getPrice($cartItem->menu_item_id, $cartItem->size);

            return [
                'menu_item'  => [
                    'id'    => $cartItem->menuItem->id,
                    'name'  => $cartItem->menuItem->name,
                    'image' => $cartItem->menuItem->image
                                    ? asset('storage/' . $cartItem->menuItem->image)
                                    : null,
                ],
                'size'       => $cartItem->size,
                'unit_price' => $unitPrice,
                'quantity'   => $cartItem->quantity,
                'subtotal'   => round($unitPrice * $cartItem->quantity, 2),
            ];
        });

        return [
            'items'       => $formattedItems,
            'total'       => round($formattedItems->sum('subtotal'), 2),
            'items_count' => $items->count(),
        ];
    }

    // ======= Add To Cart =======
    public function addToCart(User $user, array $data): array
    {
        $price = $this->getPrice($data['menu_item_id'], $data['size']);

        if(!$price){
            return ['success' => false, 'reason' => 'size_unavailable'];
        }

        $quantity = $data['quantity'] ?? 1;

        $cartItem = $user->cart()
                         ->where('menu_item_id', $data['menu_item_id'])
                         ->where('size', $data['size'])
                         ->first();

        if($cartItem){

            $cartItem->increment('quantity', $quantity);

        } else {

            $user->cart()->create([
                'menu_item_id' => $data['menu_item_id'],
                'size' => $data['size'],
                'quantity' => $quantity,
            ]);
        }

        return ['success' => true];
    }

    // ======= Update Cart Item =======
    public function updateCartItem(User $user, int $menuItemId, string $size, int $quantity): array
    {
        $cartItem = $user->cart()
                         ->where('menu_item_id', $menuItemId)
                         ->where('size', $size)
                         ->first();

        // ← Scoped Query: بندور في cart الـ user بس مش في كل الـ cart
        if (!$cartItem) {
            return ['success' => false, 'reason' => 'not_found'];
        }

        if ($quantity === 0) {
            // لو الـ quantity وصلت 0 → امسح الـ item
            $cartItem->delete();
            return ['success' => true, 'action' => 'removed'];
        }

        $cartItem->update(['quantity' => $quantity]);
        return ['success' => true, 'action' => 'updated'];
    }

    // ======= Remove Cart Item =======
    public function removeCartItem(User $user, int $menuItemId, string $size): bool
    {
        $deleted = $user->cart()
                        ->where('menu_item_id', $menuItemId)
                        ->where('size', $size)
                        ->delete();

        // ← Scoped Query: بنضمن إن الـ user يمسح من cart بتاعته بس
        return $deleted > 0;
    }

    // ======= Clear Cart =======
    public function clearCart(User $user): void
    {
        $user->cart()->delete();
    }

    // ======= Helper: Get Price =======
    public function getPrice(int $menuItemId, OrderSize|string $size): float|null
    {
        $sizePrice = MenuItemSizePrice::where('menu_item_id', $menuItemId)
                                      ->where('size', $size instanceof OrderSize ? $size->value : $size)
                                      ->first();

        return $sizePrice?->price;
    }
}