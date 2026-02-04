<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    public function OrderItemCombinations()
    {
        return $this->hasMany(OrderItemCombination::class);
    }
    public function Company()
    {
        return $this->belongsTo(Company::class);
    }
    public function OrderItems()
    {
        return $this->hasMany(OrderItem::class);
    }


}
