<?php

namespace  App\custom_helpers;

use Exception;

class ProductHelper
{
    // ---------------- check product avalaibity -------------------
    public static function checkProductAvaiblity($product_details = null, $number_of_items, $current_num_of_items = null)
    {
        $message = "product details not found !";
        $available_stock = $product_details->total_stock ?? null;
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
        return [$message, $available_stock, $do_process];
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
    // -------------- generate query requirements -----------
    public static function generateQueryRequirement($cases,$first_case_value,$second_case_value,$primary_ids,$process_orders)
    {

        $product_quantity = [
            'stock_update_ids' => [],
            'total_stock' => [],
            'out_of_stock' => [],
        ];

        // $total_stock_sql .= "WHEN ? THEN ? ";
        // $product_quantity['case_values'][] = $current_order_details->product_id;
        // $product_quantity['case_values'][] = $total_stock;
        // $out_of_stock_sql .= "WHEN ? THEN ? ";
        // $product_quantity['out_of_stock'][] = $current_order_details->product_id;
        // $product_quantity['out_of_stock'][] = $out_of_stock;
        // $product_quantity['stock_update_ids'][] = $current_order_details->product_id;
        $cases .= "WHEN ? THEN ? ";
        $process_orders['case_values'][] = intval($first_case_value);
        $process_orders['case_values'][] = $second_case_value;
        // $process_orders['add_orders'][] = $grouping_order_ids[$current_order_details->product_id];
        $process_orders['primary_ids'][] = $primary_ids;
        // return [$process_orders,$product_quantity,$cases,$total_stock_sql,$total_stock_sql];
        return [$process_orders,$cases];
    }
    // -------------------- generated sql  ----------------
    public static function generateQuery($cases,$conditional_ids,$require_data,$extra_condition=''){
        // ----------------- generate placeholder where in conditions -----------------
        $condition_order_ids = implode(',', array_fill(0, count($conditional_ids), '?'));
        $product_quantity['case_values']=array_merge($require_data['case_values'],$conditional_ids);
        $sql = " = CASE id " . $cases . " END WHERE id IN ($condition_order_ids) $extra_condition";
        return [$sql,$product_quantity];
    }
}
