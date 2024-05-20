<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ProductCreateRequest;
use App\Http\Requests\Api\ProductUpdateRequest;
use App\Models\Product;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    private function saveImage(UploadedFile $file): string
    {
        $path = 'uploads/images/products';
        $fullPath = public_path($path);
        $fileName = uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move($fullPath, $fileName);

        return "{$path}/{$fileName}";
    }

    private function deleteImage(Product $product): bool
    {
        $path = public_path($product->image);
        if (!file_exists($path)) {
            return false;
        }
        return unlink($path);
    }

    public function create(ProductCreateRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['modified_by'] = Auth::user()->email;

        $file = $request->file('image');
        $path = $this->saveImage($file);

        $data['image'] = $path;

        $product = Product::create($data);

        return response()->json([
            'success' => true,
            'data' => $product->toArray()
        ], 200);
    }

    public function update(ProductUpdateRequest $request, int $id): JsonResponse
    {
        $data = $request->validated();
        $data['modified_by'] = Auth::user()->email;
        $file = $request->file('image');

        try {
            $product = Product::findOrFail($id);

            $this->deleteImage($product);
            $data['image'] = $this->saveImage($file);

            $product->update($data);

            return response()->json([
                'success' => true,
                'data' => $product->toArray()
            ]);
        } catch (Exception) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'message' => 'Product not found'
                ]
            ], 400);
        }
    }

    public function getAll(Request $request): JsonResponse
    {
        $name = $request->query('name', '');
        $products = Product::where('name', 'like', '%' . $name . '%')->get();
        $products = $products->map(function (Product $product) {
            $productArr = $product->toArray();
            $productArr['category'] = $product->category;
            unset($productArr['category_id']);

            return $productArr;
        });

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    public function get(int $id): JsonResponse
    {
        $product = Product::find($id);

        return response()->json([
            'success' => true,
            'data' => $product->toArray()
        ]);
    }

    public function delete(int $id): JsonResponse
    {
        try {
            $category = Product::findOrFail($id);
            $category->delete();

            return response()->json([
                'success' => true
            ]);
        } catch (Exception) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'message' => 'Product not found'
                ]
            ], 400);
        }
    }
}
