<?php

namespace  App\custom_helpers\reuse_helpers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;


class ReuseHelper
{

    // ---------------- validate incomming data  ---------------
    public static function validateIncomingData($request, $request_field)
    {
        // App::setLocale(session::get('locale'));
        $error_message = [
            // 'required' => ':attribute '.__('validation_message.dynamic_validate_errors.required'),
            'required' => ':attribute is required field !',
            'integer' => ':attribute is only number format !',
            'regex' => 'phone number must be 10 digit ',
            'max' => ':attribute  size only 5 megabytes',
            'mimes' => ':attribute file type is not valid ',
            'email' => 'Please enter a valid email',
            'confirmed' => ':attribute is does not match with confirmation',
            'date' => ':attribute is date only ',
            'unique' => ':attribute is already exists ',
            'array' => ':attribute is array type',
            'required_if' => ':attribute is require field !',
            'exists' => ':attribute is not found in database !',
            'phone.exists' => 'phone number is not exists ,please register your number',
            'min'=>':attribute is must be atleast 1'
        ];
        $validate = Validator::make(
            $request->all(),
            $request_field,
            $error_message
        );
        return $validate;
    }
    // --------------- delete file form storage ----------------------
    public static function removeFormStorage($file){
        if(isset($file) ? Storage::exists($file) : false){
            Storage::delete($file);
        }
    }

}
