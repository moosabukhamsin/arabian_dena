<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductItemCertificate extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function productItem()
    {
        return $this->belongsTo(ProductItem::class);
    }
}

