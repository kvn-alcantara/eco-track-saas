<?php

namespace App\Scopes;

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
            $builder->whereRaw('1 = 0');

            return;
        }

        config('tenancy.tenant_key', 'company_id')
            |> $model(...)
            |> (fn($x) => $builder->where($x, $companyId));
    }
}
