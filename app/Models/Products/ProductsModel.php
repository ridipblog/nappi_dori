<?php

namespace App\Models\Products;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class ProductsModel extends Model
{
    use HasFactory;
    protected $table = 'products';
    protected $fillable = [
        'product_name',
        'product_desc',
        'actual_price',
        'purchase_price',
        'discount_amount',
        'total_stock',
        'out_of_stock',
        'category_id',
        'item_photo',
    ];
    protected $encrypted=false;
    public function getIdAttribute($value){
        return $this->encrypted ? Crypt::encryptString($value) : $value;
    }
    public function enabledEncryption(){
        $this->encrypted=true;
        return $this;
    }
    public function user_add_items(){
        return $this->hasMany(USerAddItemsModel::class,'product_id');
    }
}
