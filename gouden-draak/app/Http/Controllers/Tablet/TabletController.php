<?php

namespace App\Http\Controllers\Tablet;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\HelpRequest;
use App\Models\Order;
use App\Models\Table;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * US-1:  Klant plaatst bestelling via tablet.
 * US-5:  Klant vraagt hulp via tablet.
 */
class TabletController extends Controller
{
    public function index(Table $table): Response
    {
        $openOrder = $table->openOrder();

        return Inertia::render('Tablet/Index', [
            'table'      => $table,
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
                        'description'   => $p->description,
                        'price'         => (float) $p->price,
                        'current_price' => (float) $p->current_price,
                        'image'         => $p->image ? asset('storage/' . $p->image) : null,
                    ]),
                ]),
            'openOrder'  => $openOrder?->load('items.product'),
            'canOrder'   => ! $openOrder || $openOrder->canPlaceNewRound(),
            'roundsLeft' => $openOrder ? (5 - $openOrder->round) : 5,
        ]);
    }

    public function order(Request $request, Table $table): JsonResponse
    {
        $validated = $request->validate([
            'items'               => 'required|array|min:1',
            'items.*.product_id'  => 'required|exists:products,id',
            'items.*.quantity'    => 'required|integer|min:1|max:20',
        ]);

        $openOrder = $table->openOrder();

        if ($openOrder && ! $openOrder->canPlaceNewRound()) {
            return response()->json([
                'message' => 'U kunt pas over 10 minuten of het maximum is bereikt.',
            ], 422);
        }

        // Nieuwe ronde of nieuwe order
        if ($openOrder) {
            $openOrder->increment('round');
            $order = $openOrder;
        } else {
            $order = Order::create([
                'table_id' => $table->id,
                'source'   => 'tablet',
                'status'   => 'open',
                'round'    => 1,
            ]);
        }

        foreach ($validated['items'] as $item) {
            $product = \App\Models\Product::findOrFail($item['product_id']);
            \App\Models\OrderItem::create([
                'order_id'   => $order->id,
                'product_id' => $product->id,
                'quantity'   => $item['quantity'],
                'unit_price' => $product->current_price,
            ]);
        }

        $order->last_order_at = now();
        $order->recalculateTotal();

        return response()->json([
            'order'       => $order->load('items.product'),
            'rounds_left' => 5 - $order->round,
        ], 201);
    }

    /**
     * US-5: Hulp aanvragen vanaf tablet.
     */
    public function requestHelp(Request $request, Table $table): JsonResponse
    {
        $type = $request->input('type', 'hulp'); // 'hulp' of 'betalen'

        $exists = HelpRequest::where('table_id', $table->id)
            ->where('status', 'open')
            ->where('type', $type)
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Verzoek al ingediend.'], 409);
        }

        $help = HelpRequest::create([
            'table_id' => $table->id,
            'type'     => $type,
            'status'   => 'open',
        ]);

        return response()->json(['message' => 'Verzoek ingediend.', 'id' => $help->id], 201);
    }

    /**
     * Markeer de open bestelling van een tafel als betaald.
     */
    public function markPaid(Table $table): JsonResponse
    {
        $openOrder = $table->openOrder();

        if (! $openOrder) {
            return response()->json(['message' => 'Geen open bestelling gevonden.'], 404);
        }

        $openOrder->update(['status' => 'betaald']);

        return response()->json(['message' => 'Bestelling gemarkeerd als betaald.'], 200);
    }
}
