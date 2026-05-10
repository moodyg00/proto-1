<?php

namespace App\Http\Controllers\Banking;

use App\Http\Controllers\Controller;
use App\Http\Requests\Banking\CreateReconciliationAdjustmentRequest;
use App\Http\Requests\Banking\MatchReconciliationTransactionRequest;
use App\Http\Requests\Banking\StoreBankReconciliationRequest;
use App\Models\BankAccount;
use App\Models\BankReconciliation;
use App\Models\BankTransaction;
use App\Repositories\Banking\BankingRepository;
use App\Services\Banking\BankingService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class BankReconciliationController extends Controller
{
    public function __construct(
        private readonly BankingRepository $repository,
        private readonly BankingService $service
    ) {
    }

    public function index(): Response
    {
        return Inertia::render('Banking/Reconciliations/Index', [
            'reconciliations' => $this->repository->reconciliationsPaginated(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Banking/Reconciliations/Create', [
            'accounts' => BankAccount::query()->orderBy('name')->get(['id', 'name', 'current_balance']),
        ]);
    }

    public function store(StoreBankReconciliationRequest $request): RedirectResponse
    {
        $reconciliation = $this->service->createReconciliation($request->validated(), $request->user()?->id);

        return redirect()->route('banking.reconciliations.show', $reconciliation)->with('success', 'Reconciliation started.');
    }

    public function show(BankReconciliation $reconciliation): Response
    {
        return Inertia::render('Banking/Reconciliations/Show', [
            'reconciliation' => $reconciliation,
            'unreconciledTransactions' => BankTransaction::query()
                ->where('bank_account_id', $reconciliation->bank_account_id)
                ->whereDate('transaction_date', '<=', $reconciliation->statement_date)
                ->where('status', '!=', 'reconciled')
                ->orderByDesc('transaction_date')
                ->get(),
            'matchedTransactions' => BankTransaction::query()
                ->where('bank_account_id', $reconciliation->bank_account_id)
                ->whereDate('transaction_date', '<=', $reconciliation->statement_date)
                ->where('status', 'reconciled')
                ->orderByDesc('transaction_date')
                ->get(),
        ]);
    }

    public function matchTransaction(MatchReconciliationTransactionRequest $request, BankReconciliation $reconciliation): RedirectResponse
    {
        $transaction = BankTransaction::query()->findOrFail($request->validated('transaction_id'));
        $this->service->matchTransaction($reconciliation, $transaction, $request->user()?->id);

        return back()->with('success', 'Transaction matched.');
    }

    public function createAdjustment(CreateReconciliationAdjustmentRequest $request, BankReconciliation $reconciliation): RedirectResponse
    {
        $this->service->createAdjustment($reconciliation, $request->validated(), $request->user()?->id);

        return back()->with('success', 'Adjustment created.');
    }

    public function complete(BankReconciliation $reconciliation): RedirectResponse
    {
        try {
            $this->service->completeReconciliation($reconciliation, request()->user()?->id);
        } catch (\RuntimeException $exception) {
            return back()->withErrors(['reconciliation' => $exception->getMessage()]);
        }

        return back()->with('success', 'Reconciliation completed.');
    }
}
