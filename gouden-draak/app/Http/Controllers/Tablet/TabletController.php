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
            'categories' => Category::with('activeProducts')->where('active', true)->orderBy('sort_order')->get(),
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
    public function requestHelp(Table $table): JsonResponse
    {
        $exists = HelpRequest::where('table_id', $table->id)->where('status', 'open')->exists();

        if ($exists) {
            return response()->json(['message' => 'Hulpverzoek al ingediend.'], 409);
        }

        $help = HelpRequest::create(['table_id' => $table->id, 'status' => 'open']);

        return response()->json(['message' => 'Hulpverzoek ingediend.', 'id' => $help->id], 201);
    }
}
