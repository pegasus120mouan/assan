<?php

namespace App\Services\Admin;

use App\Enums\Status;
use App\Enums\UserRole;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Validation\ValidationException;

class StaffUserService
{
    public function __construct(private readonly AuditLogger $audit) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): User
    {
        $user = new User;
        $user->forceFill([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => $data['password'],
            'role' => $data['role'],
            'status' => $data['status'],
            'email_verified_at' => now(),
        ])->save();

        $this->audit->record('Utilisateur créé', 'users', $user, null, $this->snapshot($user));

        return $user;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(User $user, array $data, User $actor): User
    {
        $this->assertStaff($user);

        if ($actor->is($user)) {
            $data['role'] = $user->role;
            $data['status'] = $user->status;
        }

        $this->assertNotLastActiveAdmin($user, $data['role'], $data['status']);

        $old = $this->snapshot($user);
        $user->forceFill($data)->save();

        $this->audit->record('Utilisateur modifié', 'users', $user, $old, $this->snapshot($user->refresh()));

        return $user;
    }

    public function delete(User $user): void
    {
        $this->assertStaff($user);
        $this->assertNotLastActiveAdmin($user, UserRole::Manager, Status::Inactive);

        $old = $this->snapshot($user);
        $user->delete();

        $this->audit->record('Utilisateur supprimé', 'users', null, $old);
    }

    private function assertStaff(User $user): void
    {
        if (! $user->isStaff()) {
            abort(404);
        }
    }

    private function assertNotLastActiveAdmin(User $user, UserRole $newRole, Status $newStatus): void
    {
        if (! $user->isAdmin() || ! $user->isActive()) {
            return;
        }

        $losesAdminAccess = $newRole !== UserRole::Admin || $newStatus !== Status::Active;

        if (! $losesAdminAccess) {
            return;
        }

        $hasOtherActiveAdmin = User::query()
            ->whereKeyNot($user->id)
            ->where('role', UserRole::Admin)
            ->where('status', Status::Active)
            ->exists();

        if (! $hasOtherActiveAdmin) {
            throw ValidationException::withMessages([
                'role' => 'Impossible de retirer le dernier administrateur actif.',
            ]);
        }
    }

    /**
     * @return array<string, string>
     */
    private function snapshot(User $user): array
    {
        return [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => (string) $user->phone,
            'role' => $user->role->value,
            'status' => $user->status->value,
        ];
    }
}
