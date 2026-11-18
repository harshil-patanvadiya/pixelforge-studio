<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\Report;
use App\Services\DashboardService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(private DashboardService $dashboardService) {}

    public function __invoke(Report $request): Response
    {
        if ($request->user()->cannot('viewAdminReport')) {
            return Inertia::render('Dashboard', [
                'stats' => $this->dashboardService->userStats($request->user()),
            ]);
        }

        return Inertia::render('admin/Report', [
            'filters' => ['start_date' => $request->startDate(), 'end_date' => $request->endDate()],
            'report' => $this->dashboardService->adminReport($request->startDate(), $request->endDate()),
        ]);
    }
}
