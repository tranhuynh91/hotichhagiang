<?php

namespace App\Http\Controllers;

use App\Districts;
use App\giamho;
use App\SoHoTich;
use App\Towns;
use App\ThongTinThayDoi;
use Illuminate\Http\Request;
use App\GeneralConfigs;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;

class giamhoController extends Controller
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

            $model = giamho::whereMonth('ngaydangky', $thang)
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

            return view('manage.giamho.index')
                -> with('huyens', $huyens)
                -> with('xas', $xas)
                -> with('mahuyen', $huyen)
                -> with('maxa', $xa)
                -> with('thang',$thang)
                -> with('nam',$nam)
                -> with('model', $model)
                -> with('pageTitle','Thông tin đăn ký giám hộ ('.$count.' hồ sơ)');
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

            return view('manage.giamho.create')
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

            $modelsohotich =  SoHoTich::where('plhotich','Giám hộ')
                ->where('namso',date('Y'))->where('mahuyen',$inputs['mahuyen'])->where('maxa',$inputs['maxa'])->first()->quyenhotich;
            $inputs['soquyen'] = (isset($modelsohotich)) ? $modelsohotich : getmatinh().$inputs['mahuyen'].$inputs['maxa'].'GH'.date('Y');

            $inputs['soso'] = $this->getSoHoTich($inputs['maxa'],$inputs['mahuyen'],$inputs['soquyen'] );
            $inputs['matinh'] = getmatinh();

            $inputs['ngaydangky'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaydangky'])));
            $inputs['ngaysinhngh1'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaysinhngh1'])));
            $inputs['ngaysinhngh2'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaycapgtnk'])));
            $inputs['ngaysinhndgh'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaysinhndgh'])));
            $inputs['ngaycapgtnk'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaycapgtnk'])));
            $inputs['matinh'] = getmatinh();
            $inputs['mahs'] = $inputs['matinh'].$inputs['mahuyen'].$inputs['maxa'].'GH'.getdate()[0];
            $inputs['trangthai'] = 'Chờ duyệt';
            $model = new giamho();
            $model->create($inputs);

            return redirect('dangkygiamho');

        }else
            return view('errors.notlogin');
    }

    public function getSoHoTich($maxa,$mahuyen,$quyen){
        $idmax = giamho::where('maxa',$maxa)
            ->where('mahuyen',$mahuyen)
            ->where('soquyen',$quyen)
            ->max('id');
        if($idmax==null)
            $stt = 1;
        else{
            $model =giamho::where('id', $idmax)->first();
            $stt = $model->soso + 1;
        }
        return $stt;
    }

    public function show($id){
        if (Session::has('admin')) {

            $model = giamho::find($id);
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

            return view('manage.giamho.show')
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

            $model = giamho::find($id);
            $huyens = Districts::all();
            $huyendf = $model->mahuyen;
            $xas = Towns::where('mahuyen', $huyendf)
                ->get();
            $xadf = $model->maxa;

            $dantocs = getDanTocSelectOptions();
            $quoctichs = getQuocTichSelectOptions();

            return view('manage.giamho.edit')
                ->with('action','edit')
                ->with('huyens', $huyens)
                ->with('mahuyen', $huyendf)
                ->with('xas', $xas)
                ->with('maxa', $xadf)
                ->with('dantocs', $dantocs)
                ->with('quoctichs', $quoctichs)
                ->with('model',$model)
                ->with('pageTitle', 'Chỉnh sửa thông tin đăng ký giám hộ');
        }else
            return view('errors.notlogin');
    }

    public function update(Request $request,$id){
        if (Session::has('admin')) {
            $inputs = $request->all();
            $inputs['ngaydangky'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaydangky'])));
            $inputs['ngaysinhngh1'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaysinhngh1'])));
            $inputs['ngaysinhngh2'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaycapgtnk'])));
            $inputs['ngaysinhndgh'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaysinhndgh'])));
            $inputs['ngaycapgtnk'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaycapgtnk'])));
            $model = giamho::find($id);
            $model->update($inputs);
            return redirect('dangkygiamho');

        }else
            return view('errors.notlogin');
    }

    public function destroy(Request $request){
        if (Session::has('admin')) {
            $inputs = $request->all();
            $id = $inputs['iddelete'];
            $model = giamho::find($id);
            $model->delete();
            return redirect('dangkygiamho');

        }else
            return view('errors.notlogin');
    }

    public function duyet(Request $request){
        if (Session::has('admin')) {
            $inputs = $request->all();
            $id = $inputs['idduyet'];
            $model = giamho::find($id);
            $model->trangthai = 'Duyệt';
            $model->save();
            return redirect('dangkygiamho');

        }else
            return view('errors.notlogin');
    }

    public function prints($id){
        if (Session::has('admin')) {
            $model = giamho::find($id);
            $modelxa = Towns::where('maxa',$model->maxa)->first();
            $modelhuyen = Towns::where('mahuyen',$model->mahuyen)->first();
            $tinh = GeneralConfigs::first()->tendv;
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
            $tenxa = substr("$xa",8);
            if($model->phanloainhap == "Giám hộ")
            {
                return view('reports.giamho.print')
                    ->with('model',$model)
                    ->with('xa',$xa)
                    ->with('tenxa',$tenxa)
                    ->with('huyen',$huyen)
                    ->with('tinh',$tinh)
                    ->with('pageTitle','In giấy trích lục đăng ký giám hộ (Bản chính)');
            }
            elseif($model->phanloainhap == "Chấm dứt giám hộ")
            {
                return view('reports.cdgiamho.print')
                    ->with('model',$model)
                    ->with('xa',$xa)
                    ->with('tenxa',$tenxa)
                    ->with('huyen',$huyen)
                    ->with('tinh',$tinh)
                    ->with('pageTitle','In giấy trích lục đăng ký giám hộ (Bản chính)');
            }

        }else
            return view('errors.notlogin');
    }

    public function printsbansao($id){
        if (Session::has('admin')) {
            $model = giamho::find($id);
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
            if($model->phanloainhap == "Giám hộ")
            {
                return view('reports.giamho.printbansao')
                    ->with('model',$model)
                    ->with('xa',$xa)
                    ->with('tenxa',$tenxa)
                    ->with('huyen',$huyen)
                    ->with('tinh',$tinh)
                    ->with('pageTitle','In trích lục giám hộ(Bản sao)');
            }
            elseif($model->phanloainhap == "Chấm dứt giám hộ")
            {
                return view('reports.cdgiamho.printbansao')
                    ->with('model',$model)
                    ->with('xa',$xa)
                    ->with('tenxa',$tenxa)
                    ->with('huyen',$huyen)
                    ->with('tinh',$tinh)
                    ->with('pageTitle','In trích lục chấm dứt giám hộ(Bản sao)');
            }

        }else
            return view('errors.notlogin');
    }

    public function printstokhai($id){
        if (Session::has('admin')) {
            $model = giamho::find($id);
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
            if($model->phanloainhap == "Giám hộ")
            {
                return view('reports.giamho.printtokhai')
                    ->with('model',$model)
                    ->with('xa',$xa)
                    ->with('tencq',$tencq)
                    ->with('pageTitle','In tờ khai đăng ký giám hộ');
            }
            elseif($model->phanloainhap == "Chấm dứt giám hộ")
            {
                return view('reports.cdgiamho.printtokhai')
                    ->with('model',$model)
                    ->with('xa',$xa)
                    ->with('tencq',$tencq)
                    ->with('pageTitle','In tờ khai đăng ký giám hộ');
            }

        }else
            return view('errors.notlogin');
    }

    public function chamdut (Request $request)
    {
        if (Session::has('admin')) {
            $inputs = $request->all();
            $id = $inputs['idchamdut'];
            $model = giamho::find($id);
            $modelsohotich =  SoHoTich::where('plhotich','Chấm dứt giám hộ')
                ->where('namso',date('Y'))->where('mahuyen',$inputs['mahuyen'])->where('maxa',$inputs['maxa'])->first()->quyenhotich;
            $inputs['quyencd'] = (isset($modelsohotich)) ? $modelsohotich : getmatinh().$inputs['mahuyen'].$inputs['maxa'].'CDGH'.date('Y');
            $inputs['socd'] = $this->getSoHoTichCD($inputs['maxa'],$inputs['mahuyen'],$inputs['quyencd'] );
            $inputs['ngaychamdut'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaychamdut'])));
            $inputs['phanloainhap'] = 'Chấm dứt giám hộ';
            $model->update($inputs);
            return redirect('dangkygiamho');
        }
        else
            return view('errors.notlogin');
    }

    public function getSoHoTichCD($maxa,$mahuyen,$quyen){
        $idmax = giamho::where('maxa',$maxa)
            ->where('mahuyen',$mahuyen)
            ->where('quyencd',$quyen)
            ->max('id');
        if($idmax==null)
            $stt = 1;
        else{
            $model =giamho::where('id', $idmax)->first();
            $stt = $model->socd + 1;
        }
        return $stt;
    }
}
