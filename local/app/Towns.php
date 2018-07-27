<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Towns extends Model
{
    protected $table = 'towns';
    protected $fillable = [
        'id',
        'mahuyen',
        'tenhuyen',
        'maxa',
        'tenxa',
        'diachi',
        'dienthoai',
        'fax',
        'email',
        'website',
        'chucvunguoiky',
        'nguoiky',
        'nguoithuchien'
    ];
}
