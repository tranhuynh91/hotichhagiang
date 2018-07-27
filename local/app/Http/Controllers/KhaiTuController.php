<?php

namespace App\Http\Controllers;

use App\DanToc;
use App\Districts;
use App\KhaiTu;
use App\QuocTich;
use App\SoHoTich;
use App\Towns;
use App\ThongTinThayDoi;
use App\GeneralConfigs;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;

class KhaiTuController extends Controller
{
    protected function listHuyen() {
        return Districts::all();
    }

    protected function listXa($huyen){
        return Towns::where('mahuyen',$huyen)->get();
    }

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

            $huyens = $this->listHuyen();
            $xas = array();
            if($huyen != 'all'){
                $xas = $this->listXa($huyen);
            }

            $model = KhaiTu::whereMonth('ngaydangkykt', $thang)
                ->whereYear('ngaydangkykt', $nam);
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

            return view('manage.khaitu.index')
                -> with('huyens', $huyens)
                -> with('xas', $xas)
                -> with('mahuyen', $huyen)
                -> with('maxa', $xa)
                -> with('model', $model)
                -> with('thang', $thang)
                -> with('nam', $nam)
                -> with('pageTitle','Thông tin khai tử ('.$count.' hồ sơ)');
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

            return view('manage.khaitu.create')
                ->with('huyens', $huyens)
                ->with('mahuyen', $huyendf)
                ->with('xas', $xas)
                ->with('maxa', $xadf)
                ->with('dantocs', $dantocs)
                ->with('quoctichs', $quoctichs)
                ->with('pageTitle', 'Thêm mới thông tin khai tử');
        }else
            return view('errors.notlogin');
    }

    public function store(Request $request)
    {
        if (Session::has('admin')) {
            $inputs = $request->all();
            $khaitu = new KhaiTu();
            $modelsohotich =  SoHoTich::where('plhotich','Khai tử')
                ->where('namso',date('Y'))->where('mahuyen',$inputs['mahuyen'])->where('maxa',$inputs['maxa'])->first()->quyenhotich;
            $inputs['quyen'] = (isset($modelsohotich)) ? $modelsohotich : getmatinh().$inputs['mahuyen'].$inputs['maxa'].'KS'.date('Y');
            $inputs['so'] = $this->getSoHoTich($inputs['maxa'],$inputs['mahuyen'],$inputs['quyen'] );
            $inputs['trangthai'] = 'Chờ duyệt';
            $inputs['matinh'] = getmatinh();
            $inputs['masohoso'] = $inputs['matinh'].$inputs['mahuyen'].$inputs['maxa'].'KT'.getdate()[0];
            $inputs['ngaydangkykt'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaydangkykt'])));
            $inputs['ngaysinh'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaysinh'])));
            $inputs['ngaychet'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaychet'])));
            if($inputs['ngaycapgbt'] == '')
            {
                $inputs['ngaycapgbt'] = NULL;
            }
            else
            {
                $inputs['ngaycapgbt'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaycapgbt'])));
            }

            if( $inputs['ngaycapnk'] == '')
            {
                $inputs['ngaycapnk'] = NULL;
            }
            else
            {
                $inputs['ngaycapnk'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaycapnk'])));
            }

            $khaitu->create($inputs);
            return redirect('khaitu');
        }
        else
            return view('errors.notlogin');
    }

    public function getSoHoTich($maxa,$mahuyen,$quyen){
        $idmax = KhaiTu::where('maxa',$maxa)
            ->where('mahuyen',$mahuyen)
            ->where('quyen',$quyen)
            ->max('id');
        if($idmax==null)
            $stt = 1;
        else{
            $model =KhaiTu::where('id', $idmax)->first();
            $stt = $model->so + 1;
        }
        return $stt;
    }

    public function show($id){
        if (Session::has('admin')) {
            $khaitu = KhaiTu::find($id);
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
                $xa = Towns::where('maxa',$khaitu->maxa)->first()->tenxa;
            }
            $huyen = Districts::where('mahuyen',$khaitu->mahuyen)->first()->tenhuyen;
            $thongtinthaydoi = ThongTinThayDoi::where('mahs',$khaitu->masohoso)->get();
            return view('manage.khaitu.show')
                ->with('khaitu',$khaitu)
                ->with('xa',$xa)
                ->with('huyen',$huyen)
                ->with('thongtinthaydoi',$thongtinthaydoi)
                ->with('pageTitle', 'Thông tin khai tử');
        }else
            return view('errors.notlogin');
    }

    public function edit ($id)
    {
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
            $khaitu = KhaiTu::find($id);
            $dantocs = getDanTocSelectOptions();
            $quoctichs = getQuocTichSelectOptions();

            return view('manage.khaitu.edit')
                ->with('huyens', $huyens)
                ->with('mahuyen', $huyendf)
                ->with('xas', $xas)
                ->with('maxa', $xadf)
                ->with('dantocs', $dantocs)
                ->with('quoctichs', $quoctichs)
                ->with('khaitu',$khaitu)
                ->with('pageTitle', 'Sửa thông tin khai tử');
        }else
            return view('errors.notlogin');
    }

    public function update(Request $request, $id)
    {
        if (Session::has('admin')) {
            $inputs = $request->all();
            $inputs['ngaydangkykt'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaydangkykt'])));
            $inputs['ngaysinh'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaysinh'])));
            $inputs['ngaychet'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaychet'])));
            if($inputs['ngaycapgbt'] == '')
            {
                $inputs['ngaycapgbt'] = NULL;
            }
            else
            {
                $inputs['ngaycapgbt'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaycapgbt'])));
            }

            if( $inputs['ngaycapnk'] == '')
            {
                $inputs['ngaycapnk'] = NULL;
            }
            else
            {
                $inputs['ngaycapnk'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaycapnk'])));
            }
            $khaitu = KhaiTu::find($id);
            $khaitu->update($inputs);
            return redirect('khaitu');

        }else
            return view('errors.notlogin');
    }

    public function destroy(Request $request)
    {
        if (Session::has('admin')) {
            $khaitu = KhaiTu::where('id', $request->iddelete);
            $khaitu->delete();
            return redirect('khaitu');
        }
        else
            return view('errors.notlogin');
    }

    public function duyet(Request $request){
        if (Session::has('admin')) {
            $inputs = $request->all();
            $id = $inputs['idduyet'];
            $khaitu = KhaiTu::find($id);
            $khaitu->trangthai = 'Duyệt';
            $khaitu->save();
            return redirect('khaitu');

        }else
            return view('errors.notlogin');
    }

    public function prints($id){
        if (Session::has('admin')) {
            $model = KhaiTu::find($id);
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
            return view('reports.khaitu.print')
                ->with('model',$model)
                ->with('xa',$xa)
                ->with('tenxa',$tenxa)
                ->with('huyen',$huyen)
                ->with('tinh',$tinh)
                ->with('pageTitle','In giấy khai tử bản chính');
        }else
            return view('errors.notlogin');
    }

    public function printsbansao($id){
        if (Session::has('admin')) {
            $model = KhaiTu::find($id);
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
            return view('reports.khaitu.printbansao')
                ->with('model',$model)
                ->with('xa',$xa)
                ->with('tenxa',$tenxa)
                ->with('huyen',$huyen)
                ->with('tinh',$tinh)
                ->with('pageTitle','In giấy khai sinh bản sao');
        }else
            return view('errors.notlogin');
    }

    public function printstokhai($id){
        if (Session::has('admin')) {
            $model = KhaiTu::find($id);
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
            if($model->phanloaidk == "Ghi sổ việc khai tử tại nước ngoài")
            {
                return view('reports.khaitu.printtokhaigs')
                    ->with('model',$model)
                    ->with('xa',$xa)
                    ->with('tencq',$tencq)
                    ->with('pageTitle','In giấy đăng ký khai tử');
            }
            else
            {
                return view('reports.khaitu.printtokhai')
                    ->with('model',$model)
                    ->with('xa',$xa)
                    ->with('tencq',$tencq)
                    ->with('pageTitle','In giấy đăng ký khai tử');
            }

        }else
            return view('errors.notlogin');
    }
}
