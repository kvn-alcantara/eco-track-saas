<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WasteRecord;

class WasteRecordPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, WasteRecord $wasteRecord): bool
    {
        return $user->company_id === $wasteRecord->company_id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, WasteRecord $wasteRecord): bool
    {
        return $user->company_id === $wasteRecord->company_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, WasteRecord $wasteRecord): bool
    {
        return $user->company_id === $wasteRecord->company_id;
    }
}
