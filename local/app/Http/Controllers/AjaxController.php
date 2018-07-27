<?php

namespace App\Http\Controllers;

use App\ConNuoi;
use App\Districts;
use App\KetHon;
use App\KhaiSinh;
use App\KhaiTu;
use App\SoHoTich;
use App\Towns;
use App\TTHonNhan;
use App\Users;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;

class AjaxController extends Controller
{
    public function checkuser(Request $request){
        $input = $request->all();
        $usercheck = $input['username'];

        $model = Users::where('username', $usercheck)
            ->first();
        if (isset($model)) {
            echo 'cancel';
        } else {
            echo 'ok';
        }
    }

    public function checkmahuyen(Request $request){
        $input = $request->all();
        $mahuyencheck = $input['mahuyen'];

        $model = Districts::where('mahuyen', $mahuyencheck)
            ->first();
        if (isset($model)) {
            echo 'cancel';
        } else {
            echo 'ok';
        }
    }

    public function checkmaxa(Request $request){
        $input = $request->all();
        $maxacheck = $input['maxa'];

        $model = Towns::where('maxa', $maxacheck)
            ->first();
        if (isset($model)) {
            echo 'cancel';
        } else {
            echo 'ok';
        }
    }

    public function getXas(Request $request){
        $result = array(
            'status' => 'fail',
            'message' => 'error',
        );
        if(!Session::has('admin')) {
            $result = array(
                'status' => 'fail',
                'message' => 'permission denied',
            );
            die(json_encode($result));
        }

        $inputs = $request->all();

        if(isset($inputs['mahuyen'])){
            $xas = Towns::where('mahuyen', $inputs['mahuyen'])->get();
            $result['message'] = '<select name="maxa" id="maxa" class="form-control">';
            if(count($xas) > 0){
                foreach($xas as $xa){
                    $result['message'] .= '<option value="'.$xa->maxa.'">'.$xa->tenxa.'</option>';
                }
            }
            $result['message'] .= '</select>';
            $result['status'] = 'success';
        }

        die(json_encode($result));
    }
}
