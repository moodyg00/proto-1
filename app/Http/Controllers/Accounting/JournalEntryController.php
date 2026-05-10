<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Repositories\Accounting\AccountingRepository;
use Inertia\Inertia;
use Inertia\Response;

class JournalEntryController extends Controller
{
    public function __construct(private readonly AccountingRepository $repository)
    {
    }

    public function index(): Response
    {
        // Implements Journal Entries Index from accounting-views-and-actions.md
        return Inertia::render('Accounting/JournalEntries/Index', [
            'journalEntries' => $this->repository->journalEntriesPaginated(),
        ]);
    }
}
