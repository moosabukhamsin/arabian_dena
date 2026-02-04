<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\OrderItemCombination;
use App\Models\OrderItem;

class OrderItemCombinationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $orderItems = OrderItem::all();

        if ($orderItems->isEmpty()) {
            $this->command->warn('No order items found. Please run OrderItemSeeder first.');
            return;
        }

        $combinations = [
            [
                'order_id' => $orderItems->first()->order_id,
            ],
            [
                'order_id' => $orderItems->skip(1)->first()->order_id,
            ],
            [
                'order_id' => $orderItems->skip(2)->first()->order_id,
            ],
            [
                'order_id' => $orderItems->skip(3)->first()->order_id,
            ],
            [
                'order_id' => $orderItems->skip(4)->first()->order_id,
            ],
        ];

        foreach ($combinations as $combination) {
            OrderItemCombination::create($combination);
        }
    }
}
