<?php

namespace App\Http\Controllers;

use App\CapBanSaoTrichLuc;
use App\chamecon;
use App\CongDan;
use App\ConNuoi;
use App\DmDvQl;
use App\DnDvLt;
use App\DnDvLtReg;
use App\DonViDvVt;
use App\DonViDvVtReg;
use App\GeneralConfigs;
use App\giamho;
use App\KetHon;
use App\KhaiSinh;
use App\KhaiTu;
use App\Register;
use App\TTHonNhan;
use App\Users;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\Session;

class HomeController extends Controller
{

    public function index()
    {
        if (Session::has('admin')) {
            if(session('admin')->sadmin == 'sa')
                return redirect('cau_hinh_he_thong');
            else
                $count = '';
                if(session('admin')->level == 'T'){

                    $count['slkt'] = KhaiTu::all()->count();
                    $count['slks'] = KhaiSinh::all()->count();
                    $count['sltthn'] = TTHonNhan::all()->count();
                    $count['slkh'] = KetHon::all()->count();
                    $count['slcn'] = ConNuoi::all()->count();
                    $count['slgh'] = giamho::all()->count();
                    $count['slcmc'] = chamecon::all()->count();
                    $count['slbstl'] = CapBanSaoTrichLuc::all()->count();

                }elseif(session('admin')->level =='H'){
                    $count['slkt'] = KhaiTu::where('mahuyen',session('admin')->mahuyen)->count();
                    $count['slks'] = KhaiSinh::where('mahuyen',session('admin')->mahuyen)->count();
                    $count['sltthn'] = TTHonNhan::where('mahuyen',session('admin')->mahuyen)->count();
                    $count['slkh'] = KetHon::where('mahuyen',session('admin')->mahuyen)->count();
                    $count['slcn'] = ConNuoi::where('mahuyen',session('admin')->mahuyen)->count();
                    $count['slgh'] = giamho::where('mahuyen',session('admin')->mahuyen)->count();
                    $count['slcmc'] = chamecon::where('mahuyen',session('admin')->mahuyen)->count();
                    $count['slbstl'] = CapBanSaoTrichLuc::where('mahuyen',session('admin')->mahuyen)->count();
                }else{
                    $count['slkt'] = KhaiTu::where('mahuyen',session('admin')->mahuyen)->where('maxa',session('admin')->maxa)->count();
                    $count['slks'] = KhaiSinh::where('mahuyen',session('admin')->mahuyen)->where('maxa',session('admin')->maxa)->count();
                    $count['sltthn'] = TTHonNhan::where('mahuyen',session('admin')->mahuyen)->where('maxa',session('admin')->maxa)->count();
                    $count['slkh'] = KetHon::where('mahuyen',session('admin')->mahuyen)->where('maxa',session('admin')->maxa)->count();
                    $count['slcn'] = ConNuoi::where('mahuyen',session('admin')->mahuyen)->where('maxa',session('admin')->maxa)->count();
                    $count['slgh'] = giamho::where('mahuyen',session('admin')->mahuyen)->where('maxa',session('admin')->maxa)->count();
                    $count['slcmc'] = chamecon::where('mahuyen',session('admin')->mahuyen)->where('maxa',session('admin')->maxa)->count();
                    $count['slbstl'] = CapBanSaoTrichLuc::where('mahuyen',session('admin')->mahuyen)->where('maxa',session('admin')->maxa)->count();
                }
                return view('dashboard')
                    ->with('count',$count)
                    ->with('pageTitle','Tổng quan');
        }else
            return view('welcome');

    }



    public function forgotpassword(){

        return view('system.users.forgotpassword.index')
            ->with('pageTitle','Quên mật khẩu???');
    }

    public function forgotpasswordw(Request $request){

        $input = $request->all();

        $model = Users::where('username',$input['username'])->first();

        if(isset($model)){
            if($model->emailxt == $input['emailxt'] && $model->question == $input['question']  && $model->answer == $input['answer']){
                $model->password = 'e10adc3949ba59abbe56e057f20f883e';
                $model->save();
                return view('errors.forgotpass-success');
            }else
                return view('errors.forgotpass-errors');
        }else
            return view('errors.forgotpass-errors');

    }
}
