<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_name',
        'contact_person',
        'email',
        'phone',
        'address',
        'city',
        'country'
    ];

    // Relationships
    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }
}
