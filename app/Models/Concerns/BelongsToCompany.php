<?php

namespace App\Models\Concerns;

use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Model;

trait BelongsToCompany
{
    protected static function bootBelongsToCompany(): void
    {
        static::addGlobalScope(new CompanyScope());

        static::creating(function (Model $model): void {
            $tenantKey = config('tenancy.tenant_key', 'company_id');

            if (! $model->getAttribute($tenantKey) && auth()->user()?->company_id) {
                $model->setAttribute($tenantKey, auth()->user()->company_id);
            }
        });
    }

    public function scopeWithoutCompanyScope($query)
    {
        return $query->withoutGlobalScope(CompanyScope::class);
    }
}
