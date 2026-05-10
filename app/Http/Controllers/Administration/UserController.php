<?php

namespace App\Http\Controllers\Administration;

use App\Http\Controllers\Controller;
use App\Http\Requests\Administration\StoreUserRequest;
use App\Http\Requests\Administration\UpdateUserRequest;
use App\Models\User;
use App\Services\Administration\AdministrationService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function __construct(private readonly AdministrationService $service) {}

    public function index(): Response
    {
        $data = $this->service->listUsers(request()->only(['search', 'user_type', 'is_active']));
        return Inertia::render('Administration/Users/Index', $data);
    }

    public function create(): Response
    {
        return Inertia::render('Administration/Users/Create');
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $user = $this->service->createUser($request->validated());
        return redirect()->route('administration.users.show', $user)->with('success', 'User created.');
    }

    public function show(User $user): Response
    {
        $data = $this->service->showUser($user->id);
        return Inertia::render('Administration/Users/Show', $data);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->service->updateUser($user, $request->validated());
        return back()->with('success', 'User updated.');
    }

    public function toggleActive(User $user): RedirectResponse
    {
        $this->service->toggleUserActive($user);
        $action = $user->is_active ? 'deactivated' : 'activated';
        return back()->with('success', "User {$action}.");
    }
}
