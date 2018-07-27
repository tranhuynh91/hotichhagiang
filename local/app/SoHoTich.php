<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SoHoTich extends Model
{
    protected $table = 'sohotich';
    protected $fillable = [
        'matinh',
        'mahuyen',
        'maxa',
        'quyenhotich',
        'plhotich',
        'sobatdau',
        'soketthuc',
        'ngaybatdau',
        'ngayketthuc',
        'namso',
    ];
}
