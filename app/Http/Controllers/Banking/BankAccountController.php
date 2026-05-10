<?php

namespace App\Http\Controllers\Banking;

use App\Http\Controllers\Controller;
use App\Http\Requests\Banking\StoreBankAccountRequest;
use App\Models\BankAccount;
use App\Services\Banking\BankingService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class BankAccountController extends Controller
{
    public function __construct(private readonly BankingService $service)
    {
    }

    public function index(): Response
    {
        return Inertia::render('Banking/Accounts/Index', [
            'accounts' => BankAccount::query()->orderBy('name')->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Banking/Accounts/Create');
    }

    public function store(StoreBankAccountRequest $request): RedirectResponse
    {
        $this->service->createAccount($request->validated(), $request->user()?->id);

        return redirect()->route('banking.accounts.index')->with('success', 'Bank account created.');
    }
}
