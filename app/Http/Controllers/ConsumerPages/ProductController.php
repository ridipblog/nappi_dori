<?php

namespace App\Http\Controllers\ConsumerPages;

use App\custom_helpers\ProductHelper;
use App\custom_helpers\reuse_helpers\ReuseHelper;
use App\Http\Controllers\Controller;
use App\Models\Products\ProductsModel;
use App\Models\Products\USerAddItemsModel;
use App\Rules\BuyperProcessRule;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    // --------------- get product lists -----------------
    public function productLists(Request $request)
    {
        $res_data = [
            'message' => null,
            'status' => 400,
            'product_lists' => null,
            'pages' => 0
        ];
        try {
            $take = 10;
            $page = ($request->input('page') ?? 0) * $take;
            $main_query = ProductsModel::query();
            // ------------ search by category ---------------
            if ($request->input('category_id') ? ($request->input('category_id') == 0 ? false : true) : false) {
                $main_query->where('category_id', $request->input('category_id'));
            }
            // -------------- search by work with full text search ---------------
            if ($request->input('search_by')) {
                $search = $request->input('search_by');
                $main_query->whereRaw("MATCH(product_name) AGAINST(? IN BOOLEAN MODE)", ["$search*"]);
                // $main_query->where('product_name','LIKE',"%{$search}%");
            }
            // ------------ search by price tag ----------------
            $price_filters=$request->input('price_filter',[]);
            if(count($price_filters)==2){
                if($price_filters[0]==="between"){
                    $main_query->whereBetween('purchase_price',config("globalConfig.price_filters.$price_filters[0].$price_filters[1]"));
                }else{
                    $filter_key=$price_filters[0] === "less" ?'<' :'>';
                    $main_query->where('purchase_price',$filter_key,config("globalConfig.price_filters.$price_filters[0].$price_filters[1]"));
                }
            }
            $pages = $main_query->count() / $take;
            $res_data['total_products'] = $main_query->count();
            //if necessary retrive category wise data
            // $main_query->groupBy('category_id')
            // ->selectRaw('category_id');

            $main_query->skip($page)->take($take);
            $products = $main_query->get();
            $products->transform(function ($product) {
                $product->enabledEncryption();
                return $product;
            });
            $res_data['product_lists'] = $products;
            $res_data['pages'] = intval($pages);
        } catch (Exception $err) {
            $res_data['message'] = "Server error please try later !";
            $res_data['message'] = $err->getMessage();
        }
        return response()->json(['res_data' => $res_data]);
    }
    // ---------------- get select product ------------------
    public function getProduct(Request $request)
    {
        $res_data = [
            'message' => null,
            'status' => 400
        ];
        try {
            if ($request->input('product')) {
                $product_id = Crypt::decryptString($request->input('product'));
                $product_details = ProductsModel::where('id', $product_id)
                    ->first();
                if ($product_details) {
                    $res_data['status'] = 200;
                    $res_data['product'] = $product_details;
                } else {
                    $res_data['message'] = "Sorry , No product detail found !";
                }
            } else {
                $res_data['message'] = "Required product ID";
            }
        } catch (Exception $err) {
            $res_data['message'] = "Server error please try later !";
        }
        return response()->json(['res_data' => $res_data]);
    }
    // ------------------- product add card ----------------
    public function addCard(Request $request)
    {
        $res_data = [
            'message' => null,
            'status' => 400,
            'do_process' => false,
            'available_stock' => null
        ];
        $product_details = null;
        $logged_user = $request->user('user_guard');
        $incomming_inputs = [
            'number_of_items' => 'required|integer|min:1',
            'product' => 'required'
        ];
        $validate = ReuseHelper::validateIncomingData($request, $incomming_inputs);
        if ($validate->fails()) {
            $res_data['message'] = $validate->errors()->all();
        } else {
            $res_data['status'] = 401;
            try {
                $product_id = Crypt::decryptString($request->product);
                $product_details = ProductsModel::where('id', $product_id)
                    ->first();
                if ($product_details) {
                    [$res_data['message'], $res_data['available_stock'], $res_data['do_process']] = ProductHelper::checkProductAvaiblity($product_details, $request->number_of_items);
                } else {
                    $res_data['message'] = "No product found";
                }
            } catch (Exception $err) {
                $res_data['message'] = "Server error please try later !";
            }
        }
        if ($res_data['do_process']) {
            try {
                DB::beginTransaction();
                [$total_stock, $out_of_stock] = ProductHelper::calculateProductStock($product_details, $request->number_of_items);
                $save_or_update_cart = USerAddItemsModel::updateOrCreate([
                    'user_id' => $logged_user->id,
                    'product_id' => $product_details->id,
                    'cart_status' => 0
                ], [
                    'total_item' => DB::raw("COALESCE(total_item,0)+{$request->number_of_items}")
                ]);
                $update_product = ProductsModel::where('id', $product_id)
                    ->update([
                        'total_stock' => $total_stock,
                        'out_of_stock' => $out_of_stock
                    ]);
                $res_data['available_stock'] = $total_stock;
                $res_data['message'] = "Product added in cart ";
                $res_data['status'] = 200;
                DB::commit();
            } catch (Exception $err) {
                DB::rollBack();
                $res_data['message'] = "Server error please try later !";
            }
        }
        return response()->json(['res_data' => $res_data]);
    }
    // ----------------- show cart products ------------------
    public function cartProducts(Request $request)
    {
        $res_data = [
            'message' => null,
            'status' => 400
        ];
        try {
            $cart_products = USerAddItemsModel::query()->with(['products'])
                ->select(
                    'id',
                    'user_id',
                    'product_id',
                    'total_item'
                )
                ->where([
                    ['user_id', $request->user('user_guard')->id],
                    ['cart_status', 0]
                ])->whereHas('products', function ($query) {})
                ->get();
            $cart_products->transform(function ($product) {
                $product->enableEncryptedId();
                $product->products->enabledEncryption();
                return $product;
            });
            $res_data['cart_products'] = $cart_products;
            $res_data['status'] = 200;
        } catch (Exception $err) {
            $res_data['message'] = "Server error please try later !";
        }
        return response()->json(['res_data' => $res_data]);
    }
    // ---------- buy cart product ---------------
    public function buyerProcess(Request $request)
    {
        $res_data = [
            'message' => null,
            'status' => 400,
            'is_confirmation' => false,
            'is_error' => true
        ];
        $incomming_inputs = [
            'orders' => ['required', 'array'],
            'number_of_items' => ['required', 'array', new BuyperProcessRule('not_in_arr')],

        ];
        $validate = ReuseHelper::validateIncomingData($request, $incomming_inputs);
        if ($validate->fails()) {
            $res_data['message'] = $validate->errors()->all();
        } else {
            $res_data['status'] = 201;
            if ((count($request->orders) == count($request->number_of_items))) {
                $restrick_orders = [];
                $process_orders = [];
                $update_total_stock = [];
                $update_out_of_stock = [];
                $cases = null;
                $total_stock_sql = null;
                $out_of_stock_sql = null;
                try {
                    $logged_user = $request->user('user_guard');
                    $order_ids = array_map(fn($order) => intval(Crypt::decryptString($order)), $request->orders);
                    if (count($order_ids) === count(array_unique($order_ids))) {
                        $orders_details = USerAddItemsModel::query()->with(['products' => function ($query) {
                            $query->select('id', 'actual_price', 'purchase_price', 'total_stock', 'out_of_stock');
                        }])
                            ->where('user_id', $logged_user->id)
                            ->whereIn('id', $order_ids)
                            ->where('cart_status', 0)
                            ->whereHas('products', function ($query) {})
                            ->select('id', 'user_id', 'product_id', 'total_item')
                            ->get();
                        foreach ($order_ids as $order_key => $order_value) {
                            $current_order_details = $orders_details->firstWhere('id', $order_value);
                            [$message, $available_stock, $do_process] = ProductHelper::checkProductAvaiblity($current_order_details->products ?? null, $request->number_of_items[$order_key]);
                            if (!$do_process) {
                                $restrick_orders[] = [
                                    'order_key' => $order_key,
                                    'available_stock' => $available_stock,
                                    'stock_message' => $message
                                ];
                                $res_data['is_confirmation'] = true;
                            } else {
                                [$total_stock, $out_of_stock] = ProductHelper::calculateProductStock($current_order_details->products ?? null, $request->number_of_items[$order_key]);

                                // -------- retrive add user item table data ---------
                                [$process_orders, $cases] = ProductHelper::generateQueryRequirement($cases, $order_value, $request->number_of_items[$order_key], $order_value, $process_orders);
                                // -------- retrive product table total_stock -----------
                                [$update_total_stock, $total_stock_sql] = ProductHelper::generateQueryRequirement($total_stock_sql, $current_order_details->product_id ?? null, $total_stock, $current_order_details->product_id ?? null, $update_total_stock);
                                // ----------- retrive product out of stock ---------------
                                [$update_out_of_stock, $out_of_stock_sql] = ProductHelper::generateQueryRequirement($out_of_stock_sql, $current_order_details->product_id ?? null, $out_of_stock, $current_order_details->product_id ?? null, $update_out_of_stock);
                            }
                        }
                        $res_data['stock_report'] = $restrick_orders;
                        $res_data['is_error'] = false;
                    } else {
                        $res_data['message'] = "Orders matched each one ! ";
                        $res_data['status'] = 401;
                    }
                } catch (Exception $err) {
                    $res_data['message'] = "Server error please try later !";
                    $res_data['message'] = $err->getMessage();
                }
                // -------------- process update data queries ---------------
                if (!$res_data['is_error'] ? ($res_data['is_confirmation'] ? (count($process_orders) == 0 ? false : true) : true) : false) {
                    try {
                        DB::beginTransaction();
                        // ---------- update user add order table data --------------
                        $extra_condition = " AND cart_status = ? AND  user_id = ?";
                        [$sql, $process_orders] = ProductHelper::generateQuery($cases, $process_orders['primary_ids'], $process_orders, $extra_condition);
                        $process_orders['case_values'][] = 0;
                        $process_orders['case_values'][] = $logged_user->id;
                        DB::update("UPDATE user_add_items SET total_item $sql", $process_orders['case_values']);
                        // --------- update product table total stock ----------
                        [$sql, $update_total_stock] = ProductHelper::generateQuery($total_stock_sql, $update_total_stock['primary_ids'], $update_total_stock, '');
                        DB::update("UPDATE products SET total_stock $sql", $update_total_stock['case_values']);


                        // --------- update product table out of stock ----------
                        [$sql, $update_out_of_stock] = ProductHelper::generateQuery($out_of_stock_sql, $update_out_of_stock['primary_ids'], $update_out_of_stock, '');
                        DB::update("UPDATE products SET out_of_stock $sql", $update_out_of_stock['case_values']);
                        DB::commit();
                        $res_data['message'] = "Process done";
                        $res_data['status'] = 200;
                    } catch (Exception $err) {
                        DB::rollBack();
                        $res_data['status'] = 401;
                        $res_data['message'] = $err->getMessage();
                    }
                }
            } else {
                $res_data['message'] = "Form items not properly sets !";
            }
        }
        return response()->json(['res_data' => $res_data]);
    }
}
