<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreMenuItemRequest;
use App\Http\Requests\Admin\UpdateMenuItemRequest;
use App\Http\Resources\Admin\MenuItemResource;
use App\Services\Admin\MenuItemService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MenuItemController extends Controller
{
    public function __construct(
        private readonly MenuItemService $menuItemService
    ) {}

    // GET /api/admin/menu-items
    public function index(Request $request): JsonResponse
    {
        $items = $this->menuItemService->getItems([
            'sub_sub_category_id' => $request->query('sub_sub_category_id'),
        ]);

        // Before ApiResponse Integration

            // return response()->json([
            //     'status' => 'success',
            //     'data'   => MenuItemResource::collection($items),
            // ]);

        // After ApiResponse Integration

            return ApiResponse::success(MenuItemResource::collection($items));

        // =============================
    }

    // POST /api/admin/menu-items
    public function store(StoreMenuItemRequest $request): JsonResponse
    {
        $item = $this->menuItemService->createItem(
            $request->safe()->except('image'),
            $request->file('image')
        );

        // Before ApiResponse Integration

            // return response()->json([
            //     'status'  => 'success',
            //     'message' => 'Menu item created successfully.',
            //     'data'    => new MenuItemResource($item),
            // ], 201);

        // After ApiResponse Integration

            return ApiResponse::created(
                new MenuItemResource($item),
                'Menu item created successfully.'
            );

        // =============================
    }

    // PATCH /api/admin/menu-items/{id}
    public function update(UpdateMenuItemRequest $request, int $id): JsonResponse
    {
        $result = $this->menuItemService->updateItem(
            $id,
            $request->safe()->except('image'),
            $request->file('image')
        );

        if (!$result['success']) {
            // Before ApiResponse Integration

                // return response()->json([
                //     'status'  => 'error',
                //     'message' => 'Menu item not found.',
                // ], 404);

            // After ApiResponse Integration

                return ApiResponse::notFound('Menu item not found.');

            // =============================
        }

        // Before ApiResponse Integration

            // return response()->json([
            //     'status'  => 'success',
            //     'message' => 'Menu item updated successfully.',
            //     'data'    => new MenuItemResource($result['item']),
            // ]);

        // After ApiResponse Integration

            return ApiResponse::success(
                new MenuItemResource($result['item']),
                'Menu item updated successfully.'
            );

        // =============================
    }

    // DELETE /api/admin/menu-items/{id}
    public function destroy(int $id): JsonResponse
    {
        $result = $this->menuItemService->deleteItem($id);

        if (!$result['success']) {
            $message = match ($result['reason']) {
                'has_orders' => 'This item has order history and cannot be deleted.',
                default      => 'Menu item not found.',
            };

            // Before ApiResponse Integration

                // $status = $result['reason'] === 'has_orders' ? 422 : 404;
                //
                // return response()->json([
                //     'status'  => 'error',
                //     'message' => $message,
                // ], $status);

            // After ApiResponse Integration

                if ($result['reason'] === 'has_orders') {
                    return ApiResponse::error($message, 422);
                }

                return ApiResponse::notFound($message);

            // =============================
        }

        // Before ApiResponse Integration

            // return response()->json([
            //     'status'  => 'success',
            //     'message' => 'Menu item deleted successfully.',
            // ]);

        // After ApiResponse Integration

            return ApiResponse::success(message: 'Menu item deleted successfully.');

        // =============================
    }
}
