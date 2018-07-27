<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ConNuoi extends Model
{
    protected $table = 'connuoi';
    protected $fillable = [
        'id',
        'matinh',
        'mahuyen',
        'maxa',
        'masohoso',
        'masoconnuoi',
        'macdconnuoi',
        'so',
        'quyen',

        'hotenchanuoi',
        'macdchanuoi',
        'ngaysinhchanuoi',
        'noisinhcn',
        'dantocchanuoi',
        'quoctichchanuoi',
        'cmndchanuoi',
        'noicapgtcn',
        'ngaycapgtcn',
        'thuongtrucn',
        'nghenghiepcn',
        'dtcn',

        'hotenmenuoi',
        'macdmenuoi',
        'ngaysinhmenuoi',
        'noisinhmn',
        'dantocmenuoi',
        'quoctichmenuoi',
        'cmndmenuoi',
        'noicapgtmn',
        'ngaycapgtmn',
        'thuongtrumn',
        'nghenghiepmn',
        'dtmn',

        'hotenconnuoi',
        'gioitinhconnuoi',
        'ngaysinhconnuoi',
        'noisinhconnuoi',
        'dantocconnuoi',
        'quoctichconnuoi',
        'thuongtruconnuoi',

        'hotenchagiao',
        'macdchagiao',
        'ngaysinhchagiao',
        'dantocchagiao',
        'quoctichchagiao',
        'cmndchagiao',
        'noicapgtcg',
        'ngaycapgtcg',
        'thuongtrucg',

        'hotenmegiao',
        'macdmegiao',
        'ngaysinhmegiao',
        'dantocmegiao',
        'quoctichmegiao',
        'cmndmegiao',
        'noicapgtmg',
        'ngaycapgtmg',
        'thuongtrumg',

        'quanhenguoigiao',
        'tencsnuoiduong',
        'nguoidaidiencsnd',
        'chucvundd',
        'noidkconnuoi',
        'ngaythangdk',
        'nguoithuchien',
        'nguoikygiaycn',
        'chucvunguoidk',
        'ngaythangqd',
        'soqd',
        'ghichu',
        'lydo',
        'tinhtrangsk',
        'phanloaiconnuoi',
        'tuoiconnuoi',
        'trangthai',
    ];
}
