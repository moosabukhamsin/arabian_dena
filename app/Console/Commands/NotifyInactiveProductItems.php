<?php

namespace App\Console\Commands;

use App\Models\ProductItem;
use App\Models\User;
use App\Notifications\ProductItemInactive90DaysNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class NotifyInactiveProductItems extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'product-items:notify-inactive-90-days';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send database notifications for product items inactive for 90 days or more';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $users = User::all();

        if ($users->isEmpty()) {
            $this->info('No users found to notify.');
            return self::SUCCESS;
        }

        $thresholdDate = now()->subDays(90)->toDateString();

        $itemRows = ProductItem::query()
            ->leftJoinSub(
                DB::table('order_items as oi')
                    ->leftJoin('backload_items as bi', 'bi.order_item_id', '=', 'oi.id')
                    ->leftJoin('backloads as b', 'b.id', '=', 'bi.backload_id')
                    ->selectRaw('oi.product_item_id, COUNT(oi.id) as order_count, MAX(b.date) as last_backload_date')
                    ->groupBy('oi.product_item_id'),
                'activity',
                function ($join) {
                    $join->on('activity.product_item_id', '=', 'product_items.id');
                }
            )
            ->where('product_items.is_active', true)
            ->whereNull('product_items.inactive_90d_notified_at')
            ->where(function ($query) use ($thresholdDate) {
                $query
                    // Never ordered: inactivity from product item creation date
                    ->where(function ($sub) use ($thresholdDate) {
                        $sub->whereNull('activity.order_count')
                            ->whereDate('product_items.created_at', '<=', $thresholdDate);
                    })
                    // Ordered + backloaded: inactivity from latest backload date
                    ->orWhere(function ($sub) use ($thresholdDate) {
                        $sub->where('activity.order_count', '>', 0)
                            ->whereNotNull('activity.last_backload_date')
                            ->whereDate('activity.last_backload_date', '<=', $thresholdDate);
                    });
            })
            ->selectRaw('product_items.id, product_items.created_at, activity.order_count, activity.last_backload_date')
            ->get();

        if ($itemRows->isEmpty()) {
            $this->info('No inactive product items found.');
            return self::SUCCESS;
        }

        $productItems = ProductItem::with('product')
            ->whereIn('id', $itemRows->pluck('id')->all())
            ->get()
            ->keyBy('id');

        $notifiedIds = [];

        foreach ($itemRows as $row) {
            $productItem = $productItems->get($row->id);
            if (!$productItem) {
                continue;
            }

            $referenceDate = $row->last_backload_date ?: Carbon::parse($row->created_at)->toDateString();

            foreach ($users as $user) {
                $user->notify(new ProductItemInactive90DaysNotification($productItem, (string) $referenceDate));
            }

            $notifiedIds[] = $productItem->id;
        }

        if (!empty($notifiedIds)) {
            ProductItem::whereIn('id', $notifiedIds)->update([
                'inactive_90d_notified_at' => now(),
            ]);
        }

        $this->info('Inactive 90-day notifications sent for ' . count($notifiedIds) . ' product item(s).');

        return self::SUCCESS;
    }
}
