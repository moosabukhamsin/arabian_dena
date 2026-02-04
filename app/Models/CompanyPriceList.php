<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyPriceList extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'daily_price' => 'decimal:2',
        'weekly_price' => 'decimal:2',
        'monthly_price' => 'decimal:2',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
