<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

/**
 * UC-20: Admin kan gerechten toevoegen, aanpassen en verwijderen.
 * Nummering blijft gelijk (a, b, c voor varianten; doorlopend voor nieuw).
 */
class MenuController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Menu/Index', [
            'categories' => Category::with('products')->orderBy('sort_order')->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Menu/Form', [
            'categories' => Category::where('active', true)->orderBy('sort_order')->get(['id', 'name']),
            'product'    => null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_id'  => 'required|exists:categories,id',
            'menu_number'  => 'required|string|max:10|unique:products,menu_number',
            'name'         => 'required|string|max:200',
            'description'  => 'nullable|string',
            'price'        => 'required|numeric|min:0',
            'allergens'    => 'nullable|array',
            'image'        => 'nullable|image|max:2048',
            'active'       => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        Product::create($validated);

        return redirect()->route('admin.menu.index')->with('success', 'Gerecht toegevoegd.');
    }

    public function edit(Product $menu): Response
    {
        return Inertia::render('Admin/Menu/Form', [
            'categories' => Category::where('active', true)->orderBy('sort_order')->get(['id', 'name']),
            'product'    => $menu->load('category'),
        ]);
    }

    public function update(Request $request, Product $menu): RedirectResponse
    {
        $validated = $request->validate([
            'category_id'  => 'required|exists:categories,id',
            'menu_number'  => 'required|string|max:10|unique:products,menu_number,' . $menu->id,
            'name'         => 'required|string|max:200',
            'description'  => 'nullable|string',
            'price'        => 'required|numeric|min:0',
            'allergens'    => 'nullable|array',
            'image'        => 'nullable|image|max:2048',
            'active'       => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $menu->update($validated);

        return redirect()->route('admin.menu.index')->with('success', 'Gerecht bijgewerkt.');
    }

    public function destroy(Product $menu): RedirectResponse
    {
        // Soft delete: zet actief op false zodat menunummer bewaard blijft
        $menu->update(['active' => false]);

        return redirect()->route('admin.menu.index')->with('success', 'Gerecht verwijderd.');
    }
}
