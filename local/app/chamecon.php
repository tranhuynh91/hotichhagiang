<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class chamecon extends Model
{
    protected $table = 'chamecon';
    protected $fillable = [
        'id',
        'mahs',
        'ngaythang',
        'mahs',
        'soso',
        'soquyen',
        'soqd',
        'coquanqd',
        'phanloai',
        'cancu',
        'lydo',
        'phanloainhap',
        'nguoiky',
        'chucvu',
        'nguoithuchien',

        'ngaydangky',
        'hotennk',
        'ngaysinhnk',
        'dantocnk',
        'quoctichnk',
        'noicutrunk',
        'loaigiaytonk',
        'sogiaytonk',
        'noicapgtnk',
        'ngaycapgtnk',
        'quanhenk',
        'diachink',

        'mann',
        'hotennn',
        'gioitinhnn',
        'ngaysinhnn',
        'dantocnn',
        'quoctichnn',
        'tamtrutamvangnn',
        'socmtnn',
        'loaigiaytonn',
        'sogiaytonn',
        'noicapgtnn',
        'ngaycapgtnn',

        'mandn',
        'hotenndn',
        'gioitinhndn',
        'ngaysinhndn',
        'dantocndn',
        'quoctichndn',
        'tamtrutamvangndn',
        'loaigiaytondn',
        'sogiaytondn',
        'noicapgtndn',
        'ngaycapgtndn',

        'trangthai',
        'ghichu',
        'matinh',
        'mahuyen',
        'maxa'
    ];
}
