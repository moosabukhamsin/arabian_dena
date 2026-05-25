<?php

namespace App\Notifications;

use App\Models\User;
use App\Support\ApprovableEntity;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notification;

class ApprovalPendingNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Model $entity,
        public string $action,
        public User $actor,
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
        $actionLabel = $this->action === 'created' ? 'created' : 'updated';

        return [
            'title' => "{$typeLabel} pending approval",
            'message' => "{$this->actor->name} {$actionLabel} {$typeLabel} \"{$displayName}\". Review and approve or reject.",
            'entity_type' => $entityType,
            'entity_id' => $this->entity->id,
            'action' => $this->action,
            'actor_name' => $this->actor->name,
            'entity_label' => $displayName,
            'url' => route('dashboard.pending_approvals'),
        ];
    }
}
