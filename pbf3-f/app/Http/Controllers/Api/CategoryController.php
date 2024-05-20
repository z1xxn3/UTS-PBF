<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CategoryCreateRequest;
use App\Http\Requests\Api\CategoryUpdateRequest;
use App\Models\Category;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function create(CategoryCreateRequest $request): JsonResponse
    {
        $data = $request->validated();
        $category = Category::create($data);

        return response()->json([
            'success' => true,
            'data' => $category->toArray()
        ]);
    }

    public function update(CategoryUpdateRequest $request, int $id): JsonResponse
    {
        $data = $request->validated();

        try {
            $category = Category::findOrFail($id);
            $category->name = $data['name'] ?? $category->name;
            $category->update();

            return response()->json([
                'success' => true,
                'data' => $category->toArray()
            ]);
        } catch (Exception) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'message' => 'Category not found'
                ]
            ], 400);
        }
    }

    public function delete(int $id): JsonResponse
    {
        try {
            $category = Category::findOrFail($id);
            $category->delete();

            return response()->json([
                'success' => true
            ]);
        } catch (Exception) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'message' => 'Category not found'
                ]
            ], 400);
        }
    }

    public function get(int $id): JsonResponse
    {
        $category = Category::find($id);
        return response()->json([
            'success' => true,
            'data' => $category->toArray() ?? null
        ]);
    }

    public function getAll(Request $request): JsonResponse
    {
        $name = $request->query('name', '');
        $categories = Category::where('name', 'like', "%{$name}%")->get();

        return response()->json([
            'success' => true,
            'data' => $categories->toArray()
        ]);
    }
}
