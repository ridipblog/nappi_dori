<?php

namespace App\Models\Products;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class USerAddItemsModel extends Model
{
    use HasFactory;
    protected $table = 'user_add_items';
    protected $fillable = [
        'user_id',
        'product_id',
        'price_at_purchase',
        'total_item',
        'cart_status',
        'purchase_status',
        'delivery_status',
        'rejection_reason',
    ];
}
