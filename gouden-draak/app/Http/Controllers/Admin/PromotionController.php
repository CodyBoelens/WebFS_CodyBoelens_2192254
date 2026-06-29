<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Promotion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PromotionController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Promotions/Index', [
            'promotions' => Promotion::with('products')->orderByDesc('valid_from')->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Promotions/Form', [
            'promotion' => null,
            'products'  => Product::where('active', true)
                ->orderByRaw("CAST(menu_number AS INTEGER), LENGTH(menu_number), menu_number")
                ->get(['id', 'menu_number', 'name', 'price']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:200',
            'description'      => 'nullable|string',
            'discount_amount'  => 'nullable|numeric|min:0',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'valid_from'       => 'required|date',
            'valid_until'      => 'required|date|after_or_equal:valid_from',
            'active'           => 'boolean',
            'product_ids'      => 'nullable|array',
            'product_ids.*'    => 'exists:products,id',
        ]);

        $promotion = Promotion::create([
            'name'             => $validated['name'],
            'description'      => $validated['description'] ?? null,
            'discount_amount'  => $validated['discount_amount'] ?? null,
            'discount_percent' => $validated['discount_percent'] ?? null,
            'valid_from'       => $validated['valid_from'],
            'valid_until'      => $validated['valid_until'],
            'active'           => $validated['active'] ?? true,
        ]);

        $promotion->products()->sync($validated['product_ids'] ?? []);

        return redirect()->route('admin.promotions.index')->with('success', 'Aanbieding aangemaakt.');
    }

    public function edit(Promotion $promotion): Response
    {
        return Inertia::render('Admin/Promotions/Form', [
            'promotion' => $promotion->load('products'),
            'products'  => Product::where('active', true)
                ->orderByRaw("CAST(menu_number AS INTEGER), LENGTH(menu_number), menu_number")
                ->get(['id', 'menu_number', 'name', 'price']),
        ]);
    }

    public function update(Request $request, Promotion $promotion): RedirectResponse
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:200',
            'description'      => 'nullable|string',
            'discount_amount'  => 'nullable|numeric|min:0',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'valid_from'       => 'required|date',
            'valid_until'      => 'required|date|after_or_equal:valid_from',
            'active'           => 'boolean',
            'product_ids'      => 'nullable|array',
            'product_ids.*'    => 'exists:products,id',
        ]);

        $promotion->update([
            'name'             => $validated['name'],
            'description'      => $validated['description'] ?? null,
            'discount_amount'  => $validated['discount_amount'] ?? null,
            'discount_percent' => $validated['discount_percent'] ?? null,
            'valid_from'       => $validated['valid_from'],
            'valid_until'      => $validated['valid_until'],
            'active'           => $validated['active'] ?? true,
        ]);

        $promotion->products()->sync($validated['product_ids'] ?? []);

        return redirect()->route('admin.promotions.index')->with('success', 'Aanbieding bijgewerkt.');
    }

    public function destroy(Promotion $promotion): RedirectResponse
    {
        $promotion->products()->detach();
        $promotion->delete();

        return redirect()->route('admin.promotions.index')->with('success', 'Aanbieding verwijderd.');
    }
}
