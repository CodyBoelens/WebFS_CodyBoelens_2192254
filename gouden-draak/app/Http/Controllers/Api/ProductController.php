<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * US-9: Producten ophalen met zoeken en filteren.
     * - Zoeken op naam en menunummer
     * - Filteren op categorie
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::with(['category', 'promotions'])
            ->where('active', true);

        // Zoeken op naam of menunummer
        if ($search = $request->query('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('menu_number', 'like', "%{$search}%");
            });
        }

        // Filteren op categorie
        if ($categoryId = $request->query('category_id')) {
            $query->where('category_id', $categoryId);
        }

        $products = $query->orderBy('category_id')->orderBy('sort_order')->get()
            ->map(fn($p) => [
                'id'            => $p->id,
                'menu_number'   => $p->menu_number,
                'name'          => $p->name,
                'description'   => $p->description,
                'price'         => $p->price,
                'current_price' => $p->current_price,
                'image'         => $p->image ? asset('storage/' . $p->image) : null,
                'allergens'     => $p->allergens ?? [],
                'category'      => $p->category?->name,
                'category_id'   => $p->category_id,
            ]);

        return response()->json([
            'data'       => $products,
            'categories' => Category::where('active', true)->orderBy('sort_order')->get(['id', 'name']),
        ]);
    }

    public function show(Product $product): JsonResponse
    {
        $product->load(['category', 'promotions']);
        return response()->json($product);
    }
}
