<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

/**
 * UC-16: Klant geeft afhaal bestelling door via de website.
 * Bedanktpagina bevat een printbare QR Code met bestelnummer en gerechten.
 */
class TakeawayController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_name'       => 'required|string|max:100',
            'customer_phone'      => 'required|string|max:20',
            'items'               => 'required|array|min:1',
            'items.*.product_id'  => 'required|exists:products,id',
            'items.*.quantity'    => 'required|integer|min:1',
        ]);

        $order = Order::create([
            'source'         => 'website',
            'status'         => 'open',
            'customer_name'  => $validated['customer_name'],
            'customer_phone' => $validated['customer_phone'],
            'round'          => 1,
        ]);

        foreach ($validated['items'] as $item) {
            $product = \App\Models\Product::findOrFail($item['product_id']);
            \App\Models\OrderItem::create([
                'order_id'   => $order->id,
                'product_id' => $product->id,
                'quantity'   => $item['quantity'],
                'unit_price' => $product->current_price,
            ]);
        }

        $order->recalculateTotal();

        return redirect()->route('bestellen.bedankt', $order);
    }

    public function bedankt(Order $order): Response
    {
        // Bouw QR-inhoud: bestelnummer + alle gerechtnummers en namen
        $items = $order->items()->with('product')->get();
        $qrContent = "Bestelling #{$order->id}\n";
        foreach ($items as $item) {
            $qrContent .= "{$item->product->menu_number} - {$item->product->name} x{$item->quantity}\n";
        }

        $qrCode = base64_encode(QrCode::format('svg')->size(250)->generate($qrContent));

        return Inertia::render('Website/Bedankt', [
            'order'  => $order->load('items.product'),
            'qrCode' => $qrCode,
        ]);
    }
}
