<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CapBanSaoTrichLuc extends Model
{
    //capbansaotrichluc
    protected $table = 'capbansaotrichluc';
    protected $fillable = [
        'id',
        'madv',
        'ngaycap',
        'level',
        'sotrichluc',
        'quyentrichluc',
        'plbstrichluc',
        'quyenhotich',
        'sohotich',
        'nguoiky',
        'chucvu',
        'soluongbs',
        'ghichu',
        'hotennyc',
        'plgiaytonyc',
        'sogiaytonyc',
        'trangthai'
    ];
}
