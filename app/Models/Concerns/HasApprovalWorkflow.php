<?php

namespace App\Models\Concerns;

use App\Enums\ApprovalStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasApprovalWorkflow
{
    public function modifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'modified_by_id');
    }

    public function authorizedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'authorized_by_id');
    }

    public function isPendingApproval(): bool
    {
        return $this->approval_status === ApprovalStatus::Pending->value;
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->whereNull('approval_status')
                ->orWhere('approval_status', ApprovalStatus::Approved->value);
        });
    }

    public function scopePendingApproval(Builder $query): Builder
    {
        return $query->where('approval_status', ApprovalStatus::Pending->value);
    }

    public static function pendingApprovalAttributes(): array
    {
        return [
            'approval_status' => ApprovalStatus::Pending->value,
            'authorized_by_id' => null,
        ];
    }
}
