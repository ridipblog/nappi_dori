<?php

namespace App\Models\Products;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

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
        'purchse_date',
        'delivered_date',
    ];
    protected $encrypted = false;
    public function getIdAttribute($value)
    {
        return $this->encrypted ? Crypt::encryptString($value) : $value;
    }
    public function getProductIdAttribute($value){
        return $this->encrypted ? Crypt::encryptString($value) : $value;
    }
    public function enableEncryptedId()
    {
        $this->encrypted = true;
        return $this;
    }
    public function disableEncryptedId()
    {
        $this->encrypted = false;
        return $this;
    }
    public function products(){
        return $this->belongsTo(ProductsModel::class,'product_id');
    }
}
