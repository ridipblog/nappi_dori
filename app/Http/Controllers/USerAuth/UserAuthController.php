<?php

namespace App\Http\Controllers\USerAuth;

use App\custom_helpers\reuse_helpers\ReuseHelper;
use App\Http\Controllers\Controller;
use App\Models\AuthModel\RegistertedUserModel;
use App\Models\PublicModel\ForgotPasswordModel;
use App\Rules\ValidatePasswordRule;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserAuthController extends Controller
{
    // ----------------- login API ---------------
    public function login(Request $request)
    {
        $res_data = [
            'message' => null,
            'status' => 400
        ];
        $incomming_inputs = [
            'email' => 'required|email',
            'password' => 'required'
        ];
        $validate = ReuseHelper::validateIncomingData($request, $incomming_inputs);
        if ($validate->fails()) {
            $res_data['message'] = $validate->errors()->all();
        } else {
            $res_data['status'] = 401;
            try {
                $user = RegistertedUserModel::where('email', $request->email)
                    ->first();
                if ($user) {
                    if ($user->active == 1) {
                        if (Hash::check($request->password, $user->password)) {
                            $token = $user->createToken('RequestAuthToken')->accessToken;
                            $res_data['token'] = $token;
                            $res_data['status'] = 200;
                        } else {
                            $res_data['message'] = "Credentials not matched !";
                        }
                    } else {
                        $res_data['message'] = "Your account is deactived !";
                    }
                } else {
                    $res_data['message'] = "No account function by this email !";
                }
            } catch (Exception $err) {
                $res_data['message'] = "Server error please try later !";
            }
        }
        return response()->json(['res_data' => $res_data]);
    }
    // -------------- forgot password -------------
    public function forgotPassword(Request $request)
    {
        $res_data = [
            'message' => null,
            'status' => 400
        ];
        try {
            $incomming_inputs = [
                'email' => 'required|email|exists:registered_users,email'
            ];
            $validate = ReuseHelper::validateIncomingData($request, $incomming_inputs);
            if ($validate->fails()) {
                $res_data['message'] = $validate->errors()->all();
            } else {
                $otp = rand(100000, 999999);
                date_default_timezone_set('Asia/Kolkata');
                $send_time = date('Y-m-d H:i:s');
                ForgotPasswordModel::updateOrInsert([
                    'email' => $request->email
                ], [
                    'otp' => $otp,
                    'expire_time' => $send_time,
                    'is_used' => 1
                ]);
                $res_data['forgot_password_token'] = Crypt::encryptString($request->email);
                $res_data['otp'] = $otp;
                $res_data['status'] = 200;
            }
        } catch (Exception $err) {
            $res_data['status'] = 401;
            $res_data['message'] = "Server error please try later !";
        }
        return response()->json(['res_data' => $res_data]);
    }
    // ------------------ set new password --------------
    public function setNewPassword(Request $request)
    {
        $res_data = [
            'message' => null,
            'status' => 400,
            'do_process' => false
        ];
        $incomming_inputs = [
            'forgot_password_token' => 'required',
            'otp' => 'required|digits:6',
            'password' => ['required', new ValidatePasswordRule()],
            'confirm_password' => 'required'
        ];
        $validate = ReuseHelper::validateIncomingData($request, $incomming_inputs);
        $email = null;
        if ($validate->fails()) {
            $res_data['message'] = $validate->errors()->all();
        } else {
            if ($request->password === $request->confirm_password) {
                $res_data['status'] = 401;
                try {
                    $email = Crypt::decryptString($request->forgot_password_token);
                    $otp_details = ForgotPasswordModel::where('email', $email)->first();
                    if ($otp_details) {
                        date_default_timezone_set('Asia/Kolkata');
                        $expire_time = $otp_details->expire_time;
                        $recive_time = date('Y-m-d H:i:s');
                        $new_expire_time = new DateTime($expire_time);
                        $time_diff = $new_expire_time->diff(new DateTime($recive_time));
                        if ($time_diff->y == 0 && $time_diff->m == 0 && $time_diff->d == 0 && $time_diff->h == 0 && $time_diff->i <= 1 && $time_diff->s <= 60 && $otp_details->is_used == 1) {
                            if ($otp_details->otp === $request->otp) {
                                $res_data['do_process'] = true;
                            } else {
                                $res_data['message'] = "OTP mismatched !";
                            }
                        } else {
                            $res_data['message'] = "OTP is expire please request OTP ";
                        }
                    } else {
                        $res_data['message'] = "Please request forgot password OTP ";
                    }
                } catch (Exception $err) {
                    $res_data['message'] = "Server error please try later !";
                }
            } else {
                $res_data['message'] = ['confirm password must be same with password'];
            }
        }
        if ($res_data['do_process']) {
            try {
                DB::beginTransaction();
                $update_otp = ForgotPasswordModel::where('email', $email)
                    ->update([
                        'is_used' => 2
                    ]);
                $update_password = RegistertedUserModel::where('email', $email)
                    ->update([
                        'password' => Hash::make($request->password)
                    ]);
                DB::commit();
                $res_data['message'] = "Password has been updated !";
                $res_data['status'] = 200;
            } catch (Exception $err) {
                DB::rollBack();
                $res_data['status'] = 401;
                $res_data['message'] = "Server error please try later !";
            }
        }
        return response()->json(['res_data' => $res_data]);
    }
}
