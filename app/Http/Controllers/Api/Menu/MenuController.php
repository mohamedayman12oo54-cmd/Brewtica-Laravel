<?php

namespace App\Http\Controllers\Api\Menu;

use App\Http\Controllers\Controller;
use App\Http\Resources\Menu\MainCategoryResource;
use App\Http\Resources\Menu\MenuItemDetailResource;
use App\Http\Resources\Menu\MenuItemResource;
use App\Services\MenuService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function __construct(
        private readonly MenuService $menuService
    ){}

    // GET /api/menu/categories
    public function categories(): JsonResponse
    {
        $categories = $this->menuService->getAllCategories();

        return response()->json([
            'status' => 'success',
            'data' => MainCategoryResource::collection($categories),
        ]);
    }

    // GET /api/menu/items
    public function items(Request $request): JsonResponse
    {
        $items = $this->menuService->getItems([
            'category_id' => $request->query('category_id'),
            'q' => $request->query('q'),
        ]);

        return response()->json([
            'status' => 'success',
            'data' => MenuItemResource::collection($items),
        ]);
    }

    // GET /api/menu/items/{id}
    public function show(int $id): JsonResponse
    {
        $item = $this->menuService->getItem($id);

        return response()->json([
            'status' => 'success',
            'data' => new MenuItemDetailResource($item),
        ]);
    }
}
