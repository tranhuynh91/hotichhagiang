<?php

namespace App\Http\Controllers;

use App\chamecon;
use App\Districts;
use App\SoHoTich;
use App\Towns;
use App\ThongTinThayDoi;
use Illuminate\Http\Request;
use App\GeneralConfigs;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;

class chameconController extends Controller
{
    public function index(Request $request){
        if (Session::has('admin')) {
            $inputs = $request->all();
            $thang = isset($inputs['thang']) ? $inputs['thang'] : date('m');
            $nam = isset($inputs['nam']) ? $inputs['nam'] : date('Y');
            if(session('admin')->level == 'T') {
                $huyendf = Districts::first()->mahuyen;
                $huyen = isset($inputs['mahuyen']) ? $inputs['mahuyen'] : $huyendf;
                $xadf = Towns:: where('mahuyen', $huyen)->first()->maxa;
                if(isset($inputs['maxa'])) {
                    if ($inputs['maxa'] == "all")
                        $xa = $xadf;
                    else
                        $xa = $inputs['maxa'];
                }else {
                    $xa = $xadf;
                }

            }elseif(session('admin')->level == 'H' && session('admin')->name == 'Phòng tư Pháp huyện Yên Minh'){
                $huyen = isset($inputs['mahuyen']) ? $inputs['mahuyen'] : session('admin')->mahuyen;
                $xa = 'tpym';}
            elseif(session('admin')->level == 'H' && session('admin')->name == 'Phòng tư Pháp huyện Đồng Văn'){
                $huyen = isset($inputs['mahuyen']) ? $inputs['mahuyen'] : session('admin')->mahuyen;
                $xa = 'tpdv';
            }else{
                $huyen = isset($inputs['mahuyen']) ? $inputs['mahuyen'] : session('admin')->mahuyen;
                $xa = isset($inputs['maxa']) ? $inputs['maxa'] : session('admin')->maxa;
            }

            $huyens = listHuyen();
            $xas = array();
            if($huyen != 'all'){
                $xas = listXa($huyen);
            }

            $model = chamecon::whereMonth('ngaydangky', $thang)
                ->whereYear('ngaydangky', $nam);
            if($huyen != 'all' && $huyen != ''){
                $model = $model->where('mahuyen', $huyen);
            }
            if($xa != 'all' && $xa != ''){
                $model = $model->where('maxa', $xa);
            }else{
                $model = $model->where('maxa', $xa);
            }
            $model = $model->get();

            $count = $model->count();

            return view('manage.chamecon.index')
                -> with('huyens', $huyens)
                -> with('xas', $xas)
                -> with('mahuyen', $huyen)
                -> with('maxa', $xa)
                -> with('thang',$thang)
                -> with('nam',$nam)
                -> with('model', $model)
                -> with('pageTitle','Thông tin đăn ký cha mẹ con ('.$count.' hồ sơ)');
        }else
            return view('errors.notlogin');
    }

    public function create(){
        if (Session::has('admin')) {
            if (session('admin')->level == 'T') {
                $huyens = Districts::all();
                $huyendf = Districts::first()->mahuyen;
                $xas = Towns::where('mahuyen', $huyendf)
                    ->get();
                $xadf = $xas->first()->maxa;
            } elseif (session('admin')->level == 'H') {
                $huyens = Districts::all();
                $huyendf = Districts::where('mahuyen', session('admin')->mahuyen)->first()->mahuyen;
                $xas = Towns::where('mahuyen', $huyendf)
                    ->get();
                $xadf = $xas->first()->maxa;
            } else {
                $huyens = Districts::all();
                $huyendf = Districts::where('mahuyen', session('admin')->mahuyen)->first()->mahuyen;
                $xas = Towns::where('mahuyen', $huyendf)
                    ->get();
                $xadf = $xas->where('maxa', session('admin')->maxa)->first()->maxa;
            }

            $dantocs = getDanTocSelectOptions();
            $quoctichs = getQuocTichSelectOptions();

            return view('manage.chamecon.create')
                ->with('action','create')
                ->with('huyens', $huyens)
                ->with('mahuyen', $huyendf)
                ->with('xas', $xas)
                ->with('maxa', $xadf)
                ->with('dantocs', $dantocs)
                ->with('quoctichs', $quoctichs)
                ->with('pageTitle', 'Thêm mới thông tin giám hộ');
        }else
            return view('errors.notlogin');
    }

    public function store(Request $request){
        if (Session::has('admin')) {

            $inputs = $request->all();

            $modelsohotich =  SoHoTich::where('plhotich','Nhận cha mẹ con')
                ->where('namso',date('Y'))->where('mahuyen',$inputs['mahuyen'])->where('maxa',$inputs['maxa'])->first()->quyenhotich;
            $inputs['soquyen'] = (isset($modelsohotich)) ? $modelsohotich : getmatinh().$inputs['mahuyen'].$inputs['maxa'].'GH'.date('Y');
            $inputs['soso'] = $this->getSoHoTich($inputs['maxa'],$inputs['mahuyen'],$inputs['soquyen'] );
            $inputs['matinh'] = getmatinh();
            $inputs['mahs'] = $inputs['matinh'].$inputs['mahuyen'].$inputs['maxa'].'CMC'.getdate()[0];
            $inputs['ngaydangky'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaydangky'])));
            $inputs['ngaysinhndn'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaysinhndn'])));
            $inputs['ngaysinhnn'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaysinhnn'])));
            $inputs['ngaycapgtnk'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaycapgtnk'])));
            $inputs['ngaysinhnk'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaysinhnk'])));
            $inputs['trangthai'] = 'Chờ duyệt';
            $model = new chamecon();
            $model->create($inputs);

            return redirect('dangkynhanchamecon');

        }else
            return view('errors.notlogin');
    }

    public function getSoHoTich($maxa,$mahuyen,$quyen){
        $idmax = chamecon::where('maxa',$maxa)
            ->where('mahuyen',$mahuyen)
            ->where('soquyen',$quyen)
            ->max('id');
        if($idmax==null)
            $stt = 1;
        else{
            $model = chamecon::where('id', $idmax)->first();
            $stt = $model->soso + 1;
        }
        return $stt;
    }

    public function show($id){
        if (Session::has('admin')) {

            $model = chamecon::find($id);
            if(session('admin')->level == 'H' && session('admin')->name == 'Phòng tư Pháp huyện Yên Minh')
            {
                $xa = "tpym";
            }
            elseif(session('admin')->level == 'H' && session('admin')->name == 'Phòng tư Pháp huyện Đồng Văn')
            {
                $xa = "tpdv";
            }
            else
            {
                $xa = Towns::where('maxa',$model->maxa)->first()->tenxa;
            }
            $huyen = Districts::where('mahuyen',$model->mahuyen)->first()->tenhuyen;
            $thongtinthaydoi = ThongTinThayDoi::where('mahs',$model->mahs)->get();

            return view('manage.chamecon.show')
                ->with('model',$model)
                ->with('xa',$xa)
                ->with('huyen',$huyen)
                ->with('thongtinthaydoi',$thongtinthaydoi)
                ->with('pageTitle', 'Thông tin khai sinh');
        }else
            return view('errors.notlogin');
    }

    public function edit($id){
        if (Session::has('admin')) {

            $model = chamecon::find($id);
            $huyens = Districts::all();
            $huyendf = $model->mahuyen;
            $xas = Towns::where('mahuyen', $huyendf)
                ->get();
            $xadf = $model->maxa;

            $dantocs = getDanTocSelectOptions();
            $quoctichs = getQuocTichSelectOptions();

            return view('manage.chamecon.edit')
                ->with('action','edit')
                ->with('huyens', $huyens)
                ->with('mahuyen', $huyendf)
                ->with('xas', $xas)
                ->with('maxa', $xadf)
                ->with('dantocs', $dantocs)
                ->with('quoctichs', $quoctichs)
                ->with('model',$model)
                ->with('pageTitle', 'Chỉnh sửa thông tin đăng ký cha mẹ con');
        }else
            return view('errors.notlogin');
    }

    public function update(Request $request,$id){
        if (Session::has('admin')) {
            $inputs = $request->all();
            $inputs['ngaydangky'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaydangky'])));
            $inputs['ngaysinhndn'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaysinhndn'])));
            $inputs['ngaysinhnn'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaysinhnn'])));
            $inputs['ngaycapgtnk'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaycapgtnk'])));
            $inputs['ngaysinhnk'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaysinhnk'])));
            $inputs['trangthai'] = 'Chờ duyệt';
            $model = chamecon::find($id);
            $model->update($inputs);
            return redirect('dangkynhanchamecon');

        }else
            return view('errors.notlogin');
    }

    public function destroy(Request $request){
        if (Session::has('admin')) {
            $inputs = $request->all();
            $id = $inputs['iddelete'];
            $model = chamecon::find($id);
            $model->delete();
            return redirect('dangkynhanchamecon');

        }else
            return view('errors.notlogin');
    }

    public function duyet(Request $request){
        if (Session::has('admin')) {
            $inputs = $request->all();
            $id = $inputs['idduyet'];
            $model = chamecon::find($id);
            $model->trangthai = 'Duyệt';
            $model->save();
            return redirect('dangkynhanchamecon');

        }else
            return view('errors.notlogin');
    }

    public function prints($id){
        if (Session::has('admin')) {
            $model = chamecon::find($id);
            $modelxa = Towns::where('maxa',$model->maxa)->first();
            $modelhuyen = Districts::where('mahuyen',$model->mahuyen)->first();
            $huyen = $modelhuyen->tenhuyen;
            if(session('admin')->level == 'H' && session('admin')->name == 'Phòng tư Pháp huyện Yên Minh')
            {
                $xa = "tpym";
            }
            elseif(session('admin')->level == 'H' && session('admin')->name == 'Phòng tư Pháp huyện Đồng Văn')
            {
                $xa = "tpdv";
            }
            else
            {
                $xa = $modelxa->tenxa;
            }
            $tenxa = substr("$xa",8);
            $tinh = GeneralConfigs::first()->tendv;
            return view('reports.cmc.print')
                ->with('model',$model)
                ->with('xa',$xa)
                ->with('tenxa',$tenxa)
                ->with('huyen',$huyen)
                ->with('tinh',$tinh)
                ->with('pageTitle','In trích lục nhận cha mẹ con(Bản chính)');

        }else
            return view('errors.notlogin');
    }

    public function printsbansao($id){
        if (Session::has('admin')) {
            $model = chamecon::find($id);
            $modelxa = Towns::where('maxa',$model->maxa)->first();
            if(session('admin')->level == 'H' && session('admin')->name == 'Phòng tư Pháp huyện Yên Minh')
            {
                $xa = "tpym";
            }
            elseif(session('admin')->level == 'H' && session('admin')->name == 'Phòng tư Pháp huyện Đồng Văn')
            {
                $xa = "tpdv";
            }
            else
            {
                $xa = $modelxa->tenxa;
            }
            $tenxa = substr("$xa",8);
            $modelhuyen = Districts::where('mahuyen',$model->mahuyen)->first();
            $huyen = $modelhuyen->tenhuyen;
            $tinh = GeneralConfigs::first()->tendv;
            return view('reports.cmc.printbansao')
                ->with('model',$model)
                ->with('xa',$xa)
                ->with('tenxa',$tenxa)
                ->with('huyen',$huyen)
                ->with('tinh',$tinh)
                ->with('pageTitle','In trích lục nhận cha mẹ con(Bản sao)');
        }else
            return view('errors.notlogin');
    }

    public function printstokhai($id){
        if (Session::has('admin')) {
            $model = chamecon::find($id);
            $modelxa = Towns::where('maxa',$model->maxa)->first();
            $modelhuyen = Towns::where('mahuyen',$model->mahuyen)->first();
            if(session('admin')->level == 'H' && session('admin')->name == 'Phòng tư Pháp huyện Yên Minh')
            {
                $xa = "tpym";
            }
            elseif(session('admin')->level == 'H' && session('admin')->name == 'Phòng tư Pháp huyện Đồng Văn')
            {
                $xa = "tpdv";
            }
            else
            {
                $xa = $modelxa->tenxa;
            }
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
            return view('reports.cmc.printtokhai')
                ->with('model',$model)
                ->with('xa',$xa)
                ->with('tencq',$tencq)
                ->with('pageTitle','In tờ khai nhận cha mẹ con');

        }else
            return view('errors.notlogin');
    }
}
