<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorkOrder;

class WorkOrderPolicy
{
    public function viewAny(?User $user): bool
    {
        return $this->hasAnyRole($user, ['admin', 'dispatcher', 'contractor', 'ai_agent']);
    }

    public function view(?User $user, WorkOrder $workOrder): bool
    {
        return $this->viewAny($user);
    }

    public function create(?User $user): bool
    {
        return $this->hasAnyRole($user, ['admin', 'dispatcher', 'ai_agent']);
    }

    public function update(?User $user, WorkOrder $workOrder): bool
    {
        return $this->hasAnyRole($user, ['admin', 'dispatcher', 'contractor', 'ai_agent']);
    }

    public function delete(?User $user, WorkOrder $workOrder): bool
    {
        return $this->hasAnyRole($user, ['admin', 'dispatcher']);
    }

    private function hasAnyRole(?User $user, array $roles): bool
    {
        if (! $user) {
            return true;
        }

        $normalized = str_replace('_', ' ', $user->role ?? '');

        return in_array($normalized, $roles, true);
    }
}
