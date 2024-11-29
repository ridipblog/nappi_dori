<?php

namespace App\Models\AuthModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class RegistertedUserModel extends Authenticatable
{
    use HasApiTokens, HasFactory,Notifiable;
    protected $table='registered_users';
    protected $fillable=[
        'name',
        'email',
        'password'
    ];
    protected $hidden=[
        'password'
    ];
}
