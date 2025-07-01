<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QualityControlCheck extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'batch_number',
        'check_date',
        'checked_by_user_id',
        'result',
        'notes'
    ];

    // Relationships
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function checker()
    {
        return $this->belongsTo(User::class, 'checked_by_user_id');
    }
}
