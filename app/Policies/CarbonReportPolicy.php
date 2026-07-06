<?php

namespace App\Policies;

use App\Models\User;
use App\Models\CarbonReport;

class CarbonReportPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CarbonReport $carbonReport): bool
    {
        return $user->company_id === $carbonReport->company_id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CarbonReport $carbonReport): bool
    {
        return $user->company_id === $carbonReport->company_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CarbonReport $carbonReport): bool
    {
        return $user->company_id === $carbonReport->company_id;
    }
}
