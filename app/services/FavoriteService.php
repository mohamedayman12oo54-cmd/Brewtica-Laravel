<?php

namespace App\Services;

use App\Models\MenuItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class FavoriteService
{
    // ======= Get Favorites =======
    public function getFavorites(User $user): Collection
    {
        return $user->favorites()
                    ->with(['menuItem.sizePrices'])
                    ->latest()
                    ->get()
        ;
    }

    // ======= Toggle Favorites =======
    public function toggleFavorite(User $user, int $menuItemId): array
    {
        $menuItem = MenuItem::findOrFail($menuItemId);

        $favorite = $user->favorites()
                         ->where('menu_item_id', $menuItemId)
                         ->first()
        ;

        if($favorite){
            $favorite->delete();
            return ['action' => 'removed'];
        }

        $user->favorites()->create(['menu_item_id' => $menuItemId]);
        return ['action' => 'added'];
        
    }

    // ======= Remove Favorite =======
    public function remove(User $user, int $menuItemId): bool
    {
        $deleted = $user->favorites()
                         ->where('menu_item_id', $menuItemId)
                         ->delete()
        ;

        return $deleted > 0;
    }
}