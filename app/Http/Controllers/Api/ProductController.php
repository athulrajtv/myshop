<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $q = Product::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $q->where(function ($s) use ($search) {
                $s->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('min_price')) {
            $q->where('price', '>=', (float)$request->min_price);
        }

        if ($request->filled('max_price')) {
            $q->where('price', '<=', (float)$request->max_price);
        }

        $q->orderBy('created_at', 'desc');

        $perPage = (int) $request->input('per_page', 10);
        $products = $q->paginate($perPage)->withQueryString();

        $products->getCollection()->transform(function ($p) {
            $p->image_url = $p->image_url;
            return $p;
        });

        return response()->json($products);
    }

    public function store(ProductRequest $request)
    {

        $data = $request->validated();

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $data['image'] = $path;
        }

        $data['user_id'] = $request->user()->id ?? null;

        $product = Product::create($data);

        return response()->json([
            'message' => 'Product created successfully',
            'product' => $product
        ], 201);
    }


    public function show($id)
    {
        $product = Product::findOrFail($id);
        $product->image_url = $product->image_url;
        return response()->json($product);
    }

    public function update(ProductRequest $request, $id)
    {
        $product = Product::findOrFail($id);

        $data = $request->validated();

        if ($request->hasFile('image')) {

            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }

            $path = $request->file('image')->store('products', 'public');
            $data['image'] = $path;
        }

        $product->update($data);

        return response()->json([
            'message' => 'Product updated successfully',
            'product' => $product
        ]);
    }


    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }
        $product->delete();

        return response()->json(['message' => 'Product deleted']);
    }
}
