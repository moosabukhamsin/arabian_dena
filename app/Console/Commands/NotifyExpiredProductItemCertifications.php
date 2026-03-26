<?php

namespace App\Console\Commands;

use App\Models\ProductItem;
use App\Models\User;
use App\Notifications\ProductItemCertificationExpiredNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class NotifyExpiredProductItemCertifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'product-items:notify-expired-certifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send database notifications for product items with expired certification';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $today = Carbon::today();
        $users = User::all();

        if ($users->isEmpty()) {
            $this->info('No users found to notify.');
            return self::SUCCESS;
        }

        $expiredItems = ProductItem::with('product')
            ->where('is_active', true)
            ->whereNotNull('inspection_date')
            ->whereNull('certification_expired_notified_at')
            ->get()
            ->filter(function (ProductItem $productItem) use ($today) {
                return Carbon::parse($productItem->inspection_date)->addYear()->lessThanOrEqualTo($today);
            });

        $notifiedCount = 0;

        foreach ($expiredItems as $productItem) {
            foreach ($users as $user) {
                $user->notify(new ProductItemCertificationExpiredNotification($productItem));
            }

            $productItem->update([
                'certification_expired_notified_at' => now(),
            ]);

            $notifiedCount++;
        }

        $this->info("Expired certification notifications sent for {$notifiedCount} product item(s).");

        return self::SUCCESS;
    }
}
