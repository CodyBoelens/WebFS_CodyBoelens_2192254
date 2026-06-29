<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HelpRequest;
use Illuminate\Http\RedirectResponse;

class HelpRequestWebController extends Controller
{
    public function dismiss(HelpRequest $helpRequest): RedirectResponse
    {
        $helpRequest->update([
            'status'     => 'afgemeld',
            'handled_by' => auth()->id(),
            'handled_at' => now(),
        ]);

        return redirect()->route('admin.hulpverzoeken')->with('success', 'Hulpverzoek afgemeld.');
    }
}
