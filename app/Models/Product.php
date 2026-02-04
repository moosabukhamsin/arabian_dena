<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    public function Category()
    {
        return $this->belongsTo(Category::class);
    }
    public function ProductItems()
    {
        return $this->hasMany(ProductItem::class);
    }
    public function companyPriceLists()
    {
        return $this->hasMany(CompanyPriceList::class);
    }
    public function Orders()
    {
        return $this->belongsToMany(Order::class, 'order_items');
    }

}
