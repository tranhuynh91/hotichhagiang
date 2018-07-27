<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Districts extends Model
{
    protected $table = 'districts';
    protected $fillable = [
        'id',
        'mahuyen',
        'tenhuyen',
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
