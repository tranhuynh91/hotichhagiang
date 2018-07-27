<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DanToc extends Model
{
    protected $table = 'dantoc';
    protected $fillable = [
        'id',
        'dantoc'
    ];
}
