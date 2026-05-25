<?php

namespace App\Models;

use App\Models\Concerns\HasApprovalWorkflow;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasApprovalWorkflow, HasFactory;
    protected $guarded = ['id'];

    protected $casts = [
        'product_ids' => 'array',
        'product_quantities' => 'array',
    ];
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
