<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductItemCertification extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    public function ProductItem()
    {
        return $this->belongsTo(ProductItem::class);
    }
    
}
