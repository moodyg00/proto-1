<?php

namespace App\Policies;

use App\Models\Lead;
use App\Models\User;

class LeadPolicy
{
    public function viewAny(?User $user): bool
    {
        return $this->hasAnyRole($user, ['admin', 'dispatcher', 'ai_agent']);
    }

    public function view(?User $user, Lead $lead): bool
    {
        return $this->viewAny($user);
    }

    public function create(?User $user): bool
    {
        return $this->hasAnyRole($user, ['admin', 'dispatcher', 'ai_agent']);
    }

    public function update(?User $user, Lead $lead): bool
    {
        return $this->hasAnyRole($user, ['admin', 'dispatcher', 'ai_agent']);
    }

    public function delete(?User $user, Lead $lead): bool
    {
        return $this->hasAnyRole($user, ['admin']);
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
