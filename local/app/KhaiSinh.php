<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class KhaiSinh extends Model
{
    protected $table = 'khaisinh';
    protected $fillable = [
        'id',
        'matinh',
        'mahuyen',
        'maxa',
        'mahs',
        'plkhaisinh',
        'pldangky',
        'dunghanquahan',
        'so',
        'quyen',
        'ngaydangky',
        'nguoiky',
        'chucvu',
        'nguoithuchien',

        'hotennk',
        'loaigiaytonk',
        'sogiaytonk',
        'noicapgtnk',
        'ngaycapgtnk',
        'quanhenk',
        'diachink',

        'sochungsinh',
        'sodinhdanhcanhan',
        'hotenks',
        'gioitinhks',
        'ngaysinhks',
        'ngaysinhksbangchu',
        'dantocks',
        'quoctichks',
        'plnoisinh',
        'noisinh',
        'quequanks',

        'deathme',
        'hotenme',
        'loaigiaytome',
        'sogiaytome',
        'ngaysinhme',
        'dantocme',
        'quoctichme',
        'diachime',

        'deathcha',
        'hotencha',
        'loaigiaytocha',
        'sogiaytocha',
        'ngaysinhcha',
        'dantoccha',
        'quoctichcha',
        'diachicha',

        'trangthai',
        'sobansao',
        'noikcbbd',
        'sodtlh',
        'emaillh',
        'sosohokhau',
        'hotenchuho',
        'quanhechuho',
        'tongiaoks',
        'diachiht',
        'thuongtru',
        'ghichu',
    ];
}
