<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StaffUserRequest;
use App\Models\User;
use App\Services\Admin\StaffUserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(private readonly StaffUserService $staffUsers) {}

    public function index(Request $request): View
    {
        $this->authorize('viewStaff', User::class);

        $users = User::query()
            ->whereIn('role', [UserRole::Admin, UserRole::Manager])
            ->when($request->filled('q'), function ($query) use ($request): void {
                $term = '%'.$request->string('q').'%';
                $query->where(function ($inner) use ($term): void {
                    $inner->where('name', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhere('phone', 'like', $term);
                });
            })
            ->when($request->filled('role'), fn ($query) => $query->where('role', $request->string('role')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $editingId = (int) (old('_edit_modal') ?: $request->integer('edit'));
        $editingUser = $editingId > 0
            ? User::query()
                ->whereKey($editingId)
                ->whereIn('role', [UserRole::Admin, UserRole::Manager])
                ->first()
            : null;

        $modalUsers = $users->getCollection();
        if ($editingUser && ! $modalUsers->contains('id', $editingUser->id)) {
            $modalUsers = $modalUsers->concat([$editingUser]);
        }

        $initialModal = null;
        if (old('_create_modal') || $request->boolean('create')) {
            $initialModal = 'create';
        } elseif ($editingUser) {
            $initialModal = $editingUser->id;
        }

        return view('admin.users.index', [
            'users' => $users,
            'modalUsers' => $modalUsers,
            'filters' => $request->only(['q', 'role', 'status']),
            'initialModal' => $initialModal,
        ]);
    }

    public function create(): RedirectResponse
    {
        $this->authorize('create', User::class);

        return redirect()->route('admin.users.index', ['create' => 1]);
    }

    public function store(StaffUserRequest $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $this->staffUsers->create($request->staffData());

        return redirect()->route('admin.users.index')->with('status', 'Utilisateur créé.');
    }

    public function edit(User $user): RedirectResponse
    {
        abort_unless($user->isStaff(), 404);
        $this->authorize('update', $user);

        return redirect()->route('admin.users.index', ['edit' => $user->id]);
    }

    public function update(StaffUserRequest $request, User $user): RedirectResponse
    {
        abort_unless($user->isStaff(), 404);
        $this->authorize('update', $user);

        $this->staffUsers->update($user, $request->staffData(), $request->user());

        return redirect()->route('admin.users.index')->with('status', 'Utilisateur mis à jour.');
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_unless($user->isStaff(), 404);
        $this->authorize('delete', $user);

        $this->staffUsers->delete($user);

        return redirect()->route('admin.users.index')->with('status', 'Utilisateur supprimé.');
    }
}
