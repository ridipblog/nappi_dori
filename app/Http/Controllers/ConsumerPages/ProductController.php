<?php

namespace App\Http\Controllers\ConsumerPages;

use App\custom_helpers\ProductHelper;
use App\custom_helpers\reuse_helpers\ReuseHelper;
use App\Http\Controllers\Controller;
use App\Models\Products\ProductsModel;
use App\Models\Products\USerAddItemsModel;
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
            $page = ($request->input('page') ?? 0) * 10;
            $main_query = ProductsModel::query();
            if ($request->input('category_id')) {
                $main_query->where('category_id', $request->input('category_id'));
            }
            $pages = $main_query->count() / 10;
            $main_query->skip($page)->take(10);
            $products = $main_query->get();
            $products->transform(function ($product) {
                $product->enabledEncryption();
                return $product;
            });
            $res_data['product_lists'] = $products;
            $res_data['pages'] = intval($pages);
        } catch (Exception $err) {
            $res_data['message'] = "Server error please try later !";
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
            'do_process' => false
        ];
        $product_details = null;
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
                $save_cart = USerAddItemsModel::create([
                    'user_id' => $request->user('user_guard')->id,
                    'product_id' => $product_details->id,
                    'total_item' => $request->number_of_items,
                ]);
                $update_product = ProductsModel::where('id', $product_id)
                    ->update([
                        'total_stock' => $total_stock,
                        'out_of_stock' => $out_of_stock
                    ]);
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
                    ['user_id', Auth::guard('user_guard')->user()->id],
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
        ];
        $incomming_inputs = [
            'orders' => 'required|array',
            'number_of_items' => 'required|array',

        ];
        $validate = ReuseHelper::validateIncomingData($request, $incomming_inputs);
        if ($validate->fails()) {
            $res_data['message'] = $validate->errors()->all();
        } else {
            if (count($request->orders) == count($request->number_of_items)) {
                try {
                    $logged_user = Auth::guard('user_guard')->user();
                    $order_ids = array_map(fn($order) => Crypt::decryptString($order), $request->orders);
                    $orders_details = USerAddItemsModel::query()->with(['products' => function ($query) {
                        $query->select('id', 'actual_price', 'purchase_price', 'total_stock', 'out_of_stock');
                    }])
                        ->where('user_id', $logged_user->id)
                        ->whereIn('id', $order_ids)
                        ->whereHas('products', function ($query) {})
                        ->select('id', 'user_id', 'product_id')
                        ->get();
                    $restrick_orders = [];
                    foreach ($order_ids as $order_key => $order_value) {
                        [$message, $available_stock, $do_process] = ProductHelper::checkProductAvaiblity($orders_details->firstWhere('id', $order_value)->products ?? null, $request->number_of_items[$order_key]);
                        if (!$do_process) {
                            $restrick_orders[] = [
                                'order_key' => $order_key,
                                'available_stock' => $available_stock,
                                'stock_message' => $message
                            ];
                        }
                    }
                    // $data = [
                    //     ['id' => 1, 'total_item' => 1],
                    //     ['id' => 2, 'total_item' => 2],
                    // ];
                    // $params = [];
                    // $cases = null;
                    // foreach ($data as $values) {
                    //     $cases .= "WHEN ? THEN ? ";
                    //     $params[] = $values['id'];
                    //     $params[] = $values['total_item'];
                    //     $ids[] = $values['id'];
                    // }
                    // $idsPlaceholder = implode(',', array_fill(0, count($ids), '?'));
                    // $res_data['ids']=$idsPlaceholder;
                    // $params = array_merge($params, $ids);
                    // $sql = "UPDATE user_add_items SET total_item = CASE id " . $cases . " END WHERE id IN ($idsPlaceholder)";
                    // DB::update($sql, $params);
                    $res_data['stock_report'] = $restrick_orders;
                    $res_data['order_ids'] = $order_ids;
                } catch (Exception $err) {
                    $res_data['message'] = "Server error please try later !";
                    $res_data['message'] = $err->getMessage();
                }
            } else {
                $res_data['message'] = "Form items not properly sets !";
            }
        }
        return response()->json(['res_data' => $res_data]);
    }
}
