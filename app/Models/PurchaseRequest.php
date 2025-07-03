<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PurchaseRequest extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'request_date',
        'requested_by_user_id',
        'product_id',
        'quantity',
        'status',
        'notes'
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'request_date',
                'requested_by_user_id',
                'product_id',
                'quantity',
                'status',
                'notes'
            ]);
    }

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
