<?php

namespace App\Http\Controllers;

use App\DanToc;
use App\toado;
use App\Districts;
use App\ConNuoi;
use App\QuocTich;
use App\SoHoTich;
use App\Towns;
use App\ThongTinThayDoi;
use Illuminate\Http\Request;
use App\GeneralConfigs;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;

class ConNuoiController extends Controller
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

            $model = ConNuoi::whereMonth('ngaythangdk', $thang)
                ->whereYear('ngaythangdk', $nam);
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

            return view('manage.connuoi.index')
                -> with('huyens', $huyens)
                -> with('xas', $xas)
                -> with('mahuyen', $huyen)
                -> with('maxa', $xa)
                -> with('thang', $thang)
                -> with('nam', $nam)
                -> with('model', $model)
                -> with('pageTitle','Thông tin đăng ký con nuôi ('.$count.' hồ sơ)');
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

            return view('manage.connuoi.create')
                ->with('huyens', $huyens)
                ->with('mahuyen', $huyendf)
                ->with('xas', $xas)
                ->with('maxa', $xadf)
                ->with('dantocs', $dantocs)
                ->with('quoctichs', $quoctichs)
                ->with('pageTitle', 'Thêm mới thông tin con nuôi');
        }else
            return view('errors.notlogin');
    }
    public function store(Request $request)
    {
        if (Session::has('admin')) {
            $inputs = $request->all();
            $connuoi = new ConNuoi();
            $modelsohotich =  SoHoTich::where('plhotich','Con nuôi')
                ->where('namso',date('Y'))->where('mahuyen',$inputs['mahuyen'])->where('maxa',$inputs['maxa'])->first()->quyenhotich;
            $inputs['quyen'] = (isset($modelsohotich)) ? $modelsohotich : getmatinh().$inputs['mahuyen'].$inputs['maxa'].'KS'.date('Y');
            $inputs['so'] = $this->getSoHoTich($inputs['maxa'],$inputs['mahuyen'],$inputs['quyen'] );
            $inputs['ngaysinhchanuoi'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaysinhchanuoi'])));
            $inputs['ngaycapgtcn'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaycapgtcn'])));
            $inputs['ngaysinhmenuoi'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaysinhmenuoi'])));
            $inputs['ngaycapgtmn'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaycapgtmn'])));
            $inputs['ngaysinhconnuoi'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaysinhconnuoi'])));
            $inputs['ngaysinhchagiao'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaysinhchagiao'])));
            $inputs['ngaycapgtcg'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaycapgtcg'])));
            $inputs['ngaysinhmegiao'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaysinhmegiao'])));
            $inputs['ngaycapgtmg'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaycapgtmg'])));
            $inputs['ngaythangdk'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaythangdk'])));
            $inputs['ngaythangqd'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaythangqd'])));
            $inputs['matinh'] = getmatinh();
            $inputs['masohoso'] = $inputs['matinh'].$inputs['mahuyen'].$inputs['maxa'].'CN'.getdate()[0];
            $inputs['trangthai']= "Chờ duyệt";
            $connuoi->create($inputs);
            return redirect('dangkyconnuoi');

        }else
            return view('errors.notlogin');
    }

    public function getSoHoTich($maxa,$mahuyen,$quyen){
        $idmax = ConNuoi::where('maxa',$maxa)
            ->where('mahuyen',$mahuyen)
            ->where('quyen',$quyen)
            ->max('id');
        if($idmax==null)
            $stt = 1;
        else{
            $model =ConNuoi::where('id', $idmax)->first();
            $stt = $model->so + 1;
        }
        return $stt;
    }

    public function show($id){
        if (Session::has('admin')) {

            $connuoi = ConNuoi::find($id);
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
                $xa = Towns::where('maxa',$connuoi->maxa)->first()->tenxa;
            }
            $huyen = Districts::where('mahuyen',$connuoi->mahuyen)->first()->tenhuyen;
            $thongtinthaydoi = ThongTinThayDoi::where('mahs',$connuoi->masohoso)->get();
            return view('manage.connuoi.show')
                ->with('connuoi',$connuoi)
                ->with('xa',$xa)
                ->with('huyen',$huyen)
                ->with('thongtinthaydoi',$thongtinthaydoi)
                ->with('pageTitle', 'Thông tin đăng ký con nuôi');
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
            $connuoi = ConNuoi::find($id);
            $dantocs = getDanTocSelectOptions();
            $quoctichs = getQuocTichSelectOptions();

            return view('manage.connuoi.edit')
                ->with('huyens', $huyens)
                ->with('mahuyen', $huyendf)
                ->with('xas', $xas)
                ->with('maxa', $xadf)
                ->with('dantocs', $dantocs)
                ->with('quoctichs', $quoctichs)
                ->with('connuoi',$connuoi)
                ->with('pageTitle', 'Sửa thông tin con nuôi');
        }else
            return view('errors.notlogin');
    }
    public function update(Request $request, $id)
    {
        if (Session::has('admin')) {
            $inputs = $request->all();
            $connuoi = ConNuoi::find($id);
            $inputs['ngaysinhchanuoi'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaysinhchanuoi'])));
            $inputs['ngaycapgtcn'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaycapgtcn'])));
            $inputs['ngaysinhmenuoi'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaysinhmenuoi'])));
            $inputs['ngaycapgtmn'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaycapgtmn'])));
            $inputs['ngaysinhconnuoi'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaysinhconnuoi'])));
            $inputs['ngaysinhchagiao'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaysinhchagiao'])));
            $inputs['ngaycapgtcg'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaycapgtcg'])));
            $inputs['ngaysinhmegiao'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaysinhmegiao'])));
            $inputs['ngaycapgtmg'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaycapgtmg'])));
            $inputs['ngaythangdk'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaythangdk'])));
            $inputs['ngaythangqd'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaythangqd'])));
            $connuoi->update($inputs);
            return redirect('dangkyconnuoi/'.$id.'/edit');

        }else
            return view('errors.notlogin');
    }
    public function destroy(Request $request)
    {
        if (Session::has('admin')) {
            $inputs = $request->all();
            $id = $inputs['iddelete'];
            $connuoi = ConNuoi::find($id);
            $connuoi->delete();
            return redirect('dangkyconnuoi');

        }else
            return view('errors.notlogin');
    }

    public function duyet(Request $request){
        if (Session::has('admin')) {
            $inputs = $request->all();
            $id = $inputs['idduyet'];
            $connuoi = ConNuoi::find($id);
            $connuoi->trangthai = 'Duyệt';
            $connuoi->save();
            return redirect('dangkyconnuoi');

        }else
            return view('errors.notlogin');
    }

    public function prints($id){
        if (Session::has('admin')) {
            $model = ConNuoi::find($id);
            $modelxa = Towns::where('maxa',$model->maxa)->first();
            $toado = toado::where('maxa',$model->maxa)->where('phanloai','Giấy Chứng Nhận Nuôi Con Nuôi')->first();
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
            return view('reports.connuoi.print')
                ->with('model',$model)
                ->with('xa',$xa)
                ->with('huyen',$huyen)
                ->with('toado',$toado)
                ->with('id',$id)
                ->with('pageTitle','In giấy chứng nhận con nuôi bản chính');
        }else
            return view('errors.notlogin');
    }

    public function printss(Request $request ,$id){
        if (Session::has('admin')) {
            $inputs = $request->all();
            $model = ConNuoi::find($id);
            $modelxa = Towns::where('maxa',$model->maxa)->first();
            $kiemtratoado = toado::where('maxa',$model->maxa)->where('phanloai','Giấy Chứng Nhận Nuôi Con Nuôi')->first();
            $huyen=$modelxa->tenhuyen;
            if($kiemtratoado != '')
            {
                $kiemtratoado->delete();
            }
            if($inputs != null)
            {
                $toado1 = $inputs['xa1']; $toado2 = $inputs['xa2'];$toado3 = $inputs['xa3']; $toado4 = $inputs['xa4'];$toado5 = $inputs['xa5']; $toado6 = $inputs['xa6'];
                $toado7 = $inputs['xa7']; $toado8 = $inputs['xa8'];$toado9 = $inputs['xa9']; $toado10 = $inputs['xa10'];
                $toado11 = $inputs['xa11']; $toado12 = $inputs['xa12'];$toado13 = $inputs['xa13']; $toado14 = $inputs['xa14'];$toado15 = $inputs['xa15'];
                $toado16 = $inputs['xa16'];$toado17 = $inputs['xa17']; $toado18 = $inputs['xa18'];$toado19 = $inputs['xa19']; $toado20 = $inputs['xa20'];
                $toado21 = $inputs['xa21']; $toado22 = $inputs['xa22'];$toado23 = $inputs['xa23']; $toado24 = $inputs['xa24'];$toado25 = $inputs['xa25'];
                $toado26 = $inputs['xa26'];$toado27 = $inputs['xa27']; $toado28 = $inputs['xa28'];$toado29 = $inputs['xa29']; $toado30 = $inputs['xa30'];
                $toado31 = $inputs['xa31']; $toado32 = $inputs['xa32'];$toado33 = $inputs['xa33']; $toado34 = $inputs['xa34'];$toado35 = $inputs['xa35'];
                $toado36 = $inputs['xa36'];$toado37 = $inputs['xa37']; $toado38 = $inputs['xa38'];
                $toado39 = $inputs['xa39'];$toado40 = $inputs['xa40'];$toado41 = $inputs['xa41'];$toado42 = $inputs['xa42'];$toado43 = $inputs['xa43'];
                $toado44 = $inputs['xa44'];$toado45 = $inputs['xa45'];
                $toado46 = $inputs['xa46'];$toado47 = $inputs['xa47'];$toado48 = $inputs['xa48'];$toado49 = $inputs['xa49'];$toado50 = $inputs['xa50'];
                $toado51 = $inputs['xa51'];$toado52 = $inputs['xa52'];
                $toado53 = $inputs['xa53'];$toado54= $inputs['xa54'];$toado55 = $inputs['xa55'];$toado56 = $inputs['xa56'];$toado57 = $inputs['xa57'];
                $toado58 = $inputs['xa58'];$toado59 = $inputs['xa59'];
                $toado60 = $inputs['xa60'];$toado61 = $inputs['xa61'];$toado62 = $inputs['xa62'];
                $toado63 = $inputs['xa63'];$toado64 = $inputs['xa64'];$toado65 = $inputs['xa65'];$toado66 = $inputs['xa66'];


                $toado = new toado();
                $toado->toado1= $toado1;$toado->toado2= $toado2; $toado->toado3= $toado3;$toado->toado4= $toado4;$toado->toado5= $toado5;$toado->toado6= $toado6;
                $toado->toado7= $toado7;$toado->toado8= $toado8;$toado->toado9= $toado9;$toado->toado10= $toado10;$toado->toado11= $toado11;$toado->toado12= $toado12;
                $toado->toado13= $toado13;$toado->toado14= $toado14;$toado->toado15= $toado15;$toado->toado16= $toado16;$toado->toado17= $toado17;$toado->toado18= $toado18;
                $toado->toado19= $toado19;$toado->toado20= $toado20;$toado->toado21= $toado21;$toado->toado22= $toado22;$toado->toado23= $toado23;$toado->toado24= $toado24;
                $toado->toado25= $toado25;$toado->toado26= $toado26;$toado->toado27= $toado27;$toado->toado28= $toado28;$toado->toado29= $toado29;$toado->toado30= $toado30;
                $toado->toado31= $toado31;$toado->toado32= $toado32;$toado->toado33= $toado33;$toado->toado34= $toado34;$toado->toado35= $toado35;$toado->toado36= $toado36;
                $toado->toado37= $toado37;$toado->toado38= $toado38;$toado->toado39= $toado39;$toado->toado40= $toado40;$toado->toado41= $toado41;$toado->toado42= $toado42;
                $toado->toado43= $toado43;$toado->toado44= $toado44;$toado->toado45= $toado45;$toado->toado46= $toado46;$toado->toado47= $toado47;$toado->toado48= $toado48;
                $toado->toado49= $toado49;$toado->toado50= $toado50;$toado->toado51= $toado51;$toado->toado52= $toado52;$toado->toado53= $toado53;$toado->toado54= $toado54;
                $toado->toado55= $toado55;$toado->toado56= $toado56;$toado->toado57= $toado57;$toado->toado58= $toado58;$toado->toado59= $toado59;$toado->toado60= $toado60;
                $toado->toado61= $toado61;$toado->toado62= $toado62;$toado->toado63= $toado63;$toado->toado64= $toado64;
                $toado->toado65= $toado65;$toado->toado66= $toado66;


                $toado->mahuyen = $modelxa->mahuyen;
                $toado->maxa = $modelxa->maxa;
                $toado->phanloai = "Giấy Chứng Nhận Nuôi Con Nuôi";
                $toado->save();
            }
            else
            {
                $toado1 = $toado2 = $toado3 =$toado4 = $toado5 = $toado6= $toado7= $toado8= $toado9= $toado10= $toado11= $toado12= $toado13= $toado14= $toado15= $toado16= $toado17= $toado18= $toado19=
                $toado20 =$toado21 = $toado22= $toado23= $toado24= $toado25= $toado26= $toado27= $toado28= $toado29= $toado30= $toado31= $toado32= $toado33= $toado34= $toado35= $toado36=$toado37= $toado38= $toado39=
                $toado40 = $toado41 = $toado42 = $toado43 = $toado44 = $toado45 =$toado46 = $toado47 = $toado48 = $toado49 = $toado50 = $toado51 = $toado52 = $toado53 = $toado54 = $toado55 =
                $toado56 = $toado57 = $toado58 = $toado59 = $toado60 = $toado61 = $toado62 =
                $toado63 =$toado64 =$toado65 =$toado66 ='';
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
            return view('reports.connuoi.print')
                ->with('model',$model)
                ->with('xa',$xa)
                ->with('huyen',$huyen)
                ->with('id',$id)
                ->with('toado1',$toado1)
                ->with('toado2',$toado2)
                ->with('toado',$toado)
                ->with('pageTitle','In giấy kết hôn bản chính');
        }else
            return view('errors.notlogin');
    }

    public function printsbansao($id){
        if (Session::has('admin')) {
            $model = ConNuoi::find($id);
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
            return view('reports.connuoi.printbansao')
                ->with('model',$model)
                ->with('xa',$xa)
                ->with('tenxa',$tenxa)
                ->with('huyen',$huyen)
                ->with('tinh',$tinh)
                ->with('pageTitle','In giấy chứng nhận nuôi con nuôi bản sao');
        }else
            return view('errors.notlogin');
    }

    public function printstokhai($id){
        if (Session::has('admin')) {
            $model = ConNuoi::find($id);
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
            return view('reports.connuoi.printtokhai')
                ->with('model',$model)
                ->with('xa',$xa)
                ->with('tencq',$tencq)
                ->with('pageTitle','In tờ khai đăng ký con nuôi');

        }else
            return view('errors.notlogin');
    }
}
