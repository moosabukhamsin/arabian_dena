<?php

namespace App\Notifications;

use App\Models\ProductItem;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ProductItemInactive90DaysNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public ProductItem $productItem,
        public string $referenceDate
    ) {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Equipment inactive for 90+ days',
            'message' => "Product item #{$this->productItem->id} ({$this->productItem->series_number}) has been inactive for 90 days or more.",
            'product_item_id' => $this->productItem->id,
            'series_number' => $this->productItem->series_number,
            'product_name' => optional($this->productItem->product)->name,
            'reference_date' => $this->referenceDate,
            'url' => route('dashboard.product_item', $this->productItem),
        ];
    }
}
