<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Company extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    public function Products()
    {
        return $this->hasMany(Product::class);
    }
    public function CompanyEmployees()
    {
        return $this->hasMany(CompanyEmployee::class);
    }
    public function Orders()
    {
        return $this->hasMany(Order::class);
    }
    public function priceLists()
    {
        return $this->hasMany(CompanyPriceList::class);
    }
    public function OrderItems(): HasManyThrough
    {
        return $this->hasManyThrough(OrderItem::class, Order::class);
    }
    public function Backloads()
    {
        return $this->hasMany(Backload::class);
    }
    public function CompanyItems()
    {
        return $this->hasMany(CompanyItem::class);
    }


}
