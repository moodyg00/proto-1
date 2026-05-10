<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Repositories\Accounting\AccountingRepository;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    public function __construct(private readonly AccountingRepository $repository)
    {
    }

    public function index(): Response
    {
        // Implements Payments Index from accounting-views-and-actions.md
        return Inertia::render('Accounting/Payments/Index', [
            'payments' => $this->repository->paymentsPaginated(),
        ]);
    }
}
