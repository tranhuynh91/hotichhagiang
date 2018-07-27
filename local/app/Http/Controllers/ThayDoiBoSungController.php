<?php

namespace App\Http\Controllers;

use App\ConNuoi;
use App\DanToc;
use App\Districts;
use App\GeneralConfigs;
use App\KetHon;
use App\KhaiSinh;
use App\KhaiTu;
use App\SoHoTich;
use App\giamho;
use App\chamecon;
use App\TTHonNhan;
use App\ThongTinThayDoi;
use App\Towns;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;

class ThayDoiBoSungController extends Controller
{
    //Khai sinh
    public function kscreate($id)
    {
        if (Session::has('admin')) {
            if(session('admin')->level == 'T'){
                $huyens = Districts::all();
                $huyendf = Districts::first()->mahuyen;
                $xas = Towns::where('mahuyen',$huyendf)
                    ->get();
                $xadf = $xas->first()->maxa;
            }elseif(session('admin')->level =='H'){
                $huyens = Districts::all();
                $huyendf = Districts::where('mahuyen',session('admin')->mahuyen)->first()->mahuyen;
                $xas = Towns::where('mahuyen',$huyendf)
                    ->get();
                $xadf = $xas->first()->maxa;
            }else{
                $huyens = Districts::all();
                $huyendf = Districts::where('mahuyen',session('admin')->mahuyen)->first()->mahuyen;
                $xas = Towns::where('mahuyen',$huyendf)
                    ->get();
                $xadf = $xas->where('maxa',session('admin')->maxa)->first()->maxa;
            }

            $dantocs = getDanTocSelectOptions();
            $quoctichs = getQuocTichSelectOptions();
            return view('manage.khaisinh.include.thaydoi')
                ->with('huyens',$huyens)
                ->with('mahuyen',$huyendf)
                ->with('xas',$xas)
                ->with('maxa',$xadf)
                ->with('mahs',$id)
                ->with('dantocs', $dantocs)
                ->with('quoctichs', $quoctichs)
                ->with('pageTitle','Thêm mới thông tin thay đổi');
        }else
            return view('errors.notlogin');
    }

    public function luukhaisinhbs(Request $request){
        if (Session::has('admin')) {
            $inputs = $request->all();
            $modelsohotich =  SoHoTich::where('plhotich','Thay đổi bổ xung')
                ->where('namso',date('Y'))->first()->quyenhotich;
            $inputs['quyentd'] = (isset($modelsohotich)) ? $modelsohotich : getmatinh().$inputs['mahuyen'].$inputs['maxa'].'KS'.date('Y');

            $inputs['sotd'] = $this->getSothaydoiKS($inputs['maxa'],$inputs['mahuyen'],$inputs['quyentd'] );
            $inputs['ngaydk'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaydk'])));
            $inputs['ngaysinhntd'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaysinhntd'])));
            $inputs['plgiayto'] = "Khai sinh";
            $inputs['trangthai'] = "Chờ duyệt";
            $model = new ThongTinThayDoi();
            $model->create($inputs);
            return redirect('khaisinh');
        }else
            return view('errors.notlogin');
    }

    public function getSothaydoiKS($maxa,$mahuyen,$quyentd){
        $idmax = ThongTinThayDoi::where('maxa',$maxa)
            ->where('mahuyen',$mahuyen)
            ->where('quyentd',$quyentd)
            ->max('id');
        if($idmax==null)
            $stt = 1;
        else{
            $model =ThongTinThayDoi::where('id', $idmax)->first();
            $stt = $model->sotd + 1;
        }
        return $stt;
    }

    public function duyetks(Request $request){
        if (Session::has('admin')) {
            $inputs = $request->all();
            $id = $inputs['idduyet'];
            $model = ThongTinThayDoi::find($id);
            $model->trangthai = 'Duyệt';
            $model->save();
            return redirect('khaisinh');

        }else
            return view('errors.notlogin');
    }

    public function showks($id)
    {
        if (Session::has('admin')) {

            $model = ThongTinThayDoi::find($id);
            $xas = Towns::where('maxa',$model->maxa)->first()->tenxa;
            $huyens = Districts::where('mahuyen',$model->mahuyen)->first()->tenhuyen;
            $dantocs = getDanTocSelectOptions();
            $quoctichs = getQuocTichSelectOptions();
            return view('manage.khaisinh.include.showthaydoi')
                ->with('mahs',$id)
                ->with('xas',$xas)
                ->with('huyens',$huyens)
                ->with('dantocs', $dantocs)
                ->with('quoctichs', $quoctichs)
                ->with('model', $model)
                ->with('pageTitle','Thêm mới thông tin thay đổi');

        }else
            return view('errors.notlogin');
    }

    public function updateksbs(Request $request,$id)
    {
        if (Session::has('admin')) {
            $inputs = $request->all();
            $model = ThongTinThayDoi::find($id);
            $inputs['ngaydk'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaydk'])));
            $inputs['ngaysinhntd'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaysinhntd'])));
            $model->update($inputs);
            return redirect('khaisinh');

        }else
            return view('errors.notlogin');
    }

    public function destroyksbs(Request $request){
        if (Session::has('admin')) {
            $inputs = $request->all();
            $id = $inputs['iddelete'];
            $model = ThongTinThayDoi::find($id);
            $model->delete();
            return redirect('khaisinh');

        }else
            return view('errors.notlogin');
    }

   //Khai tử

    public function ktcreate($id)
    {
        if (Session::has('admin')) {
            if(session('admin')->level == 'T'){
                $huyens = Districts::all();
                $huyendf = Districts::first()->mahuyen;
                $xas = Towns::where('mahuyen',$huyendf)
                    ->get();
                $xadf = $xas->first()->maxa;
            }elseif(session('admin')->level =='H'){
                $huyens = Districts::all();
                $huyendf = Districts::where('mahuyen',session('admin')->mahuyen)->first()->mahuyen;
                $xas = Towns::where('mahuyen',$huyendf)
                    ->get();
                $xadf = $xas->first()->maxa;
            }else{
                $huyens = Districts::all();
                $huyendf = Districts::where('mahuyen',session('admin')->mahuyen)->first()->mahuyen;
                $xas = Towns::where('mahuyen',$huyendf)
                    ->get();
                $xadf = $xas->where('maxa',session('admin')->maxa)->first()->maxa;
            }

            $dantocs = getDanTocSelectOptions();
            $quoctichs = getQuocTichSelectOptions();
            return view('manage.khaitu.include.thaydoi')
                ->with('huyens',$huyens)
                ->with('mahuyen',$huyendf)
                ->with('xas',$xas)
                ->with('maxa',$xadf)
                ->with('mahs',$id)
                ->with('dantocs', $dantocs)
                ->with('quoctichs', $quoctichs)
                ->with('pageTitle','Thêm mới thông tin thay đổi');
        }else
            return view('errors.notlogin');
    }

    public function luukhaitubs(Request $request){
        if (Session::has('admin')) {
            $inputs = $request->all();
            $modelsohotich =  SoHoTich::where('plhotich','Thay đổi bổ xung')
                ->where('namso',date('Y'))->first()->quyenhotich;
            $inputs['quyentd'] = (isset($modelsohotich)) ? $modelsohotich : getmatinh().$inputs['mahuyen'].$inputs['maxa'].'KS'.date('Y');

            $inputs['sotd'] = $this->getSothaydoiKT($inputs['maxa'],$inputs['mahuyen'],$inputs['quyentd'] );
            $inputs['ngaydk'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaydk'])));
            $inputs['ngaysinhntd'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaysinhntd'])));
            $inputs['plgiayto'] = "Khai tử";
            $inputs['trangthai'] = "Chờ duyệt";
            $model = new ThongTinThayDoi();
            $model->create($inputs);
            return redirect('khaitu');
        }else
            return view('errors.notlogin');
    }

    public function getSothaydoiKT($maxa,$mahuyen,$quyentd){
        $idmax = ThongTinThayDoi::where('maxa',$maxa)
            ->where('mahuyen',$mahuyen)
            ->where('quyentd',$quyentd)
            ->max('id');
        if($idmax==null)
            $stt = 1;
        else{
            $model =ThongTinThayDoi::where('id', $idmax)->first();
            $stt = $model->sotd + 1;
        }
        return $stt;
    }

    public function duyetkt(Request $request){
        if (Session::has('admin')) {
            $inputs = $request->all();
            $id = $inputs['idduyet'];
            $model = ThongTinThayDoi::find($id);
            $model->trangthai = 'Duyệt';
            $model->save();
            return redirect('khaitu');

        }else
            return view('errors.notlogin');
    }

    public function showkt($id)
    {
        if (Session::has('admin')) {

            $model = ThongTinThayDoi::find($id);
            $xas = Towns::where('maxa',$model->maxa)->first()->tenxa;
            $huyens = Districts::where('mahuyen',$model->mahuyen)->first()->tenhuyen;
            $dantocs = getDanTocSelectOptions();
            $quoctichs = getQuocTichSelectOptions();
            return view('manage.khaitu.include.showthaydoi')
                ->with('mahs',$id)
                ->with('xas',$xas)
                ->with('huyens',$huyens)
                ->with('dantocs', $dantocs)
                ->with('quoctichs', $quoctichs)
                ->with('model', $model)
                ->with('pageTitle','Thêm mới thông tin thay đổi');

        }else
            return view('errors.notlogin');
    }

    public function updatektbs(Request $request,$id)
    {
        if (Session::has('admin')) {
            $inputs = $request->all();
            $model = ThongTinThayDoi::find($id);
            $inputs['ngaydk'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaydk'])));
            $inputs['ngaysinhntd'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaysinhntd'])));
            $model->update($inputs);
            return redirect('khaitu');

        }else
            return view('errors.notlogin');
    }

    public function destroyktbs(Request $request){
        if (Session::has('admin')) {
            $inputs = $request->all();
            $id = $inputs['iddelete'];
            $model = ThongTinThayDoi::find($id);
            $model->delete();
            return redirect('khaitu');

        }else
            return view('errors.notlogin');
    }

    //Xác nhận tình trạng hôn nhân

    public function tthncreate($id)
    {
        if (Session::has('admin')) {
            if(session('admin')->level == 'T'){
                $huyens = Districts::all();
                $huyendf = Districts::first()->mahuyen;
                $xas = Towns::where('mahuyen',$huyendf)
                    ->get();
                $xadf = $xas->first()->maxa;
            }elseif(session('admin')->level =='H'){
                $huyens = Districts::all();
                $huyendf = Districts::where('mahuyen',session('admin')->mahuyen)->first()->mahuyen;
                $xas = Towns::where('mahuyen',$huyendf)
                    ->get();
                $xadf = $xas->first()->maxa;
            }else{
                $huyens = Districts::all();
                $huyendf = Districts::where('mahuyen',session('admin')->mahuyen)->first()->mahuyen;
                $xas = Towns::where('mahuyen',$huyendf)
                    ->get();
                $xadf = $xas->where('maxa',session('admin')->maxa)->first()->maxa;
            }

            $dantocs = getDanTocSelectOptions();
            $quoctichs = getQuocTichSelectOptions();
            return view('manage.tthonnhan.include.thaydoi')
                ->with('huyens',$huyens)
                ->with('mahuyen',$huyendf)
                ->with('xas',$xas)
                ->with('maxa',$xadf)
                ->with('mahs',$id)
                ->with('dantocs', $dantocs)
                ->with('quoctichs', $quoctichs)
                ->with('pageTitle','Thêm mới thông tin thay đổi');
        }else
            return view('errors.notlogin');
    }

    public function luutthnbs(Request $request){
        if (Session::has('admin')) {
            $inputs = $request->all();
            $modelsohotich =  SoHoTich::where('plhotich','Thay đổi bổ xung')
                ->where('namso',date('Y'))->first()->quyenhotich;
            $inputs['quyentd'] = (isset($modelsohotich)) ? $modelsohotich : getmatinh().$inputs['mahuyen'].$inputs['maxa'].'KS'.date('Y');

            $inputs['sotd'] = $this->getSothaydoiTTHN($inputs['maxa'],$inputs['mahuyen'],$inputs['quyentd'] );
            $inputs['ngaydk'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaydk'])));
            $inputs['ngaysinhntd'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaysinhntd'])));
            $inputs['plgiayto'] = "Tình trạng hôn nhân";
            $inputs['trangthai'] = "Chờ duyệt";
            $model = new ThongTinThayDoi();
            $model->create($inputs);
            return redirect('tthonnhan');
        }else
            return view('errors.notlogin');
    }

    public function getSothaydoiTTHN($maxa,$mahuyen,$quyentd){
        $idmax = ThongTinThayDoi::where('maxa',$maxa)
            ->where('mahuyen',$mahuyen)
            ->where('quyentd',$quyentd)
            ->max('id');
        if($idmax==null)
            $stt = 1;
        else{
            $model =ThongTinThayDoi::where('id', $idmax)->first();
            $stt = $model->sotd + 1;
        }
        return $stt;
    }

    public function duyettthn(Request $request){
        if (Session::has('admin')) {
            $inputs = $request->all();
            $id = $inputs['idduyet'];
            $model = ThongTinThayDoi::find($id);
            $model->trangthai = 'Duyệt';
            $model->save();
            return redirect('tthonnhan');

        }else
            return view('errors.notlogin');
    }

    public function showtthn($id)
    {
        if (Session::has('admin')) {

            $model = ThongTinThayDoi::find($id);
            $xas = Towns::where('maxa',$model->maxa)->first()->tenxa;
            $huyens = Districts::where('mahuyen',$model->mahuyen)->first()->tenhuyen;
            $dantocs = getDanTocSelectOptions();
            $quoctichs = getQuocTichSelectOptions();
            return view('manage.tthonnhan.include.showthaydoi')
                ->with('mahs',$id)
                ->with('xas',$xas)
                ->with('huyens',$huyens)
                ->with('dantocs', $dantocs)
                ->with('quoctichs', $quoctichs)
                ->with('model', $model)
                ->with('pageTitle','Thêm mới thông tin thay đổi');

        }else
            return view('errors.notlogin');
    }

    public function updatetthnbs(Request $request,$id)
    {
        if (Session::has('admin')) {
            $inputs = $request->all();
            $model = ThongTinThayDoi::find($id);
            $inputs['ngaydk'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaydk'])));
            $inputs['ngaysinhntd'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaysinhntd'])));
            $model->update($inputs);
            return redirect('tthonnhan');

        }else
            return view('errors.notlogin');
    }

    public function destroytthnbs(Request $request){
        if (Session::has('admin')) {
            $inputs = $request->all();
            $id = $inputs['iddelete'];
            $model = ThongTinThayDoi::find($id);
            $model->delete();
            return redirect('tthonnhan');

        }else
            return view('errors.notlogin');
    }

    //Thông tin kết hôn

    public function khcreate($id)
    {
        if (Session::has('admin')) {
            if(session('admin')->level == 'T'){
                $huyens = Districts::all();
                $huyendf = Districts::first()->mahuyen;
                $xas = Towns::where('mahuyen',$huyendf)
                    ->get();
                $xadf = $xas->first()->maxa;
            }elseif(session('admin')->level =='H'){
                $huyens = Districts::all();
                $huyendf = Districts::where('mahuyen',session('admin')->mahuyen)->first()->mahuyen;
                $xas = Towns::where('mahuyen',$huyendf)
                    ->get();
                $xadf = $xas->first()->maxa;
            }else{
                $huyens = Districts::all();
                $huyendf = Districts::where('mahuyen',session('admin')->mahuyen)->first()->mahuyen;
                $xas = Towns::where('mahuyen',$huyendf)
                    ->get();
                $xadf = $xas->where('maxa',session('admin')->maxa)->first()->maxa;
            }

            $dantocs = getDanTocSelectOptions();
            $quoctichs = getQuocTichSelectOptions();
            return view('manage.kethon.include.thaydoi')
                ->with('huyens',$huyens)
                ->with('mahuyen',$huyendf)
                ->with('xas',$xas)
                ->with('maxa',$xadf)
                ->with('mahs',$id)
                ->with('dantocs', $dantocs)
                ->with('quoctichs', $quoctichs)
                ->with('pageTitle','Thêm mới thông tin thay đổi');
        }else
            return view('errors.notlogin');
    }

    public function luukethonbs(Request $request){
        if (Session::has('admin')) {
            $inputs = $request->all();
            $modelsohotich =  SoHoTich::where('plhotich','Thay đổi bổ xung')
                ->where('namso',date('Y'))->first()->quyenhotich;
            $inputs['quyentd'] = (isset($modelsohotich)) ? $modelsohotich : getmatinh().$inputs['mahuyen'].$inputs['maxa'].'KS'.date('Y');

            $inputs['sotd'] = $this->getSothaydoiKH($inputs['maxa'],$inputs['mahuyen'],$inputs['quyentd'] );
            $inputs['ngaydk'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaydk'])));
            $inputs['ngaysinhntd'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaysinhntd'])));
            $inputs['plgiayto'] = "Kết hôn";
            $inputs['trangthai'] = "Chờ duyệt";
            $model = new ThongTinThayDoi();
            $model->create($inputs);
            return redirect('kethon');
        }else
            return view('errors.notlogin');
    }

    public function getSothaydoiKH($maxa,$mahuyen,$quyentd){
        $idmax = ThongTinThayDoi::where('maxa',$maxa)
            ->where('mahuyen',$mahuyen)
            ->where('quyentd',$quyentd)
            ->max('id');
        if($idmax==null)
            $stt = 1;
        else{
            $model =ThongTinThayDoi::where('id', $idmax)->first();
            $stt = $model->sotd + 1;
        }
        return $stt;
    }

    public function duyetkh(Request $request){
        if (Session::has('admin')) {
            $inputs = $request->all();
            $id = $inputs['idduyet'];
            $model = ThongTinThayDoi::find($id);
            $model->trangthai = 'Duyệt';
            $model->save();
            return redirect('kethon');

        }else
            return view('errors.notlogin');
    }

    public function showkh($id)
    {
        if (Session::has('admin')) {

            $model = ThongTinThayDoi::find($id);
            $xas = Towns::where('maxa',$model->maxa)->first()->tenxa;
            $huyens = Districts::where('mahuyen',$model->mahuyen)->first()->tenhuyen;
            $dantocs = getDanTocSelectOptions();
            $quoctichs = getQuocTichSelectOptions();
            return view('manage.kethon.include.showthaydoi')
                ->with('mahs',$id)
                ->with('xas',$xas)
                ->with('huyens',$huyens)
                ->with('dantocs', $dantocs)
                ->with('quoctichs', $quoctichs)
                ->with('model', $model)
                ->with('pageTitle','Thêm mới thông tin thay đổi');

        }else
            return view('errors.notlogin');
    }

    public function updatekhbs(Request $request,$id)
    {
        if (Session::has('admin')) {
            $inputs = $request->all();
            $model = ThongTinThayDoi::find($id);
            $inputs['ngaydk'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaydk'])));
            $inputs['ngaysinhntd'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaysinhntd'])));
            $model->update($inputs);
            return redirect('kethon');

        }else
            return view('errors.notlogin');
    }

    public function destroykhbs(Request $request){
        if (Session::has('admin')) {
            $inputs = $request->all();
            $id = $inputs['iddelete'];
            $model = ThongTinThayDoi::find($id);
            $model->delete();
            return redirect('kethon');

        }else
            return view('errors.notlogin');
    }

    //Đăng ký con nuôi

    public function cncreate($id)
    {
        if (Session::has('admin')) {
            if(session('admin')->level == 'T'){
                $huyens = Districts::all();
                $huyendf = Districts::first()->mahuyen;
                $xas = Towns::where('mahuyen',$huyendf)
                    ->get();
                $xadf = $xas->first()->maxa;
            }elseif(session('admin')->level =='H'){
                $huyens = Districts::all();
                $huyendf = Districts::where('mahuyen',session('admin')->mahuyen)->first()->mahuyen;
                $xas = Towns::where('mahuyen',$huyendf)
                    ->get();
                $xadf = $xas->first()->maxa;
            }else{
                $huyens = Districts::all();
                $huyendf = Districts::where('mahuyen',session('admin')->mahuyen)->first()->mahuyen;
                $xas = Towns::where('mahuyen',$huyendf)
                    ->get();
                $xadf = $xas->where('maxa',session('admin')->maxa)->first()->maxa;
            }

            $dantocs = getDanTocSelectOptions();
            $quoctichs = getQuocTichSelectOptions();
            return view('manage.connuoi.include.thaydoi')
                ->with('huyens',$huyens)
                ->with('mahuyen',$huyendf)
                ->with('xas',$xas)
                ->with('maxa',$xadf)
                ->with('mahs',$id)
                ->with('dantocs', $dantocs)
                ->with('quoctichs', $quoctichs)
                ->with('pageTitle','Thêm mới thông tin thay đổi');
        }else
            return view('errors.notlogin');
    }

    public function luuconnuoibs(Request $request){
        if (Session::has('admin')) {
            $inputs = $request->all();
            $modelsohotich =  SoHoTich::where('plhotich','Thay đổi bổ xung')
                ->where('namso',date('Y'))->first()->quyenhotich;
            $inputs['quyentd'] = (isset($modelsohotich)) ? $modelsohotich : getmatinh().$inputs['mahuyen'].$inputs['maxa'].'KS'.date('Y');

            $inputs['sotd'] = $this->getSothaydoiCN($inputs['maxa'],$inputs['mahuyen'],$inputs['quyentd'] );
            $inputs['ngaydk'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaydk'])));
            $inputs['ngaysinhntd'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaysinhntd'])));
            $inputs['plgiayto'] = "Con nuôi";
            $inputs['trangthai'] = "Chờ duyệt";
            $model = new ThongTinThayDoi();
            $model->create($inputs);
            return redirect('dangkyconnuoi');
        }else
            return view('errors.notlogin');
    }

    public function getSothaydoiCN($maxa,$mahuyen,$quyentd){
        $idmax = ThongTinThayDoi::where('maxa',$maxa)
            ->where('mahuyen',$mahuyen)
            ->where('quyentd',$quyentd)
            ->max('id');
        if($idmax==null)
            $stt = 1;
        else{
            $model =ThongTinThayDoi::where('id', $idmax)->first();
            $stt = $model->sotd + 1;
        }
        return $stt;
    }

    public function duyetcn(Request $request){
        if (Session::has('admin')) {
            $inputs = $request->all();
            $id = $inputs['idduyet'];
            $model = ThongTinThayDoi::find($id);
            $model->trangthai = 'Duyệt';
            $model->save();
            return redirect('dangkyconnuoi');

        }else
            return view('errors.notlogin');
    }

    public function showcn($id)
    {
        if (Session::has('admin')) {

            $model = ThongTinThayDoi::find($id);
            $xas = Towns::where('maxa',$model->maxa)->first()->tenxa;
            $huyens = Districts::where('mahuyen',$model->mahuyen)->first()->tenhuyen;
            $dantocs = getDanTocSelectOptions();
            $quoctichs = getQuocTichSelectOptions();
            return view('manage.connuoi.include.showthaydoi')
                ->with('mahs',$id)
                ->with('xas',$xas)
                ->with('huyens',$huyens)
                ->with('dantocs', $dantocs)
                ->with('quoctichs', $quoctichs)
                ->with('model', $model)
                ->with('pageTitle','Thêm mới thông tin thay đổi');

        }else
            return view('errors.notlogin');
    }

    public function updatecnbs(Request $request,$id)
    {
        if (Session::has('admin')) {
            $inputs = $request->all();
            $model = ThongTinThayDoi::find($id);
            $inputs['ngaydk'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaydk'])));
            $inputs['ngaysinhntd'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaysinhntd'])));
            $model->update($inputs);
            return redirect('dangkyconnuoi');

        }else
            return view('errors.notlogin');
    }

    public function destroycnbs(Request $request){
        if (Session::has('admin')) {
            $inputs = $request->all();
            $id = $inputs['iddelete'];
            $model = ThongTinThayDoi::find($id);
            $model->delete();
            return redirect('dangkyconnuoi');

        }else
            return view('errors.notlogin');
    }

    //Đăng ký giám hộ

    public function ghcreate($id)
    {
        $gh = giamho::where('mahs',$id)->first();
        if (Session::has('admin')) {
            if(session('admin')->level == 'T'){
                $huyens = Districts::all();
                $huyendf = Districts::first()->mahuyen;
                $xas = Towns::where('mahuyen',$huyendf)
                    ->get();
                $xadf = $xas->first()->maxa;
            }elseif(session('admin')->level =='H'){
                $huyens = Districts::all();
                $huyendf = Districts::where('mahuyen',session('admin')->mahuyen)->first()->mahuyen;
                $xas = Towns::where('mahuyen',$huyendf)
                    ->get();
                $xadf = $xas->first()->maxa;
            }else{
                $huyens = Districts::all();
                $huyendf = Districts::where('mahuyen',session('admin')->mahuyen)->first()->mahuyen;
                $xas = Towns::where('mahuyen',$huyendf)
                    ->get();
                $xadf = $xas->where('maxa',session('admin')->maxa)->first()->maxa;
            }

            $dantocs = getDanTocSelectOptions();
            $quoctichs = getQuocTichSelectOptions();
            return view('manage.giamho.include.thaydoi')
                ->with('huyens',$huyens)
                ->with('mahuyen',$huyendf)
                ->with('xas',$xas)
                ->with('maxa',$xadf)
                ->with('mahs',$id)
                ->with('gh',$gh)
                ->with('dantocs', $dantocs)
                ->with('quoctichs', $quoctichs)
                ->with('pageTitle','Thêm mới thông tin thay đổi');
        }else
            return view('errors.notlogin');
    }

    public function luugiamhobs(Request $request){
        if (Session::has('admin')) {
            $inputs = $request->all();
            $modelsohotich =  SoHoTich::where('plhotich','Thay đổi bổ xung')
                ->where('namso',date('Y'))->first()->quyenhotich;
            $inputs['quyentd'] = (isset($modelsohotich)) ? $modelsohotich : getmatinh().$inputs['mahuyen'].$inputs['maxa'].'KS'.date('Y');

            $inputs['sotd'] = $this->getSothaydoiGH($inputs['maxa'],$inputs['mahuyen'],$inputs['quyentd'] );
            $inputs['ngaydk'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaydk'])));
            $inputs['ngaysinhntd'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaysinhntd'])));
            $inputs['plgiayto'] = $inputs['plgh'];
            $inputs['trangthai'] = "Chờ duyệt";
            $model = new ThongTinThayDoi();
            $model->create($inputs);
            return redirect('dangkygiamho');
        }else
            return view('errors.notlogin');
    }

    public function getSothaydoiGH($maxa,$mahuyen,$quyentd){
        $idmax = ThongTinThayDoi::where('maxa',$maxa)
            ->where('mahuyen',$mahuyen)
            ->where('quyentd',$quyentd)
            ->max('id');
        if($idmax==null)
            $stt = 1;
        else{
            $model =ThongTinThayDoi::where('id', $idmax)->first();
            $stt = $model->sotd + 1;
        }
        return $stt;
    }

    public function duyetgh(Request $request){
        if (Session::has('admin')) {
            $inputs = $request->all();
            $id = $inputs['idduyet'];
            $model = ThongTinThayDoi::find($id);
            $model->trangthai = 'Duyệt';
            $model->save();
            return redirect('dangkygiamho');

        }else
            return view('errors.notlogin');
    }

    public function showgh($id)
    {
        if (Session::has('admin')) {

            $model = ThongTinThayDoi::find($id);
            $xas = Towns::where('maxa',$model->maxa)->first()->tenxa;
            $huyens = Districts::where('mahuyen',$model->mahuyen)->first()->tenhuyen;
            $dantocs = getDanTocSelectOptions();
            $quoctichs = getQuocTichSelectOptions();
            return view('manage.giamho.include.showthaydoi')
                ->with('mahs',$id)
                ->with('xas',$xas)
                ->with('huyens',$huyens)
                ->with('dantocs', $dantocs)
                ->with('quoctichs', $quoctichs)
                ->with('model', $model)
                ->with('pageTitle','Thêm mới thông tin thay đổi');

        }else
            return view('errors.notlogin');
    }

    public function updateghbs(Request $request,$id)
    {
        if (Session::has('admin')) {
            $inputs = $request->all();
            $model = ThongTinThayDoi::find($id);
            $inputs['ngaydk'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaydk'])));
            $inputs['ngaysinhntd'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaysinhntd'])));
            $model->update($inputs);
            return redirect('dangkygiamho');

        }else
            return view('errors.notlogin');
    }

    public function destroyghbs(Request $request){
        if (Session::has('admin')) {
            $inputs = $request->all();
            $id = $inputs['iddelete'];
            $model = ThongTinThayDoi::find($id);
            $model->delete();
            return redirect('dangkygiamho');

        }else
            return view('errors.notlogin');
    }

    //Đăng ký nhận cha mẹ con

    public function cmccreate($id)
    {
        if (Session::has('admin')) {
            if(session('admin')->level == 'T'){
                $huyens = Districts::all();
                $huyendf = Districts::first()->mahuyen;
                $xas = Towns::where('mahuyen',$huyendf)
                    ->get();
                $xadf = $xas->first()->maxa;
            }elseif(session('admin')->level =='H'){
                $huyens = Districts::all();
                $huyendf = Districts::where('mahuyen',session('admin')->mahuyen)->first()->mahuyen;
                $xas = Towns::where('mahuyen',$huyendf)
                    ->get();
                $xadf = $xas->first()->maxa;
            }else{
                $huyens = Districts::all();
                $huyendf = Districts::where('mahuyen',session('admin')->mahuyen)->first()->mahuyen;
                $xas = Towns::where('mahuyen',$huyendf)
                    ->get();
                $xadf = $xas->where('maxa',session('admin')->maxa)->first()->maxa;
            }

            $dantocs = getDanTocSelectOptions();
            $quoctichs = getQuocTichSelectOptions();
            return view('manage.chamecon.include.thaydoi')
                ->with('huyens',$huyens)
                ->with('mahuyen',$huyendf)
                ->with('xas',$xas)
                ->with('maxa',$xadf)
                ->with('mahs',$id)
                ->with('dantocs', $dantocs)
                ->with('quoctichs', $quoctichs)
                ->with('pageTitle','Thêm mới thông tin thay đổi');
        }else
            return view('errors.notlogin');
    }

    public function luucmcbs(Request $request){
        if (Session::has('admin')) {
            $inputs = $request->all();
            $modelsohotich =  SoHoTich::where('plhotich','Thay đổi bổ xung')
                ->where('namso',date('Y'))->first()->quyenhotich;
            $inputs['quyentd'] = (isset($modelsohotich)) ? $modelsohotich : getmatinh().$inputs['mahuyen'].$inputs['maxa'].'KS'.date('Y');

            $inputs['sotd'] = $this->getSothaydoiCMC($inputs['maxa'],$inputs['mahuyen'],$inputs['quyentd'] );
            $inputs['ngaydk'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaydk'])));
            $inputs['ngaysinhntd'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaysinhntd'])));
            $inputs['plgiayto'] = "Cha mẹ con";
            $inputs['trangthai'] = "Chờ duyệt";
            $model = new ThongTinThayDoi();
            $model->create($inputs);
            return redirect('dangkynhanchamecon');
        }else
            return view('errors.notlogin');
    }

    public function getSothaydoiCMC($maxa,$mahuyen,$quyentd){
        $idmax = ThongTinThayDoi::where('maxa',$maxa)
            ->where('mahuyen',$mahuyen)
            ->where('quyentd',$quyentd)
            ->max('id');
        if($idmax==null)
            $stt = 1;
        else{
            $model =ThongTinThayDoi::where('id', $idmax)->first();
            $stt = $model->sotd + 1;
        }
        return $stt;
    }

    public function duyetcmc(Request $request){
        if (Session::has('admin')) {
            $inputs = $request->all();
            $id = $inputs['idduyet'];
            $model = ThongTinThayDoi::find($id);
            $model->trangthai = 'Duyệt';
            $model->save();
            return redirect('dangkynhanchamecon');

        }else
            return view('errors.notlogin');
    }

    public function showcmc($id)
    {
        if (Session::has('admin')) {

            $model = ThongTinThayDoi::find($id);
            $xas = Towns::where('maxa',$model->maxa)->first()->tenxa;
            $huyens = Districts::where('mahuyen',$model->mahuyen)->first()->tenhuyen;
            $dantocs = getDanTocSelectOptions();
            $quoctichs = getQuocTichSelectOptions();
            return view('manage.chamecon.include.showthaydoi')
                ->with('mahs',$id)
                ->with('xas',$xas)
                ->with('huyens',$huyens)
                ->with('dantocs', $dantocs)
                ->with('quoctichs', $quoctichs)
                ->with('model', $model)
                ->with('pageTitle','Thêm mới thông tin thay đổi');

        }else
            return view('errors.notlogin');
    }

    public function updatecmcbs(Request $request,$id)
    {
        if (Session::has('admin')) {
            $inputs = $request->all();
            $model = ThongTinThayDoi::find($id);
            $inputs['ngaydk'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaydk'])));
            $inputs['ngaysinhntd'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaysinhntd'])));
            $model->update($inputs);
            return redirect('dangkynhanchamecon');

        }else
            return view('errors.notlogin');
    }

    public function destroycmcbs(Request $request){
        if (Session::has('admin')) {
            $inputs = $request->all();
            $id = $inputs['iddelete'];
            $model = ThongTinThayDoi::find($id);
            $model->delete();
            return redirect('dangkynhanchamecon');

        }else
            return view('errors.notlogin');
    }

    public function printstokhaitdks($id){
        if (Session::has('admin')) {
            $model = thongtinthaydoi::find($id);
            $khaisinh = KhaiSinh::Where('mahs',$model->mahs)->first();
            $modelxa = Towns::where('maxa',$model->maxa)->first();
            $modelhuyen = Towns::where('mahuyen',$model->mahuyen)->first();
            $xa = $modelxa->tenxa;
            $huyen = $modelhuyen->tenhuyen;
            $tinh = GeneralConfigs::first()->tendv;
            if($xa == "Thị Trấn Yên Minh")
            {
                $tencq = 'Thị trấn Yên Minh , '.$huyen .' , Tỉnh '.$tinh;
            }
            else
            {
                $tencq = $xa.' , '.$huyen .' , Tỉnh '.$tinh;
            }
            return view('reports.khaisinh.printtokhaitdks')
                ->with('model',$model)
                ->with('xa',$xa)
                ->with('khaisinh',$khaisinh)
                ->with('tencq',$tencq)
                ->with('pageTitle','In giấy đăng ký khai sinh');

        }else
            return view('errors.notlogin');
    }

    public function printstrichlucksbs($id){
        if (Session::has('admin')) {
            $model = thongtinthaydoi::find($id);
            $khaisinh = KhaiSinh::Where('mahs',$model->mahs)->first();
            $modelxa = Towns::where('maxa',$model->maxa)->first();
            $modelhuyen = Towns::where('mahuyen',$model->mahuyen)->first();
            $xa = $modelxa->tenxa;
            $tenxa = substr("$xa",8);
            $huyen = $modelhuyen->tenhuyen;
            $tinh = GeneralConfigs::first()->tendv;
            if($xa == "Thị Trấn Yên Minh")
            {
                $tencq = 'Thị trấn Yên Minh , '.$huyen .' , Tỉnh '.$tinh;
            }
            else
            {
                $tencq = $xa.' , '.$huyen .' , Tỉnh '.$tinh;
            }
            return view('reports.khaisinh.printstrichlucksbs')
                ->with('model',$model)
                ->with('xa',$xa)
                ->with('khaisinh',$khaisinh)
                ->with('tencq',$tencq)
                ->with('tinh',$tinh)
                ->with('tenxa',$tenxa)
                ->with('huyen',$huyen)
                ->with('pageTitle','In trích lục thay đổi');

        }else
            return view('errors.notlogin');
    }

    public function printstrichlucksbc($id){
        if (Session::has('admin')) {
            $model = thongtinthaydoi::find($id);
            $khaisinh = KhaiSinh::Where('mahs',$model->mahs)->first();
            $modelxa = Towns::where('maxa',$model->maxa)->first();
            $modelhuyen = Towns::where('mahuyen',$model->mahuyen)->first();
            $xa = $modelxa->tenxa;
            $tenxa = substr("$xa",8);
            $huyen = $modelhuyen->tenhuyen;
            $tinh = GeneralConfigs::first()->tendv;
            if($xa == "Thị Trấn Yên Minh")
            {
                $tencq = 'Thị trấn Yên Minh , '.$huyen .' , Tỉnh '.$tinh;
            }
            else
            {
                $tencq = $xa.' , '.$huyen .' , Tỉnh '.$tinh;
            }
            return view('reports.khaisinh.printstrichlucksbc')
                ->with('model',$model)
                ->with('xa',$xa)
                ->with('khaisinh',$khaisinh)
                ->with('tencq',$tencq)
                ->with('tinh',$tinh)
                ->with('tenxa',$tenxa)
                ->with('huyen',$huyen)
                ->with('pageTitle','In trích lục thay đổi');

        }else
            return view('errors.notlogin');
    }

    public function printstokhaitdkt($id){
        if (Session::has('admin')) {
            $model = thongtinthaydoi::find($id);
            $khaitu = KhaiTu::Where('masohoso',$model->mahs)->first();
            $modelxa = Towns::where('maxa',$model->maxa)->first();
            $modelhuyen = Towns::where('mahuyen',$model->mahuyen)->first();
            $xa = $modelxa->tenxa;
            $huyen = $modelhuyen->tenhuyen;
            $tinh = GeneralConfigs::first()->tendv;
            if($xa == "Thị Trấn Yên Minh")
            {
                $tencq = 'Thị trấn Yên Minh , '.$huyen .' , Tỉnh '.$tinh;
            }
            else
            {
                $tencq = $xa.' , '.$huyen .' , Tỉnh '.$tinh;
            }
            return view('reports.khaitu.printtokhaitdkt')
                ->with('model',$model)
                ->with('xa',$xa)
                ->with('khaitu',$khaitu)
                ->with('tencq',$tencq)
                ->with('pageTitle','In giấy đăng ký khai sinh');

        }else
            return view('errors.notlogin');
    }

    public function printstrichlucktbs($id){
        if (Session::has('admin')) {
            $model = thongtinthaydoi::find($id);
            $modeltt = KhaiTu::Where('masohoso',$model->mahs)->first();
            $modelxa = Towns::where('maxa',$model->maxa)->first();
            $modelhuyen = Towns::where('mahuyen',$model->mahuyen)->first();
            $xa = $modelxa->tenxa;
            $tenxa = substr("$xa",8);
            $huyen = $modelhuyen->tenhuyen;
            $tinh = GeneralConfigs::first()->tendv;
            if($xa == "Thị Trấn Yên Minh")
            {
                $tencq = 'Thị trấn Yên Minh , '.$huyen .' , Tỉnh '.$tinh;
            }
            else
            {
                $tencq = $xa.' , '.$huyen .' , Tỉnh '.$tinh;
            }
            return view('reports.khaitu.printstrichlucktbs')
                ->with('model',$model)
                ->with('xa',$xa)
                ->with('modeltt',$modeltt)
                ->with('tencq',$tencq)
                ->with('tinh',$tinh)
                ->with('tenxa',$tenxa)
                ->with('huyen',$huyen)
                ->with('pageTitle','In trích lục thay đổi');

        }else
            return view('errors.notlogin');
    }

    public function printstrichlucktbc($id){
        if (Session::has('admin')) {
            $model = thongtinthaydoi::find($id);
            $modeltt = KhaiTu::Where('masohoso',$model->mahs)->first();
            $modelxa = Towns::where('maxa',$model->maxa)->first();
            $modelhuyen = Towns::where('mahuyen',$model->mahuyen)->first();
            $xa = $modelxa->tenxa;
            $tenxa = substr("$xa",8);
            $huyen = $modelhuyen->tenhuyen;
            $tinh = GeneralConfigs::first()->tendv;
            if($xa == "Thị Trấn Yên Minh")
            {
                $tencq = 'Thị trấn Yên Minh , '.$huyen .' , Tỉnh '.$tinh;
            }
            else
            {
                $tencq = $xa.' , '.$huyen .' , Tỉnh '.$tinh;
            }
            return view('reports.khaitu.printstrichlucktbc')
                ->with('model',$model)
                ->with('xa',$xa)
                ->with('modeltt',$modeltt)
                ->with('tencq',$tencq)
                ->with('tinh',$tinh)
                ->with('tenxa',$tenxa)
                ->with('huyen',$huyen)
                ->with('pageTitle','In trích lục thay đổi');

        }else
            return view('errors.notlogin');
    }

    public function printstokhaitdtthn($id){
        if (Session::has('admin')) {
            $model = thongtinthaydoi::find($id);
            $tthn = TTHonNhan::Where('mahs',$model->mahs)->first();
            $modelxa = Towns::where('maxa',$model->maxa)->first();
            $modelhuyen = Towns::where('mahuyen',$model->mahuyen)->first();
            $xa = $modelxa->tenxa;
            $huyen = $modelhuyen->tenhuyen;
            $tinh = GeneralConfigs::first()->tendv;
            if($xa == "Thị Trấn Yên Minh")
            {
                $tencq = 'Thị trấn Yên Minh , '.$huyen .' , Tỉnh '.$tinh;
            }
            else
            {
                $tencq = $xa.' , '.$huyen .' , Tỉnh '.$tinh;
            }
            return view('reports.tthonnhan.printtokhaitdtthn')
                ->with('model',$model)
                ->with('xa',$xa)
                ->with('tthn',$tthn)
                ->with('tencq',$tencq)
                ->with('pageTitle','In giấy đăng ký khai sinh');

        }else
            return view('errors.notlogin');
    }

    public function printstrichluctthnbs($id){
        if (Session::has('admin')) {
            $model = thongtinthaydoi::find($id);
            $modeltt = TTHonNhan::Where('mahs',$model->mahs)->first();
            $modelxa = Towns::where('maxa',$model->maxa)->first();
            $modelhuyen = Towns::where('mahuyen',$model->mahuyen)->first();
            $xa = $modelxa->tenxa;
            $tenxa = substr("$xa",8);
            $huyen = $modelhuyen->tenhuyen;
            $tinh = GeneralConfigs::first()->tendv;
            if($xa == "Thị Trấn Yên Minh")
            {
                $tencq = 'Thị trấn Yên Minh , '.$huyen .' , Tỉnh '.$tinh;
            }
            else
            {
                $tencq = $xa.' , '.$huyen .' , Tỉnh '.$tinh;
            }
            return view('reports.tthonnhan.printstrichluctthnbs')
                ->with('model',$model)
                ->with('xa',$xa)
                ->with('modeltt',$modeltt)
                ->with('tencq',$tencq)
                ->with('tinh',$tinh)
                ->with('tenxa',$tenxa)
                ->with('huyen',$huyen)
                ->with('pageTitle','In trích lục thay đổi');

        }else
            return view('errors.notlogin');
    }

    public function printstrichluctthnbc($id){
        if (Session::has('admin')) {
            $model = thongtinthaydoi::find($id);
            $modeltt = TTHonNhan::Where('mahs',$model->mahs)->first();
            $modelxa = Towns::where('maxa',$model->maxa)->first();
            $modelhuyen = Towns::where('mahuyen',$model->mahuyen)->first();
            $xa = $modelxa->tenxa;
            $tenxa = substr("$xa",8);
            $huyen = $modelhuyen->tenhuyen;
            $tinh = GeneralConfigs::first()->tendv;
            if($xa == "Thị Trấn Yên Minh")
            {
                $tencq = 'Thị trấn Yên Minh , '.$huyen .' , Tỉnh '.$tinh;
            }
            else
            {
                $tencq = $xa.' , '.$huyen .' , Tỉnh '.$tinh;
            }
            return view('reports.tthonnhan.printstrichluctthnbc')
                ->with('model',$model)
                ->with('xa',$xa)
                ->with('modeltt',$modeltt)
                ->with('tencq',$tencq)
                ->with('tinh',$tinh)
                ->with('tenxa',$tenxa)
                ->with('huyen',$huyen)
                ->with('pageTitle','In trích lục thay đổi');

        }else
            return view('errors.notlogin');
    }

    public function printstokhaitdkh($id){
        if (Session::has('admin')) {
            $model = thongtinthaydoi::find($id);
            $kethon = KetHon::Where('mahs',$model->mahs)->first();
            $modelxa = Towns::where('maxa',$model->maxa)->first();
            $modelhuyen = Towns::where('mahuyen',$model->mahuyen)->first();
            $xa = $modelxa->tenxa;
            $huyen = $modelhuyen->tenhuyen;
            $tinh = GeneralConfigs::first()->tendv;
            if($xa == "Thị Trấn Yên Minh")
            {
                $tencq = 'Thị trấn Yên Minh , '.$huyen .' , Tỉnh '.$tinh;
            }
            else
            {
                $tencq = $xa.' , '.$huyen .' , Tỉnh '.$tinh;
            }
            return view('reports.kethon.printtokhaitdkh')
                ->with('model',$model)
                ->with('xa',$xa)
                ->with('kethon',$kethon)
                ->with('tencq',$tencq)
                ->with('pageTitle','In giấy đăng ký khai sinh');

        }else
            return view('errors.notlogin');
    }

    public function printstrichluckhbs($id){
        if (Session::has('admin')) {
            $model = thongtinthaydoi::find($id);
            $modeltt = KetHon::Where('mahs',$model->mahs)->first();
            $modelxa = Towns::where('maxa',$model->maxa)->first();
            $modelhuyen = Towns::where('mahuyen',$model->mahuyen)->first();
            $xa = $modelxa->tenxa;
            $tenxa = substr("$xa",8);
            $huyen = $modelhuyen->tenhuyen;
            $tinh = GeneralConfigs::first()->tendv;
            if($xa == "Thị Trấn Yên Minh")
            {
                $tencq = 'Thị trấn Yên Minh , '.$huyen .' , Tỉnh '.$tinh;
            }
            else
            {
                $tencq = $xa.' , '.$huyen .' , Tỉnh '.$tinh;
            }
            return view('reports.kethon.printstrichluckhbs')
                ->with('model',$model)
                ->with('xa',$xa)
                ->with('modeltt',$modeltt)
                ->with('tencq',$tencq)
                ->with('tinh',$tinh)
                ->with('tenxa',$tenxa)
                ->with('huyen',$huyen)
                ->with('pageTitle','In trích lục thay đổi');

        }else
            return view('errors.notlogin');
    }

    public function printstrichluckhbc($id){
        if (Session::has('admin')) {
            $model = thongtinthaydoi::find($id);
            $modeltt = KetHon::Where('mahs',$model->mahs)->first();
            $modelxa = Towns::where('maxa',$model->maxa)->first();
            $modelhuyen = Towns::where('mahuyen',$model->mahuyen)->first();
            $xa = $modelxa->tenxa;
            $tenxa = substr("$xa",8);
            $huyen = $modelhuyen->tenhuyen;
            $tinh = GeneralConfigs::first()->tendv;
            if($xa == "Thị Trấn Yên Minh")
            {
                $tencq = 'Thị trấn Yên Minh , '.$huyen .' , Tỉnh '.$tinh;
            }
            else
            {
                $tencq = $xa.' , '.$huyen .' , Tỉnh '.$tinh;
            }
            return view('reports.kethon.printstrichluckhbc')
                ->with('model',$model)
                ->with('xa',$xa)
                ->with('modeltt',$modeltt)
                ->with('tencq',$tencq)
                ->with('tinh',$tinh)
                ->with('tenxa',$tenxa)
                ->with('huyen',$huyen)
                ->with('pageTitle','In trích lục thay đổi');

        }else
            return view('errors.notlogin');
    }

    public function printstokhaitdcn($id){
        if (Session::has('admin')) {
            $model = thongtinthaydoi::find($id);
            $connuoi = ConNuoi::Where('masohoso',$model->mahs)->first();
            $modelxa = Towns::where('maxa',$model->maxa)->first();
            $modelhuyen = Towns::where('mahuyen',$model->mahuyen)->first();
            $xa = $modelxa->tenxa;
            $huyen = $modelhuyen->tenhuyen;
            $tinh = GeneralConfigs::first()->tendv;
            if($xa == "Thị Trấn Yên Minh")
            {
                $tencq = 'Thị trấn Yên Minh , '.$huyen .' , Tỉnh '.$tinh;
            }
            else
            {
                $tencq = $xa.' , '.$huyen .' , Tỉnh '.$tinh;
            }
            return view('reports.connuoi.printtokhaitdcn')
                ->with('model',$model)
                ->with('xa',$xa)
                ->with('connuoi',$connuoi)
                ->with('tencq',$tencq)
                ->with('pageTitle','In tờ khai');

        }else
            return view('errors.notlogin');
    }

    public function printstrichluccnbs($id){
        if (Session::has('admin')) {
            $model = thongtinthaydoi::find($id);
            $modeltt = ConNuoi::Where('masohoso',$model->mahs)->first();
            $modelxa = Towns::where('maxa',$model->maxa)->first();
            $modelhuyen = Towns::where('mahuyen',$model->mahuyen)->first();
            $xa = $modelxa->tenxa;
            $tenxa = substr("$xa",8);
            $huyen = $modelhuyen->tenhuyen;
            $tinh = GeneralConfigs::first()->tendv;
            if($xa == "Thị Trấn Yên Minh")
            {
                $tencq = 'Thị trấn Yên Minh , '.$huyen .' , Tỉnh '.$tinh;
            }
            else
            {
                $tencq = $xa.' , '.$huyen .' , Tỉnh '.$tinh;
            }
            return view('reports.connuoi.printstrichluccnbs')
                ->with('model',$model)
                ->with('xa',$xa)
                ->with('modeltt',$modeltt)
                ->with('tencq',$tencq)
                ->with('tinh',$tinh)
                ->with('tenxa',$tenxa)
                ->with('huyen',$huyen)
                ->with('pageTitle','In trích lục thay đổi');

        }else
            return view('errors.notlogin');
    }

    public function printstrichluccnbc($id){
        if (Session::has('admin')) {
            $model = thongtinthaydoi::find($id);
            $modeltt = ConNuoi::Where('masohoso',$model->mahs)->first();
            $modelxa = Towns::where('maxa',$model->maxa)->first();
            $modelhuyen = Towns::where('mahuyen',$model->mahuyen)->first();
            $xa = $modelxa->tenxa;
            $tenxa = substr("$xa",8);
            $huyen = $modelhuyen->tenhuyen;
            $tinh = GeneralConfigs::first()->tendv;
            if($xa == "Thị Trấn Yên Minh")
            {
                $tencq = 'Thị trấn Yên Minh , '.$huyen .' , Tỉnh '.$tinh;
            }
            else
            {
                $tencq = $xa.' , '.$huyen .' , Tỉnh '.$tinh;
            }
            return view('reports.connuoi.printstrichluccnbc')
                ->with('model',$model)
                ->with('xa',$xa)
                ->with('modeltt',$modeltt)
                ->with('tencq',$tencq)
                ->with('tinh',$tinh)
                ->with('tenxa',$tenxa)
                ->with('huyen',$huyen)
                ->with('pageTitle','In trích lục thay đổi');

        }else
            return view('errors.notlogin');
    }

    public function printstokhaitdgh($id){
        if (Session::has('admin')) {
            $model = thongtinthaydoi::find($id);
            $giamho = giamho::Where('mahs',$model->mahs)->first();
            $modelxa = Towns::where('maxa',$model->maxa)->first();
            $modelhuyen = Towns::where('mahuyen',$model->mahuyen)->first();
            $xa = $modelxa->tenxa;
            $huyen = $modelhuyen->tenhuyen;
            $tinh = GeneralConfigs::first()->tendv;
            if($xa == "Thị Trấn Yên Minh")
            {
                $tencq = 'Thị trấn Yên Minh , '.$huyen .' , Tỉnh '.$tinh;
            }
            else
            {
                $tencq = $xa.' , '.$huyen .' , Tỉnh '.$tinh;
            }
            return view('reports.giamho.printtokhaitdgh')
                ->with('model',$model)
                ->with('xa',$xa)
                ->with('giamho',$giamho)
                ->with('tencq',$tencq)
                ->with('pageTitle','In tờ khai');

        }else
            return view('errors.notlogin');
    }

    public function printstrichlucghbs($id){
        if (Session::has('admin')) {
            $model = thongtinthaydoi::find($id);
            $modeltt = giamho::Where('mahs',$model->mahs)->first();
            $modelxa = Towns::where('maxa',$model->maxa)->first();
            $modelhuyen = Towns::where('mahuyen',$model->mahuyen)->first();
            $xa = $modelxa->tenxa;
            $tenxa = substr("$xa",8);
            $huyen = $modelhuyen->tenhuyen;
            $tinh = GeneralConfigs::first()->tendv;
            if($xa == "Thị Trấn Yên Minh")
            {
                $tencq = 'Thị trấn Yên Minh , '.$huyen .' , Tỉnh '.$tinh;
            }
            else
            {
                $tencq = $xa.' , '.$huyen .' , Tỉnh '.$tinh;
            }
            return view('reports.giamho.printstrichlucghbs')
                ->with('model',$model)
                ->with('xa',$xa)
                ->with('modeltt',$modeltt)
                ->with('tencq',$tencq)
                ->with('tinh',$tinh)
                ->with('tenxa',$tenxa)
                ->with('huyen',$huyen)
                ->with('pageTitle','In trích lục thay đổi');

        }else
            return view('errors.notlogin');
    }

    public function printstrichlucghbc($id){
        if (Session::has('admin')) {
            $model = thongtinthaydoi::find($id);
            $modeltt = giamho::Where('mahs',$model->mahs)->first();
            $modelxa = Towns::where('maxa',$model->maxa)->first();
            $modelhuyen = Towns::where('mahuyen',$model->mahuyen)->first();
            $xa = $modelxa->tenxa;
            $tenxa = substr("$xa",8);
            $huyen = $modelhuyen->tenhuyen;
            $tinh = GeneralConfigs::first()->tendv;
            if($xa == "Thị Trấn Yên Minh")
            {
                $tencq = 'Thị trấn Yên Minh , '.$huyen .' , Tỉnh '.$tinh;
            }
            else
            {
                $tencq = $xa.' , '.$huyen .' , Tỉnh '.$tinh;
            }
            return view('reports.giamho.printstrichlucghbc')
                ->with('model',$model)
                ->with('xa',$xa)
                ->with('modeltt',$modeltt)
                ->with('tencq',$tencq)
                ->with('tinh',$tinh)
                ->with('tenxa',$tenxa)
                ->with('huyen',$huyen)
                ->with('pageTitle','In trích lục thay đổi');

        }else
            return view('errors.notlogin');
    }

    public function printstokhaitdcmc($id){
        if (Session::has('admin')) {
            $model = thongtinthaydoi::find($id);
            $cmc = chamecon::Where('mahs',$model->mahs)->first();
            $modelxa = Towns::where('maxa',$model->maxa)->first();
            $modelhuyen = Towns::where('mahuyen',$model->mahuyen)->first();
            $xa = $modelxa->tenxa;
            $huyen = $modelhuyen->tenhuyen;
            $tinh = GeneralConfigs::first()->tendv;
            if($xa == "Thị Trấn Yên Minh")
            {
                $tencq = 'Thị trấn Yên Minh , '.$huyen .' , Tỉnh '.$tinh;
            }
            else
            {
                $tencq = $xa.' , '.$huyen .' , Tỉnh '.$tinh;
            }
            return view('reports.cmc.printtokhaitdcmc')
                ->with('model',$model)
                ->with('xa',$xa)
                ->with('cmc',$cmc)
                ->with('tencq',$tencq)
                ->with('pageTitle','In tờ khai');

        }else
            return view('errors.notlogin');
    }

    public function printstrichluccmcbs($id){
        if (Session::has('admin')) {
            $model = thongtinthaydoi::find($id);
            $modeltt = chamecon::Where('mahs',$model->mahs)->first();
            $modelxa = Towns::where('maxa',$model->maxa)->first();
            $modelhuyen = Towns::where('mahuyen',$model->mahuyen)->first();
            $xa = $modelxa->tenxa;
            $tenxa = substr("$xa",8);
            $huyen = $modelhuyen->tenhuyen;
            $tinh = GeneralConfigs::first()->tendv;
            if($xa == "Thị Trấn Yên Minh")
            {
                $tencq = 'Thị trấn Yên Minh , '.$huyen .' , Tỉnh '.$tinh;
            }
            else
            {
                $tencq = $xa.' , '.$huyen .' , Tỉnh '.$tinh;
            }
            return view('reports.cmc.printstrichluccmcbs')
                ->with('model',$model)
                ->with('xa',$xa)
                ->with('modeltt',$modeltt)
                ->with('tencq',$tencq)
                ->with('tinh',$tinh)
                ->with('tenxa',$tenxa)
                ->with('huyen',$huyen)
                ->with('pageTitle','In trích lục thay đổi');

        }else
            return view('errors.notlogin');
    }

    public function printstrichluccmcbc($id){
        if (Session::has('admin')) {
            $model = thongtinthaydoi::find($id);
            $modeltt = chamecon::Where('mahs',$model->mahs)->first();
            $modelxa = Towns::where('maxa',$model->maxa)->first();
            $modelhuyen = Towns::where('mahuyen',$model->mahuyen)->first();
            $xa = $modelxa->tenxa;
            $tenxa = substr("$xa",8);
            $huyen = $modelhuyen->tenhuyen;
            $tinh = GeneralConfigs::first()->tendv;
            if($xa == "Thị Trấn Yên Minh")
            {
                $tencq = 'Thị trấn Yên Minh , '.$huyen .' , Tỉnh '.$tinh;
            }
            else
            {
                $tencq = $xa.' , '.$huyen .' , Tỉnh '.$tinh;
            }
            return view('reports.cmc.printstrichluccmcbc')
                ->with('model',$model)
                ->with('xa',$xa)
                ->with('modeltt',$modeltt)
                ->with('tencq',$tencq)
                ->with('tinh',$tinh)
                ->with('tenxa',$tenxa)
                ->with('huyen',$huyen)
                ->with('pageTitle','In trích lục thay đổi');

        }else
            return view('errors.notlogin');
    }
}
