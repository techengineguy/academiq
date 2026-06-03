<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Spatie\Multitenancy\Models\Tenant;

trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder): void {
            $tenant = Tenant::current();

            if ($tenant) {
                $builder->where('institution_id', '=', $tenant->getKey());
            }
        });

        static::creating(function (self $model): void {
            $tenant = Tenant::current();

            if ($tenant && empty($model->institution_id)) {
                $model->institution_id = $tenant->getKey();
            }
        });
    }
}
