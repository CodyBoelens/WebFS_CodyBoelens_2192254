<?php

namespace App\Http\Controllers\Kassa;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * US-9:  Kassamedewerker zoekt gerechten (naam, nummer, categorie).
 * US-10: Opmerkingen per gerecht toevoegen.
 * US-11: Rijstkeuze per gerecht.
 */
class KassaController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Kassa/Index', [
            'categories' => Category::with(['activeProducts.promotions'])
                ->where('active', true)
                ->orderBy('sort_order')
                ->get()
                ->map(fn($cat) => [
                    'id'   => $cat->id,
                    'name' => $cat->name,
                    'active_products' => $cat->activeProducts->map(fn($p) => [
                        'id'            => $p->id,
                        'menu_number'   => $p->menu_number,
                        'name'          => $p->name,
                        'price'         => (float) $p->price,
                        'current_price' => (float) $p->current_price,
                        'category_id'   => $p->category_id,
                    ]),
                ]),
            'openOrders' => Order::with(['items.product', 'table'])
                ->whereIn('status', ['open', 'in_behandeling'])
                ->where('source', 'kassa')
                ->latest()
                ->get(),
            'riceChoices' => OrderItem::RICE_CHOICES,
        ]);
    }

    public function newOrder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'items'             => 'required|array|min:1',
            'items.*.product_id'  => 'required|exists:products,id',
            'items.*.quantity'    => 'required|integer|min:1',
            'items.*.note'        => 'nullable|string|max:255',
            'items.*.rice_choice' => 'nullable|string',
        ]);

        $order = Order::create(['source' => 'kassa', 'status' => 'open', 'round' => 1]);

        foreach ($validated['items'] as $item) {
            $product = Product::findOrFail($item['product_id']);
            OrderItem::create([
                'order_id'    => $order->id,
                'product_id'  => $product->id,
                'quantity'    => $item['quantity'],
                'unit_price'  => $product->current_price,
                'note'        => $item['note'] ?? null,
                'rice_choice' => $item['rice_choice'] ?? null,
            ]);
        }

        $order->recalculateTotal();

        return redirect()->route('kassa.index')->with('success', "Bestelling #{$order->id} aangemaakt.");
    }

    public function updateItem(Request $request, Order $order, OrderItem $item): RedirectResponse
    {
        abort_unless($item->order_id === $order->id, 403);

        $validated = $request->validate([
            'note'        => 'nullable|string|max:255',
            'rice_choice' => 'nullable|string',
            'quantity'    => 'nullable|integer|min:1',
        ]);

        $item->update($validated);
        $order->recalculateTotal();

        return redirect()->back()->with('success', 'Orderregel bijgewerkt.');
    }

    public function removeItem(Order $order, OrderItem $item): RedirectResponse
    {
        abort_unless($item->order_id === $order->id, 403);
        $item->delete();
        $order->recalculateTotal();

        return redirect()->back()->with('success', 'Gerecht verwijderd van bestelling.');
    }

    public function markPaid(Order $order): RedirectResponse
    {
        $order->update(['status' => 'betaald']);
        return redirect()->route('kassa.index')->with('success', "Bestelling #{$order->id} als betaald gemarkeerd.");
    }
}
