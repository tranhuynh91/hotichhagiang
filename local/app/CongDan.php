<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CongDan extends Model
{
    protected $table = 'congdan';
    protected $fillable = [
        'id',
        'matinh',
        'mahuyen',
        'maxa',
        'macongdan',
        'hoten',
        'hotenk',
        'gioitinh',
        'dantoc',
        'quoctich',
        'tongiao',
        'ngaysinh',
        'noisinh',
        'quequan',
        'thuongtru',
        'socmnd',
        'ttcmnd',
        'trangthai',
        'tttd',
        'tthonnhan',
        'action'
    ];
}
