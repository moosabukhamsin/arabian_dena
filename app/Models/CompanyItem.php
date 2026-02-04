<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyItem extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    public function Company()
    {
        return $this->belongsTo(Company::class);
    }
    public function ProductItem()
    {
        return $this->belongsTo(ProductItem::class);
    }





}
