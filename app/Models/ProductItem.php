<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductItem extends Model
{
    use HasFactory;

    /** Statuses that stop rental auto-updates and operational notifications. */
    public const TERMINAL_STATUSES = ['Lost', 'Scrap'];

    protected $guarded = ['id'];

    public function hasTerminalStatus(): bool
    {
        return in_array($this->status, self::TERMINAL_STATUSES, true);
    }

    public function scopeWithoutTerminalStatus($query)
    {
        return $query->whereNotIn('status', self::TERMINAL_STATUSES);
    }
    public function Product()
    {
        return $this->belongsTo(Product::class);
    }
    public function OrderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function BackloadItems()
    {
        return $this->hasManyThrough(BackloadItem::class, OrderItem::class);
    }

    public function Certificates()
    {
        return $this->hasMany(ProductItemCertificate::class);
    }

}
