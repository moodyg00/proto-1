<?php

namespace App\Http\Controllers\Banking;

use App\Http\Controllers\Controller;
use App\Http\Requests\Banking\StoreBankTransferRequest;
use App\Models\BankAccount;
use App\Models\BankTransfer;
use App\Repositories\Banking\BankingRepository;
use App\Services\Banking\BankingService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class BankTransferController extends Controller
{
    public function __construct(
        private readonly BankingRepository $repository,
        private readonly BankingService $service
    ) {
    }

    public function index(): Response
    {
        return Inertia::render('Banking/Transfers/Index', [
            'transfers' => $this->repository->transfersPaginated(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Banking/Transfers/Create', [
            'accounts' => BankAccount::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(StoreBankTransferRequest $request): RedirectResponse
    {
        $transfer = $this->service->createTransfer($request->validated(), $request->user()?->id);

        return redirect()->route('banking.transfers.show', $transfer)->with('success', 'Transfer created.');
    }

    public function show(BankTransfer $transfer): Response
    {
        return Inertia::render('Banking/Transfers/Show', [
            'transfer' => $transfer,
            'fromAccount' => BankAccount::query()->find($transfer->from_account_id, ['id', 'name']),
            'toAccount' => BankAccount::query()->find($transfer->to_account_id, ['id', 'name']),
        ]);
    }
}
