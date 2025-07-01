<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodsReceiptNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'receipt_date',
        'received_by_user_id',
        'status'
    ];

    // Relationships
    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by_user_id');
    }
}
