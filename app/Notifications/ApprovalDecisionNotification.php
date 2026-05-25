<?php

namespace App\Notifications;

use App\Models\User;
use App\Support\ApprovableEntity;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notification;

class ApprovalDecisionNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Model $entity,
        public string $decision,
        public User $reviewer,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $entityType = ApprovableEntity::typeFromModel($this->entity);
        $typeLabel = ApprovableEntity::typeLabel($entityType);
        $displayName = ApprovableEntity::displayName($this->entity);
        $approved = $this->decision === 'approved';

        return [
            'title' => $approved
                ? "{$typeLabel} approved"
                : "{$typeLabel} rejected",
            'message' => $approved
                ? "{$this->reviewer->name} approved your {$typeLabel} \"{$displayName}\"."
                : "{$this->reviewer->name} rejected your {$typeLabel} \"{$displayName}\".",
            'entity_type' => $entityType,
            'entity_id' => $this->entity->id,
            'decision' => $this->decision,
            'reviewer_name' => $this->reviewer->name,
            'entity_label' => $displayName,
            'url' => $approved ? ApprovableEntity::viewUrl($this->entity, $entityType) : null,
        ];
    }
}
