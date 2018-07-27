<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class KetHon extends Model
{
    protected $table = 'kethon';
    protected $fillable = [
        'id',
        'matinh',
        'mahuyen',
        'maxa',
        'mahs',
        'pldangky',
        'sokethon',
        'quyenkethon',
        'ngaydangky',
        'nguoiky',
        'chucvu',
        'nguoithuchien',
        'ghichu',

        'hotenvo',
        'loaigiaytovo',
        'sogiaytovo',
        'ngaycapvo',
        'noicapvo',
        'ngaysinhvo',
        'dantocvo',
        'quoctichvo',
        'diachivo',
        'lankhvo',

        'hotenchong',
        'loaigiaytochong',
        'sogiaytochong',
        'ngaycapchong',
        'noicapchong',
        'ngaysinhchong',
        'dantocchong',
        'quoctichchong',
        'diachichong',
        'lankhchong',
        'trangthai'
    ];
}
