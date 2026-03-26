<?php

namespace App\Notifications;

use App\Models\ProductItem;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ProductItemCertificationExpiredNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public ProductItem $productItem)
    {
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
            'title' => 'Product item certification expired',
            'message' => "Certification is expired for product item #{$this->productItem->id} ({$this->productItem->series_number}).",
            'product_item_id' => $this->productItem->id,
            'series_number' => $this->productItem->series_number,
            'product_name' => optional($this->productItem->product)->name,
            'inspection_date' => $this->productItem->inspection_date,
            'url' => route('dashboard.product_item', $this->productItem),
        ];
    }
}
