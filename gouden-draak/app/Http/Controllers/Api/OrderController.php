<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $orders = Order::with(['items.product', 'table'])
            ->when($request->query('table_id'), fn($q, $id) => $q->where('table_id', $id))
            ->when($request->query('status'), fn($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(20);

        return response()->json($orders);
    }

    public function show(Order $order): JsonResponse
    {
        return response()->json($order->load(['items.product', 'table']));
    }

    /**
     * US-1: Nieuwe bestelling aanmaken (tablet of kassa).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'source'          => 'required|in:tablet,kassa,website',
            'table_id'        => 'nullable|exists:tables,id',
            'customer_name'   => 'nullable|string|max:100',
            'customer_phone'  => 'nullable|string|max:20',
            'items'           => 'required|array|min:1',
            'items.*.product_id'  => 'required|exists:products,id',
            'items.*.quantity'    => 'required|integer|min:1',
            'items.*.rice_choice' => 'nullable|string',
            'items.*.note'        => 'nullable|string|max:255',
        ]);

        // US-1: Controleer of tafel mag bestellen (10 min & max 5 rondes)
        if ($validated['source'] === 'tablet' && $validated['table_id']) {
            $openOrder = Order::where('table_id', $validated['table_id'])
                ->whereIn('status', ['open', 'in_behandeling'])
                ->latest()
                ->first();

            if ($openOrder) {
                if (! $openOrder->canPlaceNewRound()) {
                    return response()->json([
                        'message' => 'U kunt pas over 10 minuten opnieuw bestellen, of het maximum van 5 rondes is bereikt.',
                    ], 422);
                }
                // Nieuwe ronde op bestaande order
                $order = $openOrder;
                $order->increment('round');
            }
        }

        if (! isset($order)) {
            $order = Order::create([
                'source'         => $validated['source'],
                'table_id'       => $validated['table_id'] ?? null,
                'customer_name'  => $validated['customer_name'] ?? null,
                'customer_phone' => $validated['customer_phone'] ?? null,
                'status'         => 'open',
                'round'          => 1,
            ]);
        }

        foreach ($validated['items'] as $item) {
            $product = Product::findOrFail($item['product_id']);
            OrderItem::create([
                'order_id'    => $order->id,
                'product_id'  => $product->id,
                'quantity'    => $item['quantity'],
                'unit_price'  => $product->current_price,
                'rice_choice' => $item['rice_choice'] ?? null,
                'note'        => $item['note'] ?? null,
            ]);
        }

        $order->last_order_at = now();
        $order->recalculateTotal();

        return response()->json($order->load(['items.product', 'table']), 201);
    }

    /**
     * US-10: Opmerking of rijstkeuze bijwerken op een orderregel.
     */
    public function update(Request $request, Order $order): JsonResponse
    {
        $validated = $request->validate([
            'status'              => 'sometimes|in:open,in_behandeling,gereed,betaald,geannuleerd',
            'items'               => 'sometimes|array',
            'items.*.id'          => 'required_with:items|exists:order_items,id',
            'items.*.note'        => 'nullable|string|max:255',
            'items.*.rice_choice' => 'nullable|string',
        ]);

        if (isset($validated['status'])) {
            $order->update(['status' => $validated['status']]);
        }

        if (isset($validated['items'])) {
            foreach ($validated['items'] as $itemData) {
                $item = OrderItem::find($itemData['id']);
                if ($item && $item->order_id === $order->id) {
                    $item->update([
                        'note'        => $itemData['note'] ?? $item->note,
                        'rice_choice' => $itemData['rice_choice'] ?? $item->rice_choice,
                    ]);
                }
            }
        }

        $order->recalculateTotal();
        return response()->json($order->fresh()->load(['items.product']));
    }
}
