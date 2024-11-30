<?php

namespace App\Http\Controllers\ConsumerPages;

use App\custom_helpers\reuse_helpers\ReuseHelper;
use App\Http\Controllers\Controller;
use App\Models\Products\ProductsModel;
use App\Models\Products\USerAddItemsModel;
use Exception;
use Illuminate\Http\Request;
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
            $res_data['product_lists'] = $main_query->get()->pluck('id');
            $res_data['pages'] = $pages;
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
                    if (($product_details->total_stock === 0 || $product_details->out_of_stock === 0) ? true : ($product_details->total_stock == null ? false : ($product_details->total_stock >= $request->number_of_items ? false : true))) {
                        $res_data['message'] = "The product is now out stock !";
                        if ($product_details->total_stock && ($product_details->total_stock < $request->number_of_items)) {
                            $res_data['message'] = "the amount of stock is only $product_details->total_stock !";
                            $res_data['stock'] = $product_details->total_stock;
                        }
                    } else {
                        $res_data['do_process'] = true;
                    }
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
                $total_stock = null;
                $out_of_stock = 1;
                if ($product_details->total_stock !== null ? ($request->total_stock !== 0 ? true : false) : false) {
                    $total_stock = $product_details->total_stock - $request->number_of_items;
                    if ($total_stock === 0) {
                        $out_of_stock = 0;
                    }
                }
                $res_data['total_stock'] = $total_stock;
                $res_data['out_of_stock'] = $out_of_stock;
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
                DB::commit();
            } catch (Exception $err) {
                DB::rollBack();
                $res_data['message'] = "Server error please try later !";
            }
        }
        return response()->json(['res_data' => $res_data]);
    }
}
