<?php

namespace  App\custom_helpers;

use Exception;

class ProductHelper
{
    // ---------------- check product avalaibity -------------------
    public static function checkProductAvaiblity($product_details = null, $number_of_items)
    {
        $message = "product details not found !";
        $available_stock=$product_details->total_stock ?? null;
        $do_process = false;
        if ($product_details) {
            if (($product_details->total_stock === 0 || $product_details->out_of_stock === 0) ? true : ($product_details->total_stock == null ? false : ($product_details->total_stock >= $number_of_items ? false : true))) {
                $message = "The product is now out stock !";
                if ($product_details->total_stock && ($product_details->total_stock < $number_of_items)) {
                    $message = "the amount of stock is only $product_details->total_stock !";
                }
            } else {
                $message = null;
                $do_process = true;
            }
        }
        return [$message, $available_stock,$do_process];
    }
    // ---------------------- calculate total_stock and check out_of_stock ------------------
    public static function calculateProductStock($product_details, $number_of_items)
    {
        $total_stock = null;
        $out_of_stock = 1;
        if ($product_details->total_stock !== null ? ($product_details->total_stock !== 0 ? true : false) : false) {
            $total_stock = $product_details->total_stock - $number_of_items;
            if ($total_stock === 0) {
                $out_of_stock = 0;
            }
        }
        return [$total_stock, $out_of_stock];
    }
}
