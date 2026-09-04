<?php

namespace App\Models\Concerns;

use App\Enums\ShiftStatus;
use App\Exceptions\BusinessException;
use App\Models\Shift;
use Illuminate\Database\Eloquent\Model;

trait ImmutableWhenShiftClosed
{
    public static function bootImmutableWhenShiftClosed(): void
    {
        static::updating(function (Model $model): void {
            $model->assertShiftIsMutable();
        });

        static::deleting(function (Model $model): void {
            $model->assertShiftIsMutable();
        });
    }

    protected function assertShiftIsMutable(): void
    {
        if ($this instanceof Shift) {
            $original = $this->getOriginal('status');
            $wasClosed = $original === ShiftStatus::Closed
                || $original === ShiftStatus::Closed->value;

            if ($wasClosed) {
                throw new BusinessException('Records cannot be changed after the shift is closed.');
            }

            return;
        }

        $shift = $this->resolveRelatedShift();

        if ($shift instanceof Shift && $shift->status === ShiftStatus::Closed) {
            throw new BusinessException('Records cannot be changed after the shift is closed.');
        }
    }

    protected function resolveRelatedShift(): ?Shift
    {
        if ($this instanceof Shift) {
            return $this;
        }

        if ($this->relationLoaded('shift')) {
            return $this->getRelation('shift');
        }

        if (isset($this->shift_id) && $this->shift_id) {
            return Shift::query()->find($this->shift_id);
        }

        return null;
    }
}
