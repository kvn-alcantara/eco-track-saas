<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WasteRecord;

class WasteRecordPolicy
{
    public function viewAny(User $user): bool
    {
        return (bool) $user->company_id;
    }

    public function view(User $user, WasteRecord $wasteRecord): bool
    {
        return $user->company_id === $wasteRecord->company_id;
    }

    public function create(User $user): bool
    {
        return (bool) $user->company_id && $user->role->canCreateWaste();
    }

    public function update(User $user, WasteRecord $wasteRecord): bool
    {
        return $user->company_id === $wasteRecord->company_id
            && $user->role->canUpdateWaste();
    }

    public function delete(User $user, WasteRecord $wasteRecord): bool
    {
        return $user->company_id === $wasteRecord->company_id
            && $user->role->canDeleteWaste();
    }
}
