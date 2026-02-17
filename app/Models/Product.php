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

    /**
     * Get the image URL attribute
     * Handles both storage and public directory images
     * 
     * - Images uploaded via UI: stored in storage/app/public/ with just filename in DB
     *   Example: "fZUPGfA9uleSJHtVz1acDRIYgIu7wrahPAablxY7.jpg"
     *   Returns: asset('storage/fZUPGfA9uleSJHtVz1acDRIYgIu7wrahPAablxY7.jpg')
     * 
     * - Images from seeder: stored in public/assets/images/products/ with full path in DB
     *   Example: "assets/images/products/1.jpg"
     *   Returns: asset('assets/images/products/1.jpg')
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
