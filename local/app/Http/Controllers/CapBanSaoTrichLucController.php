<?php

namespace App\Http\Controllers;

use App\CapBanSaoTrichLuc;
use App\chamecon;
use App\Districts;
use App\GeneralConfigs;
use App\KhaiSinh;
use App\KhaiTu;
use App\giamho;
use App\SoHoTich;
use App\Towns;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;

class CapBanSaoTrichLucController extends Controller
{
    public function index(Request $request){
        if (Session::has('admin')) {
            $inputs = $request->all();
            $thang = isset($inputs['thang']) ? $inputs['thang'] : date('m');
            $nam = isset($inputs['nam']) ? $inputs['nam'] : date('Y');

            $model = CapBanSaoTrichLuc::whereMonth('ngaycap', $thang)
                ->whereYear('ngaycap', $nam);
            if(session('admin')->level == 'T'){
                $model = $model->where('level','T');
            }elseif(session('admin')->level == 'H'){
                $model = $model->where('level','H')
                    ->where('madv',session('admin')->mahuyen);
            }else{
                $model = $model->where('level','X')
                    ->where('madv',session('admin')->maxa);
            }
            $model = $model->get();
            $count = $model->count();

            return view('manage.capbansaotrichluc.index')
                -> with('thang',$thang)
                -> with('nam',$nam)
                -> with('model', $model)
                -> with('pageTitle','Thông tin cấp bản sao trích lục ('.$count.' hồ sơ)');
        }else
            return view('errors.notlogin');
    }

    public function store(Request $request){
        if (Session::has('admin')) {
            $inputs = $request->all();

            $inputs['trangthai'] = 'Chờ duyệt';

            $inputs['ngaycap'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaycap'])));
            if(session('admin')->level == 'T'){
                $inputs['madv'] = getmatinh();
                $inputs['level'] = 'T';
                $inputs['quyentrichluc'] = $inputs['madv'].'TLBS'.date('Y');
                $inputs['sotrichluc'] = $this->getSoHoTich($inputs['madv'],$inputs['quyentrichluc'] );
            }elseif(session('admin')->level == 'H'){
                $inputs['madv'] = session('admin')->mahuyen;
                $inputs['level'] = 'H';
                $inputs['quyentrichluc'] = $inputs['madv'].'TLBS'.date('Y');
                $inputs['sotrichluc'] = $this->getSoHoTich($inputs['madv'],$inputs['quyentrichluc'] );
            }else{
                $inputs['madv'] = session('admin')->maxa;
                $inputs['level'] = 'X';
                $inputs['quyentrichluc'] = $inputs['madv'].'TLBS'.date('Y');
                $inputs['sotrichluc'] = $this->getSoHoTich($inputs['madv'],$inputs['quyentrichluc'] );
            }
            $model = new CapBanSaoTrichLuc();
            $model->create($inputs);
            return redirect('capbansaotrichluc');
        }else
            return view('errors.notlogin');
    }

    public function getSoHoTich($madv,$quyen){
        $idmax = CapBanSaoTrichLuc::where('madv',$madv)
            ->where('quyentrichluc',$quyen)
            ->max('id');
        if($idmax==null)
            $stt = 1;
        else{
            $model =CapBanSaoTrichLuc::where('id', $idmax)->first();
            $stt = $model->sotrichluc + 1;
        }
        return $stt;
    }

    public function show($id){
        if (Session::has('admin')) {
            $model = CapBanSaoTrichLuc::find($id);
            if($model->plbstrichluc == 'Khai sinh') {
                $modeltt = KhaiSinh::where('quyen',$model->quyenhotich)
                    ->where('so',$model->sohotich)
                    ->first();

                return view('manage.capbansaotrichluc.show')
                    ->with('model', $model)
                    ->with('modeltt',$modeltt)
                    ->with('pageTitle', 'Thông tin cấp trích lục bản sao');
            }
            if($model->plbstrichluc == 'Khai tử') {
                $modeltt = KhaiTu::where('quyen',$model->quyenhotich)
                    ->where('so',$model->sohotich)
                    ->first();

                return view('manage.capbansaotrichluc.show')
                    ->with('model', $model)
                    ->with('modeltt',$modeltt)
                    ->with('pageTitle', 'Thông tin cấp trích lục bản sao');
            }

        }else
            return view('errors.notlogin');
    }

    public function edit($id){
        if (Session::has('admin')) {
            $model = CapBanSaoTrichLuc::find($id);

                return view('manage.capbansaotrichluc.edit')
                    ->with('model', $model)
                    ->with('pageTitle', 'Chỉnh sửa thông tin cấp trích lục bản sao');
        }else
            return view('errors.notlogin');
    }

    public function update(Request $request,$id){
        if (Session::has('admin')) {
            $inputs = $request->all();
            $inputs['ngaycap'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaycap'])));
            $model = CapBanSaoTrichLuc::find($id);
            $model->update($inputs);
            return redirect('capbansaotrichluc');
        }else
            return view('errors.notlogin');
    }

    public function destroy(Request $request){
        if (Session::has('admin')) {
            $inputs = $request->all();
            $id = $inputs['iddelete'];
            $model = CapBanSaoTrichLuc::find($id);
            $model->delete();
            return redirect('capbansaotrichluc');

        }else
            return view('errors.notlogin');
    }

    public function duyet(Request $request){
        if (Session::has('admin')) {
            $inputs = $request->all();
            $id = $inputs['idduyet'];
            $model = CapBanSaoTrichLuc::find($id);
            $model->trangthai = 'Duyệt';
            $model->save();
            return redirect('capbansaotrichluc');

        }else
            return view('errors.notlogin');
    }

    public function prints($id){
        if (Session::has('admin')) {

            $model = CapBanSaoTrichLuc::find($id);

            if($model->level == 'T'){
                $tinh = GeneralConfigs::first()->tendv;
                $huyen = '';
                $xa = '';
            }elseif($model->level == 'H'){
                $tinh = GeneralConfigs::first()->tendv;
                $huyen = Districts::where('mahuyen',$model->madv)->first()->tenhuyen;
                $xa = '';
            }else{
                $tinh = GeneralConfigs::first()->tendv;
                $modelxa = Towns::where('maxa',$model->madv)->first();
                $xa = $modelxa->tenxa;
                $modelhuyen = Districts::where('mahuyen',$modelxa->mahuyen)->first();
                $huyen = $modelhuyen->tenhuyen;
                $tenxa = substr("$xa",8);
            }
            if($model->plbstrichluc == 'Khai sinh') {
                $modeltt = KhaiSinh::where('quyen',$model->quyenhotich)
                    ->where('so',$model->sohotich)
                    ->first();
                $noidkks = Towns::where('maxa',$modeltt->maxa)->first()->tenxa;
                return view('reports.khaisinh.printtrichluc')
                    ->with('model', $model)
                    ->with('modeltt',$modeltt)
                    ->with('tinh', $tinh)
                    ->with('huyen', $huyen)
                    ->with('xa', $xa)
                    ->with('tenxa', $tenxa)
                    ->with('noidkks',$noidkks)
                    ->with('pageTitle', 'In giấy khai sinh bản sao');
            }

            if($model->plbstrichluc == 'Khai tử') {
                $modeltt = KhaiTu::where('quyen',$model->quyenhotich)
                    ->where('so',$model->sohotich)
                    ->first();

                $noidkkt = Towns::where('maxa',$modeltt->maxa)->first()->tenxa;
                return view('reports.khaitu.printtrichluc')
                    ->with('model', $model)
                    ->with('modeltt',$modeltt)
                    ->with('tinh', $tinh)
                    ->with('huyen', $huyen)
                    ->with('xa', $xa)
                    ->with('tenxa', $tenxa)
                    ->with('noidkkt',$noidkkt)
                    ->with('pageTitle', 'In giấy khai tử bản sao');
            }

            if($model->plbstrichluc == 'Chấm dứt giám hộ') {
                $modeltt = giamho::where('quyencd',$model->quyenhotich)
                    ->where('socd',$model->sohotich)
                    ->first();

                $noidkcdgh = Towns::where('maxa',$modeltt->maxa)->first()->tenxa;
                return view('reports.cdgiamho.printtrichluc')
                    ->with('model', $model)
                    ->with('modeltt',$modeltt)
                    ->with('tinh', $tinh)
                    ->with('huyen', $huyen)
                    ->with('xa', $xa)
                    ->with('tenxa', $tenxa)
                    ->with('noidkkt',$noidkcdgh)
                    ->with('pageTitle', 'In chấm dứt giám hộ bản sao');
            }

            if($model->plbstrichluc == 'Giám hộ') {
                $modeltt = giamho::where('soquyen',$model->quyenhotich)
                    ->where('soso',$model->sohotich)
                    ->first();

                $noidkcdgh = Towns::where('maxa',$modeltt->maxa)->first()->tenxa;
                return view('reports.giamho.printtrichluc')
                    ->with('model', $model)
                    ->with('modeltt',$modeltt)
                    ->with('tinh', $tinh)
                    ->with('huyen', $huyen)
                    ->with('xa', $xa)
                    ->with('tenxa', $tenxa)
                    ->with('noidkkt',$noidkcdgh)
                    ->with('pageTitle', 'In chấm dứt giám hộ bản sao');
            }

            if($model->plbstrichluc == 'Nhận cha mẹ con') {
                $modeltt = chamecon::where('soquyen',$model->quyenhotich)
                    ->where('soso',$model->sohotich)
                    ->first();

                $noidkcdgh = Towns::where('maxa',$modeltt->maxa)->first()->tenxa;
                return view('reports.cmc.printtrichluc')
                    ->with('model', $model)
                    ->with('modeltt',$modeltt)
                    ->with('tinh', $tinh)
                    ->with('huyen', $huyen)
                    ->with('xa', $xa)
                    ->with('tenxa', $tenxa)
                    ->with('noidkkt',$noidkcdgh)
                    ->with('pageTitle', 'In chấm dứt giám hộ bản sao');
            }
        }else
            return view('errors.notlogin');
    }

}