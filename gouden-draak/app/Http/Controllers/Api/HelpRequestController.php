<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HelpRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HelpRequestController extends Controller
{
    /**
     * US-5: Geef een overzicht van alle open hulpverzoeken (voor backoffice).
     */
    public function index(): JsonResponse
    {
        $requests = HelpRequest::with('table')
            ->where('status', 'open')
            ->orderBy('created_at')
            ->get();

        return response()->json($requests);
    }

    /**
     * US-5: Klant vraagt hulp vanaf tablet.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'table_id' => 'required|exists:tables,id',
        ]);

        // Geen dubbele open verzoeken per tafel
        $existing = HelpRequest::where('table_id', $validated['table_id'])
            ->where('status', 'open')
            ->exists();

        if ($existing) {
            return response()->json(['message' => 'Er staat al een hulpverzoek open voor deze tafel.'], 409);
        }

        $helpRequest = HelpRequest::create([
            'table_id' => $validated['table_id'],
            'status'   => 'open',
        ]);

        return response()->json($helpRequest, 201);
    }

    /**
     * US-5: Ober meldt hulpverzoek af.
     */
    public function dismiss(Request $request, HelpRequest $helpRequest): JsonResponse
    {
        $helpRequest->update([
            'status'     => 'afgemeld',
            'handled_by' => auth()->id(),
            'handled_at' => now(),
        ]);

        return response()->json($helpRequest);
    }
}
