<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'request_date',
        'requested_by_user_id',
        'product_id',
        'quantity',
        'status',
        'notes'
    ];

    // Relationships
    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
