<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_name',
        'contact_person',
        'email',
        'phone',
        'address',
        'city',
        'country'
    ];

    // Relationships
    public function salesOrders()
    {
        return $this->hasMany(SalesOrder::class);
    }

    public function quotations()
    {
        return $this->hasMany(Quotation::class);
    }

    public function serviceTickets()
    {
        return $this->hasMany(ServiceTicket::class);
    }
}
