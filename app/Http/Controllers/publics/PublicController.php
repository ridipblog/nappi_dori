<?php

namespace App\Http\Controllers\publics;

use App\custom_helpers\reuse_helpers\ReuseHelper;
use App\Http\Controllers\Controller;
use App\Models\AuthModel\RegistertedUserModel;
use App\Rules\ValidatePasswordRule;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class PublicController extends Controller
{
    public function registration(Request $request)
    {
        $res_data = [
            'status' => 400,
            'message' => null
        ];
        try {
            $incomming_inputs = [
                'name' => 'required',
                'email' => ['required', 'email', Rule::unique('registered_users', 'email')],
                'password' => ['required',new ValidatePasswordRule()],
                'confirm_password' => 'required'
            ];
            $validate = ReuseHelper::validateIncomingData($request, $incomming_inputs);
            if ($validate->fails()) {
                $res_data['message'] = $validate->errors()->all();
            } else {
                if ($request->password === $request->confirm_password) {
                    $save_user = RegistertedUserModel::create([
                        'name' => $request->name,
                        'email' => $request->email,
                        'password' => Hash::make($request->password)
                    ]);
                    $res_data['status']=200;
                    $res_data['message']="Registration Completed !";
                } else {
                    $res_data['message'] = ['confirm password must be same with password'];
                }
            }
        } catch (Exception $err) {
            $res_data['status'] = 401;
            // $res_data['message']=$err->getMessage();
            $res_data['message'] = "Server error please try later !";
        }
        return response()->json(['res_data' => $res_data]);
    }
}
