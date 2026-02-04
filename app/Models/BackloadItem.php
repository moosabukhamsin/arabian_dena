<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BackloadItem extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    public function Backload()
    {
        return $this->belongsTo(Backload::class);
    }
    public function OrderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }
}
