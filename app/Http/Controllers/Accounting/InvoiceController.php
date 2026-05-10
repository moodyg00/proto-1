<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Accounting\RecordInvoicePaymentRequest;
use App\Http\Requests\Accounting\StoreInvoiceRequest;
use App\Http\Requests\Accounting\UpdateInvoiceRequest;
use App\Models\Contact;
use App\Models\Invoice;
use App\Models\Organization;
use App\Repositories\Accounting\AccountingRepository;
use App\Services\Accounting\AccountingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
{
    public function __construct(
        private readonly AccountingService $service,
        private readonly AccountingRepository $repository
    ) {
    }

    public function index(Request $request): Response
    {
        // Implements Invoices Index from accounting-views-and-actions.md
        return Inertia::render('Accounting/Invoices/Index', [
            'invoices' => $this->repository->invoicesPaginated($request->only(['status'])),
            'filters' => $request->only(['status']),
        ]);
    }

    public function create(): Response
    {
        // Implements Invoice Create from accounting-views-and-actions.md
        return Inertia::render('Accounting/Invoices/Create', [
            'contacts' => Contact::query()->orderBy('name')->get(['id', 'name']),
            'organizations' => Organization::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(StoreInvoiceRequest $request): RedirectResponse
    {
        $invoice = $this->service->createInvoice($request->validated(), $request->user()?->id);

        return redirect()->route('accounting.invoices.show', $invoice)->with('success', 'Invoice created.');
    }

    public function show(Invoice $invoice): Response
    {
        // Implements Invoice Show from accounting-views-and-actions.md
        $invoice->load(['items', 'payments']);

        return Inertia::render('Accounting/Invoices/Show', [
            'invoice' => $invoice,
            'payments' => $invoice->payments,
            'lineItems' => $invoice->items,
        ]);
    }

    public function edit(Invoice $invoice): Response
    {
        // Implements Invoice Edit from accounting-views-and-actions.md
        $invoice->load('items');

        return Inertia::render('Accounting/Invoices/Edit', [
            'invoice' => $invoice,
            'lineItems' => $invoice->items,
            'contacts' => Contact::query()->orderBy('name')->get(['id', 'name']),
            'organizations' => Organization::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice): RedirectResponse
    {
        $updated = $this->service->updateInvoice($invoice, $request->validated(), $request->user()?->id);

        return redirect()->route('accounting.invoices.show', $updated)->with('success', 'Invoice updated.');
    }

    public function destroy(Request $request, Invoice $invoice): RedirectResponse
    {
        if ($invoice->status !== 'draft') {
            return back()->withErrors(['invoice' => 'Only draft invoices can be deleted.']);
        }

        $invoice->delete();

        return redirect()->route('accounting.invoices.index')->with('success', 'Invoice deleted.');
    }

    public function recordPayment(RecordInvoicePaymentRequest $request, Invoice $invoice): RedirectResponse
    {
        $this->service->recordInvoicePayment($invoice, $request->validated(), $request->user()?->id);

        return back()->with('success', 'Payment recorded and invoice status updated automatically.');
    }
}
