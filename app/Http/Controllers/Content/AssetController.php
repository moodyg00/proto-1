<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use App\Repositories\Content\ContentRepository;
use Inertia\Inertia;
use Inertia\Response;

class AssetController extends Controller
{
    public function __construct(private readonly ContentRepository $repository)
    {
    }

    public function index(): Response
    {
        return Inertia::render('Content/Assets/Index', [
            'assets' => $this->repository->assetsPaginated(),
        ]);
    }
}
