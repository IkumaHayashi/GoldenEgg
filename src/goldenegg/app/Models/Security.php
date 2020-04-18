<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Security extends Model
{
    protected $guarded = [
        'id',
    ];
    protected $fillable = [
        'loginid',
        'password'
    ];

    public function getPasswordAttribute($value)
    {
        return Crypt::decrypt($value);
    }
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Crypt::encrypt($value);
    }
}