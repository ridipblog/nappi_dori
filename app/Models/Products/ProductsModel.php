<?php

namespace App\Models\Products;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductsModel extends Model
{
    use HasFactory;
    protected $table = 'products';
    protected $fillable = [
        'product_name',
        'product_desc',
        'price',
        'discount_price',
        'total_stock',
        'out_of_stock',
        'category_id',
        'item_photo',
    ];
}
