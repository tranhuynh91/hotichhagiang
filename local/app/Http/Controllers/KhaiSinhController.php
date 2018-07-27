<?php

namespace App\Http\Controllers;

use App\CongDan;
use App\DanToc;
use App\Districts;
use App\GeneralConfigs;
use App\KhaiSinh;
use App\QuocTich;
use App\SoHoTich;
use App\ThongTinThayDoi;
use App\toado;
use App\Towns;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;

class KhaiSinhController extends Controller
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

            $model = KhaiSinh::whereMonth('ngaydangky', $thang)
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

            return view('manage.khaisinh.index')
                -> with('huyens', $huyens)
                -> with('xas', $xas)
                -> with('mahuyen', $huyen)
                -> with('maxa', $xa)
                -> with('thang',$thang)
                -> with('nam',$nam)
                -> with('model', $model)
                -> with('pageTitle','Thông tin khai sinh ('.$count.' hồ sơ)');
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

            return view('manage.khaisinh.create')
                ->with('action','create')
                ->with('huyens', $huyens)
                ->with('mahuyen', $huyendf)
                ->with('xas', $xas)
                ->with('maxa', $xadf)
                ->with('dantocs', $dantocs)
                ->with('quoctichs', $quoctichs)
                ->with('pageTitle', 'Thêm mới thông tin khai sinh');
        }else
            return view('errors.notlogin');
    }

    public function store(Request $request){
        if (Session::has('admin')) {

            $inputs = $request->all();
            $modelsohotich =  SoHoTich::where('plhotich','Khai sinh')
                ->where('namso',date('Y'))->where('mahuyen',$inputs['mahuyen'])->where('maxa',$inputs['maxa'])->first()->quyenhotich;
            $inputs['quyen'] = (isset($modelsohotich)) ? $modelsohotich : getmatinh().$inputs['mahuyen'].$inputs['maxa'].'KS'.date('Y');
            $inputs['so'] = $this->getSoHoTich($inputs['maxa'],$inputs['mahuyen'],$inputs['quyen'] );
            $inputs['ngaydangky'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaydangky'])));
            $inputs['ngaysinhks'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaysinhks'])));
            $inputs['ngaysinhksbangchu'] = getDateText($inputs['ngaysinhks']);
            $inputs['matinh'] = getmatinh();
            $inputs['mahs'] = $inputs['matinh'].$inputs['mahuyen'].$inputs['maxa'].'KS'.getdate()[0];
            $inputs['ngaycapgtnk'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaycapgtnk'])));
            $inputs['trangthai'] = 'Chờ duyệt';
            $model = new KhaiSinh();
            $model->create($inputs);

            return redirect('khaisinh');

        }else
            return view('errors.notlogin');
    }

    public function getSoHoTich($maxa,$mahuyen,$quyen){
        $idmax = KhaiSinh::where('maxa',$maxa)
            ->where('mahuyen',$mahuyen)
            ->where('quyen',$quyen)
            ->max('id');
        if($idmax==null)
            $stt = 1;
        else{
            $model =KhaiSinh::where('id', $idmax)->first();
            $stt = $model->so + 1;
        }
        return $stt;
    }

    public function show($id){
        if (Session::has('admin')) {
            $model = KhaiSinh::find($id);
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

            return view('manage.khaisinh.show')
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

            $model = KhaiSinh::find($id);
            $huyens = Districts::all();
            $huyendf = $model->mahuyen;
            $xas = Towns::where('mahuyen', $huyendf)
                ->get();
            $xadf = $model->maxa;

            $dantocs = getDanTocSelectOptions();
            $quoctichs = getQuocTichSelectOptions();

            return view('manage.khaisinh.edit')
                ->with('action','edit')
                ->with('huyens', $huyens)
                ->with('mahuyen', $huyendf)
                ->with('xas', $xas)
                ->with('maxa', $xadf)
                ->with('dantocs', $dantocs)
                ->with('quoctichs', $quoctichs)
                ->with('model',$model)
                ->with('pageTitle', 'Chỉnh sửa thông tin khai sinh');
        }else
            return view('errors.notlogin');
    }

    public function update(Request $request,$id){
        if (Session::has('admin')) {
            $inputs = $request->all();
            $inputs['ngaydangky'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaydangky'])));
            $inputs['ngaysinhks'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaysinhks'])));
            $inputs['ngaysinhksbangchu'] = getDateText($inputs['ngaysinhks']);
            $inputs['ngaycapgtnk'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaycapgtnk'])));
            $model = KhaiSinh::find($id);
            $model->update($inputs);
            return redirect('khaisinh');

        }else
            return view('errors.notlogin');
    }

    public function destroy(Request $request){
        if (Session::has('admin')) {
            $inputs = $request->all();
            $id = $inputs['iddelete'];
            $model = KhaiSinh::find($id);
            $model->delete();
            return redirect('khaisinh');

        }else
            return view('errors.notlogin');
    }

    public function duyet(Request $request){
        if (Session::has('admin')) {
            $inputs = $request->all();
            $id = $inputs['idduyet'];
            $model = KhaiSinh::find($id);
            $model->trangthai = 'Duyệt';
            $model->save();
            return redirect('khaisinh');

        }else
            return view('errors.notlogin');
    }

    public function prints($id){
        if (Session::has('admin')) {
            $model = KhaiSinh::find($id);
            $modelxa = Towns::where('maxa',$model->maxa)->first();
            $toado = toado::where('maxa',$model->maxa)->where('phanloai','Giấy Khai Sinh')->first();
            $huyen=$modelxa->tenhuyen;

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
            //if($model->pldangky == "Ghi sổ việc sinh con ở nước ngoài")
            // {
            //    return view('reports.khaisinh.printtrichlucsobc')
            //        ->with('model',$model)
            //         ->with('xa',$xa)
            //        ->with('pageTitle','In trích lục');
            // }
            // else
            //  {
                return view('reports.khaisinh.print')
                    ->with('model',$model)
                    ->with('xa',$xa)
                    ->with('id',$id)
                    ->with('huyen',$huyen)
                    ->with('toado',$toado)
                    ->with('pageTitle','In giấy khai sinh bản chính');
            // }
        }else
            return view('errors.notlogin');
    }
    public function printss(Request $request ,$id){
        if (Session::has('admin')) {
            $inputs = $request->all();
            $model = KhaiSinh::find($id);
            $modelxa = Towns::where('maxa',$model->maxa)->first();
            $huyen=$modelxa->tenhuyen;
            $kiemtratoado = toado::where('maxa',$model->maxa)->where('phanloai','Giấy Khai Sinh')->first();
            if($kiemtratoado != '')
            {
                $kiemtratoado->delete();
            }
            if($inputs != null)
            {
                $toado1 = $inputs['xa1']; $toado2 = $inputs['xa2'];$toado3 = $inputs['xa3']; $toado4 = $inputs['xa4'];$toado5 = $inputs['xa5']; $toado6 = $inputs['xa6'];$toado7 = $inputs['xa7']; $toado8 = $inputs['xa8'];$toado9 = $inputs['xa9']; $toado10 = $inputs['xa10'];
                $toado11 = $inputs['xa11']; $toado12 = $inputs['xa12'];$toado13 = $inputs['xa13']; $toado14 = $inputs['xa14'];$toado15 = $inputs['xa15']; $toado16 = $inputs['xa16'];$toado17 = $inputs['xa17']; $toado18 = $inputs['xa18'];$toado19 = $inputs['xa19']; $toado20 = $inputs['xa20'];
                $toado21 = $inputs['xa21']; $toado22 = $inputs['xa22'];$toado23 = $inputs['xa23']; $toado24 = $inputs['xa24'];$toado25 = $inputs['xa25']; $toado26 = $inputs['xa26'];$toado27 = $inputs['xa27']; $toado28 = $inputs['xa28'];$toado29 = $inputs['xa29']; $toado30 = $inputs['xa30'];
                $toado31 = $inputs['xa31']; $toado32 = $inputs['xa32'];$toado33 = $inputs['xa33']; $toado34 = $inputs['xa34'];$toado35 = $inputs['xa35']; $toado36 = $inputs['xa36'];$toado37 = $inputs['xa37']; $toado38 = $inputs['xa38'];$toado39 = $inputs['xa39']; $toado40 = $inputs['xa40'];
                $toado41 = $inputs['xa41']; $toado42 = $inputs['xa42'];$toado43 = $inputs['xa43']; $toado44 = $inputs['xa44'];$toado45 = $inputs['xa45']; $toado46 = $inputs['xa46'];$toado47 = $inputs['xa47']; $toado48 = $inputs['xa48'];$toado49 = $inputs['xa49']; $toado50 = $inputs['xa50'];
                $toado51 = $inputs['xa51']; $toado52 = $inputs['xa52'];


                $toado = new toado();
                $toado->toado1= $toado1;$toado->toado2= $toado2; $toado->toado3= $toado3;$toado->toado4= $toado4;$toado->toado5= $toado5;$toado->toado6= $toado6;$toado->toado7= $toado7;$toado->toado8= $toado8;$toado->toado9= $toado9;$toado->toado10= $toado10;$toado->toado11= $toado11;$toado->toado12= $toado12;
                $toado->toado13= $toado13;$toado->toado14= $toado14;$toado->toado15= $toado15;$toado->toado16= $toado16;$toado->toado17= $toado17;$toado->toado18= $toado18;$toado->toado19= $toado19;$toado->toado20= $toado20;$toado->toado21= $toado21;$toado->toado22= $toado22;$toado->toado23= $toado23;$toado->toado24= $toado24;
                $toado->toado25= $toado25;$toado->toado26= $toado26;$toado->toado27= $toado27;$toado->toado28= $toado28;$toado->toado29= $toado29;$toado->toado30= $toado30;$toado->toado31= $toado31;$toado->toado32= $toado32;$toado->toado33= $toado33;$toado->toado34= $toado34;$toado->toado35= $toado35;$toado->toado36= $toado36;
                $toado->toado37= $toado37;$toado->toado38= $toado38;$toado->toado39= $toado39;$toado->toado40= $toado40;$toado->toado41= $toado41;$toado->toado42= $toado42;$toado->toado43= $toado43;$toado->toado44= $toado44;$toado->toado45= $toado45;$toado->toado46= $toado46;$toado->toado47= $toado47;
                $toado->toado48= $toado48;$toado->toado49= $toado49;$toado->toado50= $toado50;$toado->toado51= $toado51;$toado->toado52= $toado52;

                $toado->mahuyen = $modelxa->mahuyen;
                $toado->maxa = $modelxa->maxa;
                $toado->phanloai = "Giấy Khai Sinh";
                $toado->save();
            }
            else
            {
                $toado1 = $toado2 = $toado3 =$toado4 = $toado5 = $toado6= $toado7= $toado8= $toado9= $toado10= $toado11= $toado12= $toado13= $toado14= $toado15= $toado16= $toado17= $toado18= $toado19=
                $toado20 =$toado21 = $toado22= $toado23= $toado24= $toado25= $toado26= $toado27= $toado28= $toado29= $toado30= $toado31= $toado32= $toado33= $toado34= $toado35= $toado36=
                $toado37 =$toado38 = $toado39= $toado40= $toado41= $toado42= $toado43= $toado44= $toado45= $toado46= $toado47= $toado48= $toado49= $toado50= $toado51= $toado52='';
            }

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
            if($model->pldangky == "Ghi sổ việc sinh con ở nước ngoài")
            {
                return view('reports.khaisinh.printtrichlucsobc')
                    ->with('model',$model)
                    ->with('xa',$xa)
                    ->with('pageTitle','In trích lục');
            }
            else
            {
                return view('reports.khaisinh.print')
                    ->with('model',$model)
                    ->with('xa',$xa)
                    ->with('huyen',$huyen)
                    ->with('id',$id)
                    ->with('toado1',$toado1)
                    ->with('toado2',$toado2)
                    ->with('toado',$toado)
                    ->with('pageTitle','In giấy khai sinh bản chính');
            }
        }else
            return view('errors.notlogin');
    }

    public function printsbansao($id){
        if (Session::has('admin')) {
            $model = khaisinh::find($id);
            $modelxa = Towns::where('maxa',$model->maxa)->first();
            $modelhuyen = Districts::where('mahuyen',$model->mahuyen)->first();
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

            $huyen = $modelhuyen->tenhuyen;
            $tinh = GeneralConfigs::first()->tendv;
            //if($model->pldangky == "Ghi sổ việc sinh con ở nước ngoài")
           // {
            //    return view('reports.khaisinh.printtrichlucsobs')
           //         ->with('model',$model)
           //         ->with('xa',$xa)
             //       ->with('tenxa',$tenxa)
             //       ->with('huyen',$huyen)
             //       ->with('tinh',$tinh)
             //       ->with('pageTitle','In trích lục');
            //}
            //else
           // {
                return view('reports.khaisinh.printbansao')
                    ->with('model',$model)
                    ->with('xa',$xa)
                    ->with('tenxa',$tenxa)
                    ->with('huyen',$huyen)
                    ->with('tinh',$tinh)
                    ->with('pageTitle','In giấy khai sinh bản sao');
            //}

        }else
            return view('errors.notlogin');
    }

    public function printstokhai($id){
        if (Session::has('admin')) {
            $model = KhaiSinh::find($id);
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
                $tencq = 'Thị trấn Yên Minh, '.$huyen.', Tỉnh '.$tinh;
            }
            else
            {
                $tencq = $xa.', '.$huyen .', Tỉnh '.$tinh;
            }
            if($model->pldangky == "Ghi sổ việc sinh con ở nước ngoài")
            {
                return view('reports.khaisinh.printtokhaigs')
                    ->with('model',$model)
                    ->with('xa',$xa)
                    ->with('tencq',$tencq)
                    ->with('pageTitle','In tờ khai ghi sổ việc khai sinh');
            }
            else
            {
                return view('reports.khaisinh.printtokhai')
                    ->with('model',$model)
                    ->with('xa',$xa)
                    ->with('tencq',$tencq)
                    ->with('pageTitle','In tờ khai đăng ký khai sinh');
            }
        }else
            return view('errors.notlogin');
    }
}
