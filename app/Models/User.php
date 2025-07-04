<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\Models\Role as BaseRole;
use Spatie\Permission\Models\Permission as BasePermission;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable, HasRoles, SoftDeletes, LogsActivity;

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    protected $fillable = [
        'name',
        'username',
        'password',
        'email',
        'role',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll();
    }

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

class Role extends BaseRole
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}

class Permission extends BasePermission
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
