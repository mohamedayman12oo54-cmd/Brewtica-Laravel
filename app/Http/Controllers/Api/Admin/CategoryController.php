<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\StoreSubCategoryRequest;
use App\Http\Requests\Admin\StoreSubSubCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Http\Requests\Admin\UpdateSubCategoryRequest;
use App\Http\Requests\Admin\UpdateSubSubCategoryRequest;
use App\Http\Resources\Admin\CategoryResource;
use App\Services\Admin\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(
        private readonly CategoryService $categoryService
    ) {}

    // ======= Main Categories =======

    // GET /api/admin/categories
    public function indexMain(): JsonResponse
    {
        $categories = $this->categoryService->getMainCategories();

        return response()->json([
            'status' => 'success',
            'data'   => CategoryResource::collection($categories),
        ]);
    }

    // POST /api/admin/categories
    public function storeMain(StoreCategoryRequest $request): JsonResponse
    {
        $category = $this->categoryService->createMainCategory($request->validated());

        return response()->json([
            'status'  => 'success',
            'message' => 'Main category created successfully.',
            'data'    => new CategoryResource($category),
        ], 201);
    }

    // PATCH /api/admin/categories/{id}
    public function updateMain(UpdateCategoryRequest $request, int $id): JsonResponse
    {
        $result = $this->categoryService->updateMainCategory($id, $request->validated());

        if (!$result['success']) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Main category not found.',
            ], 404);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Main category updated successfully.',
            'data'    => new CategoryResource($result['category']),
        ]);
    }

    // DELETE /api/admin/categories/{id}
    public function destroyMain(int $id): JsonResponse
    {
        $result = $this->categoryService->deleteMainCategory($id);

        if (!$result['success']) {
            return $this->deleteErrorResponse($result['reason']);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Main category deleted successfully.',
        ]);
    }

    // ======= Sub Categories =======

    // GET /api/admin/sub-categories
    public function indexSub(Request $request): JsonResponse
    {
        $subCategories = $this->categoryService->getSubCategories([
            'main_category_id' => $request->query('main_category_id'),
        ]);

        return response()->json([
            'status' => 'success',
            'data'   => CategoryResource::collection($subCategories),
        ]);
    }

    // POST /api/admin/sub-categories
    public function storeSub(StoreSubCategoryRequest $request): JsonResponse
    {
        $subCategory = $this->categoryService->createSubCategory(
            $request->safe()->except('image'),
            $request->file('image')
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'Sub category created successfully.',
            'data'    => new CategoryResource($subCategory),
        ], 201);
    }

    // PATCH /api/admin/sub-categories/{id}
    public function updateSub(UpdateSubCategoryRequest $request, int $id): JsonResponse
    {
        $result = $this->categoryService->updateSubCategory(
            $id,
            $request->safe()->except('image'),
            $request->file('image')
        );

        if (!$result['success']) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Sub category not found.',
            ], 404);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Sub category updated successfully.',
            'data'    => new CategoryResource($result['category']),
        ]);
    }

    // DELETE /api/admin/sub-categories/{id}
    public function destroySub(int $id): JsonResponse
    {
        $result = $this->categoryService->deleteSubCategory($id);

        if (!$result['success']) {
            return $this->deleteErrorResponse($result['reason']);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Sub category deleted successfully.',
        ]);
    }

    // ======= Sub-Sub Categories =======

    // GET /api/admin/sub-sub-categories
    public function indexSubSub(Request $request): JsonResponse
    {
        $subSubCategories = $this->categoryService->getSubSubCategories([
            'sub_category_id' => $request->query('sub_category_id'),
        ]);

        return response()->json([
            'status' => 'success',
            'data'   => CategoryResource::collection($subSubCategories),
        ]);
    }

    // POST /api/admin/sub-sub-categories
    public function storeSubSub(StoreSubSubCategoryRequest $request): JsonResponse
    {
        $subSubCategory = $this->categoryService->createSubSubCategory(
            $request->safe()->except('image'),
            $request->file('image')
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'Sub-sub category created successfully.',
            'data'    => new CategoryResource($subSubCategory),
        ], 201);
    }

    // PATCH /api/admin/sub-sub-categories/{id}
    public function updateSubSub(UpdateSubSubCategoryRequest $request, int $id): JsonResponse
    {
        $result = $this->categoryService->updateSubSubCategory(
            $id,
            $request->safe()->except('image'),
            $request->file('image')
        );

        if (!$result['success']) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Sub-sub category not found.',
            ], 404);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Sub-sub category updated successfully.',
            'data'    => new CategoryResource($result['category']),
        ]);
    }

    // DELETE /api/admin/sub-sub-categories/{id}
    public function destroySubSub(int $id): JsonResponse
    {
        $result = $this->categoryService->deleteSubSubCategory($id);

        if (!$result['success']) {
            return $this->deleteErrorResponse($result['reason']);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Sub-sub category deleted successfully.',
        ]);
    }

    // ======= Helpers =======
    private function deleteErrorResponse(string $reason): JsonResponse
    {
        $message = match ($reason) {
            'has_items' => 'This category still has menu items. Remove them before deleting.',
            default     => 'Category not found.',
        };

        $status = $reason === 'has_items' ? 422 : 404;

        return response()->json([
            'status'  => 'error',
            'message' => $message,
        ], $status);
    }
}
