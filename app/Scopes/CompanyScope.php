<?php

namespace App\Scopes;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class CompanyScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $companyId = app()->bound('currentCompanyId')
            ? app('currentCompanyId')
            : auth()->user()?->company_id;

        if (! $companyId) {
            // During authentication (e.g. Sanctum retrieving the user), 
            // auth()->user() is not yet available. We should not apply the scope 
            // if we are explicitly looking for a user or if the context is not yet set.
            // However, we want to ensure that for OTHER models, they are NOT accessed without a company.
            if ($model instanceof User) {
                return;
            }

            $builder->whereRaw('1 = 0');

            return;
        }

        $tenantKey = config('tenancy.tenant_key', 'company_id');
        $builder->where($tenantKey, $companyId);
    }
}
