<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditService
{
    public function record(AuditAction $action, Model $model): void
    {
        $oldValues = null;
        $newValues = null;

        if ($action === AuditAction::Created) {
            $newValues = $this->safeAttributes($model);
        }

        if ($action === AuditAction::Updated) {
            $oldValues = $this->onlyChanged($model->getOriginal(), array_keys($model->getChanges()));
            $newValues = $model->getChanges();
            unset($newValues['updated_at']);
        }

        if ($action === AuditAction::Deleted) {
            $oldValues = $this->safeAttributes($model);
        }

        AuditLog::query()->create([
            'user_id' => Auth::id(),
            'action' => $action,
            'auditable_type' => $model::class,
            'auditable_id' => $model->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function safeAttributes(Model $model): array
    {
        $attributes = $model->attributesToArray();
        unset($attributes['password'], $attributes['remember_token']);

        return $attributes;
    }

    /**
     * @param  array<string, mixed>  $original
     * @param  array<int, string>  $keys
     * @return array<string, mixed>
     */
    private function onlyChanged(array $original, array $keys): array
    {
        return collect($original)
            ->only($keys)
            ->except(['updated_at'])
            ->all();
    }
}
