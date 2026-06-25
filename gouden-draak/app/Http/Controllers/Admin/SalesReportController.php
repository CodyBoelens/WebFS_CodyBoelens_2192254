<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SalesReport;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * UC-19: Overzicht en download van dagelijkse verkoop rapportages.
 */
class SalesReportController extends Controller
{
    public function index(): Response
    {
        $reports = SalesReport::orderByDesc('report_date')->paginate(30);

        return Inertia::render('Admin/Rapportages', [
            'reports' => $reports,
        ]);
    }

    public function download(SalesReport $report): StreamedResponse
    {
        abort_unless($report->file_path && Storage::exists($report->file_path), 404);

        return Storage::download(
            $report->file_path,
            'rapportage-' . $report->report_date->format('Y-m-d') . '.xlsx'
        );
    }
}
