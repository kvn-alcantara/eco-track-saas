<?php

namespace App\Policies;

use App\Models\CarbonReport;
use App\Models\User;

class CarbonReportPolicy
{
    public function viewAny(User $user): bool
    {
        return (bool) $user->company_id;
    }

    public function view(User $user, CarbonReport $carbonReport): bool
    {
        return $user->company_id === $carbonReport->company_id;
    }

    public function create(User $user): bool
    {
        return (bool) $user->company_id && $user->role->canManageReports();
    }

    public function generate(User $user): bool
    {
        return (bool) $user->company_id && $user->role->canManageReports();
    }

    public function approve(User $user, CarbonReport $carbonReport): bool
    {
        return $user->company_id === $carbonReport->company_id
            && $user->role->canApproveReports();
    }

    public function update(User $user, CarbonReport $carbonReport): bool
    {
        return $user->company_id === $carbonReport->company_id
            && $user->role->canManageReports();
    }

    public function delete(User $user, CarbonReport $carbonReport): bool
    {
        return $user->company_id === $carbonReport->company_id
            && $user->role->canManageReports();
    }
}
