<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItemCombination extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    public function OrderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
