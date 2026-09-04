<?php

namespace App\Models\Concerns;

use App\Enums\AuditAction;
use App\Services\AuditService;
use Illuminate\Database\Eloquent\Model;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function (Model $model): void {
            app(AuditService::class)->record(AuditAction::Created, $model);
        });

        static::updated(function (Model $model): void {
            if ($model->wasChanged()) {
                app(AuditService::class)->record(AuditAction::Updated, $model);
            }
        });

        static::deleted(function (Model $model): void {
            app(AuditService::class)->record(AuditAction::Deleted, $model);
        });
    }
}
