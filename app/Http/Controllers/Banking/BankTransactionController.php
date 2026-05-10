<?php

namespace App\Http\Controllers\Banking;

use App\Http\Controllers\Controller;
use App\Http\Requests\Banking\CategorizeBankTransactionRequest;
use App\Http\Requests\Banking\LinkBankTransactionJournalEntryRequest;
use App\Http\Requests\Banking\StoreBankTransactionRequest;
use App\Http\Requests\Banking\UpdateBankTransactionRequest;
use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\JournalEntry;
use App\Repositories\Banking\BankingRepository;
use App\Services\Banking\BankingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BankTransactionController extends Controller
{
    public function __construct(
        private readonly BankingRepository $repository,
        private readonly BankingService $service
    ) {
    }

    public function index(Request $request): Response
    {
        return Inertia::render('Banking/Transactions/Index', [
            'transactions' => $this->repository->transactionsPaginated($request->only(['status', 'account_id'])),
            'accounts' => BankAccount::query()->orderBy('name')->get(['id', 'name']),
            'filters' => $request->only(['status', 'account_id']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Banking/Transactions/Create', [
            'accounts' => BankAccount::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(StoreBankTransactionRequest $request): RedirectResponse
    {
        $transaction = $this->service->createTransaction($request->validated(), $request->user()?->id);

        return redirect()->route('banking.transactions.show', $transaction)->with('success', 'Bank transaction recorded.');
    }

    public function show(BankTransaction $transaction): Response
    {
        return Inertia::render('Banking/Transactions/Show', [
            'transaction' => $transaction,
            'journalEntries' => JournalEntry::query()->orderByDesc('entry_date')->limit(50)->get(['id', 'entry_number', 'entry_date', 'description']),
            'accounts' => BankAccount::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(UpdateBankTransactionRequest $request, BankTransaction $transaction): RedirectResponse
    {
        $this->service->updateTransaction($transaction, $request->validated(), $request->user()?->id);

        return back()->with('success', 'Transaction updated.');
    }

    public function categorize(CategorizeBankTransactionRequest $request, BankTransaction $transaction): RedirectResponse
    {
        $this->service->categorizeTransaction($transaction, $request->validated(), $request->user()?->id);

        return back()->with('success', 'Transaction categorized.');
    }

    public function linkJournalEntry(LinkBankTransactionJournalEntryRequest $request, BankTransaction $transaction): RedirectResponse
    {
        $this->service->linkJournalEntry($transaction, $request->validated(), $request->user()?->id);

        return back()->with('success', 'Journal entry linked.');
    }

    public function destroy(Request $request, BankTransaction $transaction): RedirectResponse
    {
        try {
            $this->service->deleteManualTransaction($transaction, $request->user()?->id);
        } catch (\RuntimeException $exception) {
            return back()->withErrors(['transaction' => $exception->getMessage()]);
        }

        return redirect()->route('banking.transactions.index')->with('success', 'Transaction deleted.');
    }
}
