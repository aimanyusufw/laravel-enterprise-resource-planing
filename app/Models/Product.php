<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_name',
        'description',
        'unit_of_measure',
        'standard_cost',
        'selling_price',
        'stock_on_hand',
        'min_stock_level',
        'max_stock_level'
    ];

    // Relationships
    public function salesOrderItems()
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    public function purchaseOrderItems()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function purchaseRequests()
    {
        return $this->hasMany(PurchaseRequest::class);
    }

    public function workOrders()
    {
        return $this->hasMany(WorkOrder::class);
    }

    // Parent product for BOM
    public function billOfMaterials()
    {
        return $this->hasMany(BillOfMaterial::class, 'product_id');
    }

    // Component products for BOM
    public function usedInBillOfMaterials()
    {
        return $this->hasMany(BillOfMaterial::class, 'component_product_id');
    }

    public function inventoryTransactions()
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    public function qualityControlChecks()
    {
        return $this->hasMany(QualityControlCheck::class);
    }

    public function serviceTickets()
    {
        return $this->hasMany(ServiceTicket::class);
    }
}
