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

    /**
     * Get the image URL attribute
     * Handles both storage and public directory images
     * 
     * - Images uploaded via UI: stored in storage/app/public/ with just filename in DB
     *   Example: "fZUPGfA9uleSJHtVz1acDRIYgIu7wrahPAablxY7.jpg"
     *   Returns: asset('storage/fZUPGfA9uleSJHtVz1acDRIYgIu7wrahPAablxY7.jpg')
     * 
     * - Images from seeder: stored in public/assets/images/users/ with full path in DB
     *   Example: "assets/images/users/1.jpg"
     *   Returns: asset('assets/images/users/1.jpg')
     */
    public function getImageUrlAttribute()
    {
        if (!$this->image) {
            return null;
        }

        // If image path starts with 'assets/', it's in public directory (from seeder)
        if (str_starts_with($this->image, 'assets/')) {
            return asset($this->image);
        }

        // Otherwise, it's in storage (uploaded via UI)
        // The database stores just the filename, e.g., "fZUPGfA9uleSJHtVz1acDRIYgIu7wrahPAablxY7.jpg"
        return asset('storage/' . $this->image);
    }

}
