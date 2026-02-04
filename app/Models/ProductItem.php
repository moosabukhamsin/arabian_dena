<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductItem extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    public function Product()
    {
        return $this->belongsTo(Product::class);
    }
    public function OrderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
    public function ProductItemCertifications()
    {
        return $this->hasMany(ProductItemCertification::class);
    }

    public function BackloadItems()
    {
        return $this->hasManyThrough(BackloadItem::class, OrderItem::class);
    }

}
