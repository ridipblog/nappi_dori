<?php

namespace App\Http\Controllers\ConsumerPages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // --------------- get product lists -----------------
    public function productLists(Request $request)
    {
        $res_data = [
            'message' => null,
            'status' => 400
        ];
        return response()->json(['res_data' => $res_data]);
    }
    // ------------------- product add card ----------------
    public function addCard(Request $request)
    {
        $res_data = [
            'message' => null,
            'status' => 400
        ];
        return response()->json(['res_data' => $res_data]);
    }
}
