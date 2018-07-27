<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GeneralConfigs extends Model
{
    protected $table = 'general-configs';
    protected $fillable = [
        'id',
        'matinh',
        'maqhns',
        'tendv',
        'diachi',
        'teldv',
        'thutruong',
        'ketoan',
        'nguoilapbieu',
        'setting'
    ];
}
