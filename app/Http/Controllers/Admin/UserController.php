<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Report;
use App\Http\Resources\Generation\Resource as GenerationResource;
use App\Models\User;
use App\Services\DashboardService;
use App\Services\GenerationService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private DashboardService $dashboardService) {}

    public function index(Report $request): Response
    {
        $this->authorize('viewAdminReport');

        return Inertia::render('admin/Users', [
            'filters' => ['start_date' => $request->startDate(), 'end_date' => $request->endDate()],
            'users' => $this->dashboardService->userReport($request->startDate(), $request->endDate()),
        ]);
    }

    public function show(User $user, GenerationService $generationService): Response
    {
        $this->authorize('viewAdminReport');

        return Inertia::render('admin/UserShow', [
            'user' => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email],
            'generations' => GenerationResource::collection($generationService->collection($user)),
        ]);
    }
}
