<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class QualityControlCheck extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'product_id',
        'batch_number',
        'check_date',
        'checked_by_user_id',
        'result',
        'notes'
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'product_id',
                'batch_number',
                'check_date',
                'checked_by_user_id',
                'result',
                'notes'
            ]);
    }

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
