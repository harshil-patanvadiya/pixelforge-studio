<?php

namespace App\Http\Controllers;

use App\Enums\ImageQuality;
use App\Enums\ImageSize;
use App\Http\Requests\Generation\Estimate;
use App\Http\Requests\Generation\Store;
use App\Http\Resources\Generation\Resource as GenerationResource;
use App\Models\Generation;
use App\Services\GenerationService;
use App\Traits\ApiResponser;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GenerationController extends Controller
{
    use ApiResponser;
    use AuthorizesRequests;

    public function __construct(private GenerationService $generationService) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Generation::class);

        return Inertia::render('generations/Index', [
            'generations' => GenerationResource::collection($this->generationService->collection($request->user())),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Generation::class);

        return Inertia::render('generations/Create', [
            'sizes' => collect(ImageSize::cases())->map(fn (ImageSize $size) => [
                'value' => $size->value,
                'label' => $size->label(),
            ]),
            'qualities' => collect(ImageQuality::cases())->map(fn (ImageQuality $quality) => [
                'value' => $quality->value,
                'label' => $quality->label(),
            ]),
        ]);
    }

    public function store(Store $request): RedirectResponse
    {
        $this->authorize('create', Generation::class);

        $this->generationService->store($request->user(), $request->validated());

        return to_route('generations.index');
    }

    public function show(Generation $generation): Response
    {
        $this->authorize('view', $generation);

        return Inertia::render('generations/Show', [
            'generation' => new GenerationResource($generation),
            'costBreakdown' => $this->generationService->costBreakdown($generation),
        ]);
    }

    public function estimate(Estimate $request): JsonResponse
    {
        $this->authorize('create', Generation::class);

        return $this->success($this->generationService->estimate($request->user(), $request->validated()));
    }
}
