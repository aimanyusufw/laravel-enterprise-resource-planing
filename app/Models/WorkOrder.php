<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class WorkOrder extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'sales_order_id',
        'product_id',
        'quantity',
        'start_date',
        'end_date',
        'status',
        'user_id'
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'sales_order_id',
                'product_id',
                'quantity',
                'start_date',
                'end_date',
                'status',
                'user_id'
            ]);
    }

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
