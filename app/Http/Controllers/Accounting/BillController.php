<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Accounting\RecordBillPaymentRequest;
use App\Http\Requests\Accounting\StoreBillRequest;
use App\Http\Requests\Accounting\UpdateBillRequest;
use App\Models\Bill;
use App\Models\Organization;
use App\Repositories\Accounting\AccountingRepository;
use App\Services\Accounting\AccountingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BillController extends Controller
{
    public function __construct(
        private readonly AccountingService $service,
        private readonly AccountingRepository $repository
    ) {
    }

    public function index(Request $request): Response
    {
        // Implements Bills Index from accounting-views-and-actions.md
        return Inertia::render('Accounting/Bills/Index', [
            'bills' => $this->repository->billsPaginated($request->only(['status'])),
            'filters' => $request->only(['status']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Accounting/Bills/Create', [
            'vendors' => Organization::query()->whereIn('relationship_type', ['vendor', 'supplier'])->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(StoreBillRequest $request): RedirectResponse
    {
        $bill = $this->service->createBill($request->validated(), $request->user()?->id);

        return redirect()->route('accounting.bills.show', $bill)->with('success', 'Bill created.');
    }

    public function show(Bill $bill): Response
    {
        // Implements Bill Show from accounting-views-and-actions.md
        return Inertia::render('Accounting/Bills/Show', [
            'bill' => $bill,
            'payments' => \App\Models\Payment::query()->where('bill_id', $bill->id)->latest('payment_date')->get(),
        ]);
    }

    public function edit(Bill $bill): Response
    {
        return Inertia::render('Accounting/Bills/Edit', [
            'bill' => $bill,
            'vendors' => Organization::query()->whereIn('relationship_type', ['vendor', 'supplier'])->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(UpdateBillRequest $request, Bill $bill): RedirectResponse
    {
        $bill->update([
            ...$request->validated(),
            'updated_by' => $request->user()?->id,
        ]);

        return redirect()->route('accounting.bills.show', $bill)->with('success', 'Bill updated.');
    }

    public function destroy(Bill $bill): RedirectResponse
    {
        if ($bill->status !== 'draft') {
            return back()->withErrors(['bill' => 'Only draft bills can be deleted.']);
        }

        $bill->delete();

        return redirect()->route('accounting.bills.index')->with('success', 'Bill deleted.');
    }

    public function recordPayment(RecordBillPaymentRequest $request, Bill $bill): RedirectResponse
    {
        $this->service->recordBillPayment($bill, $request->validated(), $request->user()?->id);

        return back()->with('success', 'Payment recorded and bill status updated when fully paid.');
    }
}
