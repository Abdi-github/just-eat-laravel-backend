<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\User\Services\UserService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function __construct(private readonly UserService $service) {}

    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'status']);
        $users = $this->service->paginateWithCounts($filters);

        return Inertia::render('User/Index', [
            'users'   => $users,
            'filters' => $filters,
        ]);
    }

    public function show(int $id): Response
    {
        $user = $this->service->findByIdWithRelations($id, ['orders', 'reviews', 'favorites']);

        abort_unless($user, 404);

        return Inertia::render('User/Show', [
            'user' => $user,
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'is_active' => ['boolean'],
        ]);

        $this->service->update($id, $validated);

        return back()->with('success', 'User updated.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->service->delete($id);

        return redirect()->route('admin.users.index')->with('success', 'User deleted.');
    }
}
