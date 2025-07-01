<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'username',
        'password',
        'email',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relationships
    public function salesOrders()
    {
        return $this->hasMany(SalesOrder::class, 'user_id');
    }

    public function quotations()
    {
        return $this->hasMany(Quotation::class, 'user_id');
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class, 'user_id');
    }

    public function goodsReceiptNotes()
    {
        return $this->hasMany(GoodsReceiptNote::class, 'received_by_user_id');
    }

    public function workOrders()
    {
        return $this->hasMany(WorkOrder::class, 'user_id');
    }

    public function inventoryTransactions()
    {
        return $this->hasMany(InventoryTransaction::class, 'user_id');
    }

    public function qualityControlChecks()
    {
        return $this->hasMany(QualityControlCheck::class, 'checked_by_user_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'user_id');
    }

    public function serviceTickets()
    {
        return $this->hasMany(ServiceTicket::class, 'assigned_to_user_id');
    }
}
