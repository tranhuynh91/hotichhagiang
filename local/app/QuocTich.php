<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class QuocTich extends Model
{
    protected $table = 'quoctich';
    protected $fillable = [
        'id',
        'quoctich'
    ];
}
