<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    /**
     * UC-13: wissel de taal van de applicatie.
     * Ondersteunde talen: nl, en
     */
    public function __invoke(Request $request, string $locale): RedirectResponse
    {
        if (! in_array($locale, ['nl', 'en'])) {
            abort(400, 'Taal niet ondersteund');
        }

        session(['locale' => $locale]);
        app()->setLocale($locale);

        return redirect()->back();
    }
}
