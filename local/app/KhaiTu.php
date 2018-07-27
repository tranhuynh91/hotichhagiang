<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class KhaiTu extends Model
{
    protected $table = "khaitu";
    protected $fillable = [
        'id',
        'matinh',
        'mahuyen',
        'maxa',
        'so',
        'quyen',

        'hoten',
        'gioitinh',
        'ngaysinh',
        'noisinh',
        'dantoc',
        'quoctich',
        'thuongtru',
        'loaigiayto',
        'sogiayto',
        'giotu',
        'phuttu',
        'ngaychet',
        'noichet',
        'nguyennhan',
        'giaybaotu',
        'donvicapgbt',
        'ngaycapgbt',
        'noidangkykt',
        'ngaydangkykt',
        'ghichukt',
        'nguoithuchien',
        'nguoikygct',
        'chucvu',
        'hotennk',
        'loaigiaytonk',
        'sogiaytonk',
        'noicapnk',
        'ngaycapnk',
        'noicutrunk',
        'quanhe',
        'phanloaikt',
        'phanloaidk',
        'phanloaituoi',
        'tuoinguoitu',
        'dunghanquahan',
        'dunghanquahan',
        'masohoso',
        'trangthai',
    ];
}
