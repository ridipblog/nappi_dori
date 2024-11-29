<?php

namespace App\Models\PublicModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForgotPasswordModel extends Model
{
    use HasFactory;
    protected $table='forgot_password';
    protected $fillable=[
        'email',
        'otp',
        'expire_time',
        'is_used'
    ];
}
