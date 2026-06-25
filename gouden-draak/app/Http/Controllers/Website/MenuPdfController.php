<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Promotion;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

/**
 * UC-14: Genereer een up-to-date PDF van het menu vanuit de database.
 * Inclusief aanbiedingen op een aparte pagina.
 */
class MenuPdfController extends Controller
{
    public function __invoke(): Response
    {
        $categories = Category::with(['activeProducts.promotions' => fn($q) => $q->active()])
            ->where('active', true)
            ->orderBy('sort_order')
            ->get();

        $activePromotions = Promotion::active()->with('products')->get();

        $pdf = Pdf::loadView('pdf.menu', compact('categories', 'activePromotions'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('menu-de-gouden-draak.pdf');
    }
}
