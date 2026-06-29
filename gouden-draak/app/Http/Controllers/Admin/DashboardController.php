<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HelpRequest;
use App\Models\Order;
use App\Models\SalesReport;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $today = now()->toDateString();

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'total_orders'        => Order::whereDate('created_at', $today)->count(),
                'revenue_today'       => Order::whereDate('created_at', $today)->where('status', 'betaald')->sum('total'),
                'open_orders_kassa'   => Order::whereIn('status', ['open', 'in_behandeling'])->where('source', 'kassa')->count(),
                'open_orders_tablet'  => Order::whereIn('status', ['open', 'in_behandeling'])->where('source', 'tablet')->count(),
                'open_orders_website' => Order::whereIn('status', ['open', 'in_behandeling'])->where('source', 'website')->count(),
                'open_help_requests'  => HelpRequest::where('status', 'open')->count(),
            ],
        ]);
    }
}
