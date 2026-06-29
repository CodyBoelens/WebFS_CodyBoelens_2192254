<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

/**
 * US-10: Geeft de meest gebruikte opmerkingen terug vanuit de database.
 */
class NotePresetsController extends Controller
{
    public function index(): JsonResponse
    {
        $notes = OrderItem::select('note', DB::raw('COUNT(*) as count'))
            ->whereNotNull('note')
            ->where('note', '!=', '')
            ->groupBy('note')
            ->orderByDesc('count')
            ->limit(8)
            ->pluck('note');

        return response()->json($notes);
    }
}
