<?php

namespace App\Models;

use App\Models\Concerns\HasApprovalWorkflow;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasApprovalWorkflow, HasFactory;
    protected $guarded = ['id'];
    public function Products()
    {
        return $this->hasMany(Product::class);
    }
}
