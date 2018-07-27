<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TTHonNhan extends Model
{
    protected $table = 'tthonnhan';
    protected $fillable = [
        'id',
        'matinh',
        'mahuyen',
        'maxa',
        'mahs',
        'so',
        'quyen',
        'soxacnhan',
        'ngayxn',
        'donvixn',
        'nguoidn',
        'quanhe',
        'hotenxn',
        'ngaysinh',
        'noisinh',
        'gioitinh',
        'dantoc',
        'quoctich',
        'giayto',
        'sogiayto',
        'noicap',
        'ngaycap',
        'noicutru',
        'nghenghiep',
        'tungay',
        'denngay',
        'tthonnhan',
        'noidungxn',
        'trangthai',
        'hotennky',
        'dantocnk',
        'quoctichnk',
        'noicutrunk',
        'chucvunky',
        'phanloai',
        'nguoithuchien',
    ];
}
