<?php

namespace App\Http\Controllers;

use App\Http\Requests\Wallet\Purchase;
use App\Http\Resources\WalletTransaction\Resource as WalletTransactionResource;
use App\Services\WalletService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WalletController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private WalletService $walletService) {}

    public function index(Request $request): Response
    {
        $this->authorize('useWallet');

        return Inertia::render('wallet/Index', [
            'transactions' => WalletTransactionResource::collection($this->walletService->collection($request->user())),
            'packages' => config('site.credit_packages'),
        ]);
    }

    public function store(Purchase $request): RedirectResponse
    {
        $this->authorize('useWallet');

        $this->walletService->purchase($request->user(), $request->validated('amount'));

        return back();
    }
}
