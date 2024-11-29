<?php

namespace App\Http\Controllers\admin;

use App\custom_helpers\reuse_helpers\ReuseHelper;
use App\Http\Controllers\Controller;
use App\Models\Products\ProductsModel;
use Error;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ManageProductsController extends Controller
{
    // ----------------- save or update products ----------------
    public function saveOrUpdateProducts(Request $request)
    {
        $res_data = [
            'message' => null,
            'status' => 400
        ];
        $incomming_inputs = [
            'product_name' => 'required',
            'product_desc' => 'required',
            'price' => 'integer',
            'discount_price' => 'required|integer',
            'total_stock' => 'integer',
            'out_of_stock' => 'integer',
            'category_id' => 'integer',
            'item_photo' => [$request->api_type !== "update" ? 'required' : '', 'max:2048', 'mimes:jpg,jpeg,png'],
        ];
        $validate = ReuseHelper::validateIncomingData($request, $incomming_inputs);
        $item_photo = null;
        if ($validate->fails()) {
            $res_data['message'] = $validate->errors()->all();
        } else {
            $res_data['status'] = 401;
            try {
                DB::beginTransaction();
                $save_update_product = ProductsModel::updateOrCreate([
                    'id' => $request->product_id ? Crypt::decryptString($request->product_id) : null
                ], [
                    'product_name' => $request->product_name,
                    'product_desc' => $request->product_desc,
                    'price' => $request->price,
                    'discount_price' => $request->discount_price,
                    'total_stock' => $request->total_stock,
                    'out_of_stock' => $request->out_of_stock ?? 1,
                    'category_id' => $request->category_id ?? 0,
                    // 'item_photo' => $request->item_photo,
                ]);
                if ($request->hasFile('item_photo') ? true : ($request->product_id ? ($save_update_product->item_photo ? false : throw new Error('File not found')) : throw new Error('File not found'))) {
                    $item_photo = $request->file('item_photo')->store('/public/products_photos');
                    ReuseHelper::removeFormStorage($save_update_product->item_photo ?? null);
                    $update_item_photo = ProductsModel::where('id', $save_update_product->id)
                        ->update([
                            'item_photo' => $item_photo
                        ]);
                }
                DB::commit();
                $res_data['message'] = "Product added successfully !";
                $res_data['product'] = $save_update_product;
                $res_data['status'] = 200;
            } catch (Exception $err) {
                DB::rollBack();
                ReuseHelper::removeFormStorage($item_photo);
                $res_data['message'] = "Server error please try later !";
                // $res_data['message'] = $err->getMessage();
            }
        }
        return response()->json(['res_data' => $res_data]);
    }
}
