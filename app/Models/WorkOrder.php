<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'sales_order_id',
        'product_id',
        'quantity',
        'start_date',
        'end_date',
        'status',
        'user_id'
    ];

    // Relationships
    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class)->with('customer');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
