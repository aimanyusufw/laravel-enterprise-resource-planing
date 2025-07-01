<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BillOfMaterial extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'component_product_id',
        'quantity'
    ];

    // Relationships
    public function parentProduct()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function componentProduct()
    {
        return $this->belongsTo(Product::class, 'component_product_id');
    }
}
