<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SalesReport;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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

    public function download(SalesReport $report): BinaryFileResponse
    {
        $fullPath = storage_path('app/' . $report->file_path);

        abort_unless($report->file_path && file_exists($fullPath), 404);

        return response()->download(
            $fullPath,
            'rapportage-' . $report->report_date->format('Y-m-d') . '.xlsx',
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        );
    }
}
