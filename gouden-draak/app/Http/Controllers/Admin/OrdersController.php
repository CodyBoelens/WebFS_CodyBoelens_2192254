<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrdersController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Bestellingen', [
            'orders' => Order::with(['items.product', 'table'])
                ->whereIn('status', ['open', 'in_behandeling', 'gereed'])
                ->orderBy('created_at')
                ->get()
                ->map(fn($o) => [
                    'id'            => $o->id,
                    'source'        => $o->source,
                    'status'        => $o->status,
                    'total'         => (float) $o->total,
                    'customer_name' => $o->customer_name,
                    'table_number'  => $o->table?->number,
                    'created_at'    => $o->created_at->format('H:i'),
                    'items'         => $o->items->map(fn($i) => [
                        'naam'      => $i->product->name,
                        'nummer'    => $i->product->menu_number,
                        'quantity'  => $i->quantity,
                        'note'      => $i->note,
                    ]),
                ]),
        ]);
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:in_behandeling,gereed,betaald,geannuleerd',
        ]);

        $order->update(['status' => $validated['status']]);

        return redirect()->back()->with('success', "Bestelling #{$order->id} bijgewerkt.");
    }
}
