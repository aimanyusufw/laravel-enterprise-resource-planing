<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'product_id',
        'issue_description',
        'ticket_date',
        'status',
        'assigned_to_user_id',
        'resolution_details'
    ];

    // Relationships
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function assignedToUser()
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }
}
