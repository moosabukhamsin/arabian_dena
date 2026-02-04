<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Backload extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    public function Company()
    {
        return $this->belongsTo(Company::class);
    }
    public function BackloadItems()
    {
        return $this->hasMany(BackloadItem::class);
    }

}
