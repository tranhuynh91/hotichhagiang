<?php

namespace App\Http\Controllers;

use App\CapBanSaoTrichLuc;
use App\chamecon;
use App\ConNuoi;
use App\Districts;
use App\GeneralConfigs;
use App\giamho;
use App\KetHon;
use App\KhaiSinh;
use App\KhaiTu;
use App\ThongTinThayDoi;
use App\Towns;
use App\TTHonNhan;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;

class ReportsController extends Controller
{
    public function index(Request $request){
        if (Session::has('admin')) {
            $day = date('Y-m-d');
            $nam = date('Y');
            $som = date("Y-m-01", strtotime($day));
            $eom = date("Y-m-t", strtotime($day));
            $inputs = $request->all();
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

            }elseif(session('admin')->level == 'H'){
                $huyen = isset($inputs['mahuyen']) ? $inputs['mahuyen'] : session('admin')->mahuyen;
                $xadf = Towns:: where('mahuyen', $huyen)->first()->maxa;
                $xa = isset($inputs['maxa']) ? $inputs['maxa'] : $xadf;
            }else{
                $huyen = isset($inputs['mahuyen']) ? $inputs['mahuyen'] : session('admin')->mahuyen;
                $xa = isset($inputs['maxa']) ? $inputs['maxa'] : session('admin')->maxa;
            }

            $huyens = listHuyen();
            $xas = array();
            if($huyen != 'all'){
                $xas = listXa($huyen);
            }

            return view('reports.bcth.index')
                ->with('som',date('d/m/Y',strtotime($som)))
                ->with('eom',date('d/m/Y',strtotime($eom)))
                -> with('huyens', $huyens)
                -> with('xas', $xas)
                -> with('mahuyen', $huyen)
                -> with('maxa', $xa)
                -> with('nam', $nam)
                ->with('pageTitle', 'Báo cáo sổ sách tổng hợp');

        } else {
            return view('errors.notlogin');
        }
    }

    public function sokhaisinh(Request $request){
        if (Session::has('admin')) {

            $inputs = $request->all();
            //dd($inputs);
            $ngaytu = date('Y-m-d',strtotime(str_replace('/', '-', $inputs['ngaytu'])));
            $ngayden = date('Y-m-d',strtotime(str_replace('/', '-', $inputs['ngayden'])));

            $xa = Towns::where('maxa',$inputs['xa'])->first()->tenxa;
            $huyen = Districts::where('mahuyen',$inputs['huyen'])
                ->first()->tenhuyen;
            $tinh = GeneralConfigs::first()->tendv;
            $tencq = $xa.' - '.$huyen .' - '.$tinh;


            $model = KhaiSinh::where('trangthai','Duyệt')
                ->where('maxa',$inputs['xa'])
                ->where('mahuyen',$inputs['huyen'])
                ->whereBetween('ngaydangky', [$ngaytu, $ngayden])
                ->get();

            $thaydoi = ThongTinThayDoi::where('trangthai','Duyệt')->get();

            return view('reports.bcth.sokhaisinh')
                ->with('inputs',$inputs)
                ->with('model',$model)
                ->with('tencq',$tencq)
                ->with('thaydoi',$thaydoi)
                ->with('pageTitle','Sổ khai sinh');

        } else {
            return view('errors.notlogin');
        }
    }

    public function sokhaitu(Request $request){
        if (Session::has('admin')) {

            $inputs = $request->all();
            //dd($inputs);
            $ngaytu = date('Y-m-d',strtotime(str_replace('/', '-', $inputs['ngaytu'])));
            $ngayden = date('Y-m-d',strtotime(str_replace('/', '-', $inputs['ngayden'])));

            $xa = Towns::where('maxa',$inputs['xa'])->first()->tenxa;
            $huyen = Districts::where('mahuyen',$inputs['huyen'])
                ->first()->tenhuyen;
            $tinh = GeneralConfigs::first()->tendv;
            $tencq = $xa.' - '.$huyen .' - '.$tinh;


            $khaitu = KhaiTu::where('trangthai','Duyệt')
                ->where('maxa',$inputs['xa'])
                ->where('mahuyen',$inputs['huyen'])
                ->whereBetween('ngaydangkykt', [$ngaytu, $ngayden])
                ->get();

            $thaydoi = ThongTinThayDoi::where('trangthai','Duyệt')->get();

            return view('reports.bcth.sokhaitu')
                ->with('inputs',$inputs)
                ->with('khaitu',$khaitu)
                ->with('tencq',$tencq)
                ->with('thaydoi',$thaydoi)
                ->with('pageTitle','Sổ khai tử');

        } else {
            return view('errors.notlogin');
        }
    }

    public function sotthonnhan(Request $request){
        if (Session::has('admin')) {

            $inputs = $request->all();
            //dd($inputs);
            $ngaytu = date('Y-m-d',strtotime(str_replace('/', '-', $inputs['ngaytu'])));
            $ngayden = date('Y-m-d',strtotime(str_replace('/', '-', $inputs['ngayden'])));
            if(session('admin')->level == 'T'){
                $tencq = GeneralConfigs::first()->tendv;
            }elseif(session('admin')=='H'){
                $huyen = Districts::where('mahuyen',session('admin')->mahuyen)
                    ->first()->tenhuyen;
                $tinh = GeneralConfigs::first()->tendv;
                $tencq = $huyen .' - '.$tinh;
            }else{
                $xa = Towns::where('maxa',session('admin')->maxa)->first()->tenxa;
                $huyen = Districts::where('mahuyen',session('admin')->mahuyen)
                    ->first()->tenhuyen;
                $tinh = GeneralConfigs::first()->tendv;
                $tencq = $xa.' - '.$huyen .' - '.$tinh;
            }
            $model = TTHonNhan::where('trangthai','Duyệt')
                ->where('maxa',$inputs['xa'])
                ->where('mahuyen',$inputs['huyen'])
                ->whereBetween('ngayxn', [$ngaytu, $ngayden])
                ->get();
            $thaydoi = ThongTinThayDoi::where('trangthai','Duyệt')->get();
            return view('reports.bcth.sotthonnhan')
                ->with('inputs',$inputs)
                ->with('model',$model)
                ->with('tencq',$tencq)
                ->with('thaydoi',$thaydoi)
                ->with('pageTitle','Sổ cấp giấy xác nhận tình trạng hôn nhân');

        } else {
            return view('errors.notlogin');
        }
    }

    public function sokethon(Request $request){
        if (Session::has('admin')) {

            $inputs = $request->all();
            //dd($inputs);
            $ngaytu = date('Y-m-d',strtotime(str_replace('/', '-', $inputs['ngaytu'])));
            $ngayden = date('Y-m-d',strtotime(str_replace('/', '-', $inputs['ngayden'])));

            $xa = Towns::where('maxa',$inputs['xa'])->first()->tenxa;
            $huyen = Districts::where('mahuyen',$inputs['huyen'])
                ->first()->tenhuyen;
            $tinh = GeneralConfigs::first()->tendv;
            $tencq = $xa.' - '.$huyen .' - '.$tinh;


            $model = KetHon::where('trangthai','Duyệt')
                ->where('maxa',$inputs['xa'])
                ->where('mahuyen',$inputs['huyen'])
                ->whereBetween('ngaydangky', [$ngaytu, $ngayden])
                ->get();
            $thaydoi = ThongTinThayDoi::where('trangthai','Duyệt')->get();
            return view('reports.bcth.sokethon')
                ->with('inputs',$inputs)
                ->with('model',$model)
                ->with('tencq',$tencq)
                ->with('thaydoi',$thaydoi)
                ->with('pageTitle','Sổ đăng ký kết hôn');

        } else {
            return view('errors.notlogin');
        }
    }

    public function sogiamho(Request $request){
        if (Session::has('admin')) {

            $inputs = $request->all();
            //dd($inputs);
            $ngaytu = date('Y-m-d',strtotime(str_replace('/', '-', $inputs['ngaytu'])));
            $ngayden = date('Y-m-d',strtotime(str_replace('/', '-', $inputs['ngayden'])));

            $xa = Towns::where('maxa',$inputs['xa'])->first()->tenxa;
            $huyen = Districts::where('mahuyen',$inputs['huyen'])
                ->first()->tenhuyen;
            $tinh = GeneralConfigs::first()->tendv;
            $tencq = $xa.' - '.$huyen .' - '.$tinh;


            $model = giamho::where('trangthai','Duyệt')
                ->where('maxa',$inputs['xa'])
                ->where('mahuyen',$inputs['huyen'])
                ->where('phanloainhap','Giám hộ')
                ->whereBetween('ngaydangky', [$ngaytu, $ngayden])
                ->get();
            $thaydoi = ThongTinThayDoi::where('trangthai','Duyệt')->get();
            return view('reports.bcth.sogiamho')
                ->with('inputs',$inputs)
                ->with('model',$model)
                ->with('tencq',$tencq)
                ->with('thaydoi',$thaydoi)
                ->with('pageTitle','Sổ đăng ký giám hộ');

        } else {
            return view('errors.notlogin');
        }
    }

    public function sochamdutgh(Request $request){
        if (Session::has('admin')) {

            $inputs = $request->all();
            //dd($inputs);
            $ngaytu = date('Y-m-d',strtotime(str_replace('/', '-', $inputs['ngaytu'])));
            $ngayden = date('Y-m-d',strtotime(str_replace('/', '-', $inputs['ngayden'])));

            $xa = Towns::where('maxa',$inputs['xa'])->first()->tenxa;
            $huyen = Districts::where('mahuyen',$inputs['huyen'])
                ->first()->tenhuyen;
            $tinh = GeneralConfigs::first()->tendv;
            $tencq = $xa.' - '.$huyen .' - '.$tinh;


            $model = giamho::where('trangthai','Duyệt')
                ->where('maxa',$inputs['xa'])
                ->where('mahuyen',$inputs['huyen'])
                ->where('phanloainhap','Chấm dứt giám hộ')
                ->whereBetween('ngaydangky', [$ngaytu, $ngayden])
                ->get();
            $thaydoi = ThongTinThayDoi::where('trangthai','Duyệt')->get();
            return view('reports.bcth.sochamdutgh')
                ->with('inputs',$inputs)
                ->with('model',$model)
                ->with('tencq',$tencq)
                ->with('thaydoi',$thaydoi)
                ->with('pageTitle','Sổ chấm dứt giám hộ');

        } else {
            return view('errors.notlogin');
        }
    }

    public function sodknhancmc(Request $request){
        if (Session::has('admin')) {

            $inputs = $request->all();
            //dd($inputs);
            $ngaytu = date('Y-m-d',strtotime(str_replace('/', '-', $inputs['ngaytu'])));
            $ngayden = date('Y-m-d',strtotime(str_replace('/', '-', $inputs['ngayden'])));

            $xa = Towns::where('maxa',$inputs['xa'])->first()->tenxa;
            $huyen = Districts::where('mahuyen',$inputs['huyen'])
                ->first()->tenhuyen;
            $tinh = GeneralConfigs::first()->tendv;
            $tencq = $xa.' - '.$huyen .' - '.$tinh;


            $model = chamecon::where('trangthai','Duyệt')
                ->where('maxa',$inputs['xa'])
                ->where('mahuyen',$inputs['huyen'])
                ->whereBetween('ngaydangky', [$ngaytu, $ngayden])
                ->get();
            $thaydoi = ThongTinThayDoi::where('trangthai','Duyệt')->get();
            return view('reports.bcth.sodknhancmc')
                ->with('inputs',$inputs)
                ->with('model',$model)
                ->with('tencq',$tencq)
                ->with('thaydoi',$thaydoi)
                ->with('pageTitle','Sổ đăng ký nhận cha mẹ con');

        } else {
            return view('errors.notlogin');
        }
    }

    public function soconnuoi(Request $request){
        if (Session::has('admin')) {

            $inputs = $request->all();
            //dd($inputs);
            $ngaytu = date('Y-m-d',strtotime(str_replace('/', '-', $inputs['ngaytu'])));
            $ngayden = date('Y-m-d',strtotime(str_replace('/', '-', $inputs['ngayden'])));

            $xa = Towns::where('maxa',$inputs['xa'])->first()->tenxa;
            $huyen = Districts::where('mahuyen',$inputs['huyen'])
                ->first()->tenhuyen;
            $tinh = GeneralConfigs::first()->tendv;
            $tencq = $xa.' - '.$huyen .' - '.$tinh;


            $model = ConNuoi::where('trangthai','Duyệt')
                ->where('maxa',$inputs['xa'])
                ->where('mahuyen',$inputs['huyen'])
                ->whereBetween('ngaythangdk', [$ngaytu, $ngayden])
                ->get();
            $thaydoi = ThongTinThayDoi::where('trangthai','Duyệt')->get();
            return view('reports.bcth.soconnuoi')
                ->with('inputs',$inputs)
                ->with('model',$model)
                ->with('tencq',$tencq)
                ->with('thaydoi',$thaydoi)
                ->with('pageTitle','Sổ đăng ký nuôi con nuôi');

        } else {
            return view('errors.notlogin');
        }
    }

    public function sotrichluc(Request $request){
        if (Session::has('admin')) {

            $inputs = $request->all();
            //dd($inputs);
            $ngaytu = date('Y-m-d',strtotime(str_replace('/', '-', $inputs['ngaytu'])));
            $ngayden = date('Y-m-d',strtotime(str_replace('/', '-', $inputs['ngayden'])));

            if(session('admin')->level == 'T') {
                $model = CapBanSaoTrichLuc::where('trangthai', 'Duyệt')
                    ->where('level','T')
                    ->whereBetween('ngaycap', [$ngaytu, $ngayden])
                    ->get();
                $tendv = GeneralConfigs::first()->tendv;
            }elseif(session('admin')->level == 'H') {
                $model = CapBanSaoTrichLuc::where('trangthai', 'Duyệt')
                    ->where('level', 'H')
                    ->where('madv',session('admin')->mahuyen)
                    ->whereBetween('ngaycap', [$ngaytu, $ngayden])
                    ->get();
                $tendv = Districts::where('mahuyen',session('admin')->mahuyen)->first()->tenhuyen;
            }else{
                $model = CapBanSaoTrichLuc::where('trangthai', 'Duyệt')
                    ->where('level', 'X')
                    ->where('madv',session('admin')->maxa)
                    ->whereBetween('ngaycap', [$ngaytu, $ngayden])
                    ->get();
                $tendv = Towns::where('maxa',session('admin')->maxa)->first()->tenxa;
            }

            return view('reports.bcth.sotrichluc')
                ->with('inputs',$inputs)
                ->with('model',$model)
                ->with('tendv',$tendv)
                ->with('pageTitle','Sổ cấp bản sao trích lục');

        } else {
            return view('errors.notlogin');
        }
    }

    public function sothaydoi(Request $request){
        if (Session::has('admin')) {

            $inputs = $request->all();
            //dd($inputs);
            $ngaytu = date('Y-m-d',strtotime(str_replace('/', '-', $inputs['ngaytu'])));
            $ngayden = date('Y-m-d',strtotime(str_replace('/', '-', $inputs['ngayden'])));

            $xa = Towns::where('maxa',$inputs['xa'])->first()->tenxa;
            $huyen = Districts::where('mahuyen',$inputs['huyen'])
                ->first()->tenhuyen;
            $tinh = GeneralConfigs::first()->tendv;
            $tencq = $xa.' - '.$huyen .' - '.$tinh;


            $model = ThongTinThayDoi::where('trangthai','Duyệt')
                ->where('maxa',$inputs['xa'])
                ->where('mahuyen',$inputs['huyen'])
                ->whereBetween('ngaydk', [$ngaytu, $ngayden])
                ->get();

            return view('reports.bcth.sothaydoi')
                ->with('inputs',$inputs)
                ->with('model',$model)
                ->with('tencq',$tencq)
                ->with('pageTitle','Sổ đăng ký thay đổi, cải chính, xác định lại dân tộc, bổ xung hộ tịch');

        } else {
            return view('errors.notlogin');
        }
    }

    public function bcksktkh(Request $request){
        if (Session::has('admin')) {
            $inputs = $request->all();
            $nam = $inputs['nam'];
            $ngaythangnam = date('Y-m-d');
            $tuoichong=0;$tuoivo=0;
            $kybaocao = $inputs['kybaocao'];
            $Count1=0;$Count2=0;$Count3=0;$Count4=0;$Count5=0;$Count6=0;$Count7=0;
            $Count8=0;$Count9=0;$Count10=0;$Count11=0;$Count12=0;
            $Count13=0;$Count14=0;$Count15=0;$Count16=0;$Count17=0;
            $Count18=0;$Count19=0;$Count22=0;
            if(isset($inputs['ngaytu']) || isset($inputs['ngayden']))
            {
                $ngaytu = date('Y-m-d',strtotime(str_replace('/', '-', $inputs['ngaytu'])));
                $ngayden = date('Y-m-d',strtotime(str_replace('/', '-', $inputs['ngayden'])));
            }
            else
            {

                if($inputs['kybaocao']=="Báo cáo 6 tháng đầu năm") {$ngaytu=$nam.'-01-01';$ngayden=$nam.'-06-06';}
                elseif ($inputs['kybaocao']=="Báo cáo năm") {$ngaytu=$nam.'-01-01';$ngayden=$nam.'-11-07';}
                elseif ($inputs['kybaocao']=="Báo cáo năm chính thức") {$nam2 = $nam + 1;$ngaytu=$nam.'-01-20';$ngayden=$nam2.'-01-20';}

            }

            $xa = Towns::where('maxa',$inputs['xa'])->first()->tenxa;
            $huyen = Districts::where('mahuyen',$inputs['huyen'])
                ->first()->tenhuyen;
            $tinh = GeneralConfigs::first()->tendv;
            $tencq = $xa.' - '.$huyen .' - '.$tinh;

            $khaisinh = KhaiSinh::where('trangthai','Duyệt')
                ->where('maxa',$inputs['xa'])->where('mahuyen',$inputs['huyen'])->whereBetween('ngaydangky', [$ngaytu, $ngayden])->get();

            $khaitu = KhaiTu::where('trangthai','Duyệt')
                ->where('maxa',$inputs['xa'])->where('mahuyen',$inputs['huyen'])->whereBetween('ngaydangkykt', [$ngaytu, $ngayden])->get();

            $kethon = KetHon::where('trangthai','Duyệt')
                ->where('maxa',$inputs['xa'])->where('mahuyen',$inputs['huyen'])->whereBetween('ngaydangky', [$ngaytu, $ngayden])->get();
            //Khai sinh
            foreach($khaisinh as $ks){if(isset($ks->id) && $ks->pldangky=="Đăng ký mới"){$Count1++;}}
            foreach($khaisinh as $ks){if($ks->dantocks=="Kinh" && $ks->pldangky=="Đăng ký mới"){$Count2++;}}
            foreach($khaisinh as $ks){if($ks->dantocks!="Kinh" && $ks->pldangky=="Đăng ký mới"){$Count3++;}}
            foreach($khaisinh as $ks){if($ks->gioitinhks=="Nam" && $ks->pldangky=="Đăng ký mới"){$Count4++;}}
            foreach($khaisinh as $ks){if($ks->gioitinhks=="Nữ" && $ks->pldangky=="Đăng ký mới"){$Count5++;}}
            foreach($khaisinh as $ks){if($ks->dunghanquahan=="Đúng hạn" && $ks->pldangky=="Đăng ký mới"){$Count6++;}}
            foreach($khaisinh as $ks){if($ks->dunghanquahan=="Quá hạn" || $ks->dunghanquahan=="Quá hạn dưới 5 tuổi" && $ks->pldangky=="Đăng ký mới"){$Count7++;}}
            foreach($khaisinh as $ks){if($ks->pldangky=="Đăng ký mới" && $ks->dunghanquahan=="Quá hạn" ){$Count8++;}}
            foreach($khaisinh as $ks){if($ks->dunghanquahan=="Quá hạn dưới 5 tuổi" && $ks->pldangky=="Đăng ký mới"){$Count9++;}}
            foreach($khaisinh as $ks){if($ks->pldangky=="Đăng ký lại"){$Count10++;}}
            //Khai tử
            foreach($khaitu as $kt){if(isset($kt->id) && $kt->phanloaidk=="Đăng ký mới"){$Count11++;}}
            foreach($khaitu as $kt){if($kt->phanloaituoi=="Dưới 1 tuổi" && $kt->phanloaidk=="Đăng ký mới"){$Count12++;}}
            foreach($khaitu as $kt){if($kt->phanloaituoi=="Từ 1 tuổi đến dưới 5 tuổi" && $kt->phanloaidk=="Đăng ký mới"){$Count13++;}}
            foreach($khaitu as $kt){if($kt->phanloaituoi=="Từ 5 tuổi trở lên" && $kt->phanloaidk=="Đăng ký mới"){$Count14++;}}
            foreach($khaitu as $kt){if($kt->phanloaikt=="Đúng hạn" && $kt->phanloaidk=="Đăng ký mới"){$Count15++;}}
            foreach($khaitu as $kt){if($kt->phanloaikt=="Quá hạn" && $kt->phanloaidk=="Đăng ký mới"){$Count16++;}}
            foreach($khaitu as $kt){if($kt->phanloaidk=="Đăng ký lại"){$Count17++;}}
            //Kết hôn
            foreach($kethon as $kh){if(isset($kh->id) && ($kh->pldangky=="Đăng ký mới" || $kh->pldangky=="Đăng ký mới(lần đầu)")){$Count18++;}}
            foreach($kethon as $kh){if($kh->pldangky=="Đăng ký mới(lần đầu)"){$Count19++;}}
            foreach($kethon as $kh)
            {
                if($kh->pldangky=="Đăng ký mới(lần đầu)")
                {
                    $tuoichong = $tuoichong + $this->getAge($kh->ngaysinhchong)/$Count19;
                    $tuoivo = $tuoivo + $this->getAge($kh->ngaysinhvo)/$Count19;
                }
            }
            foreach($kethon as $kh){if($kh->pldangky=="Đăng ký lại"){$Count22++;}}

            return view('reports.bcth.bcksktkh')
                ->with('inputs',$inputs)->with('Count1',$Count1)->with('Count2',$Count2)->with('Count3',$Count3)->with('Count4',$Count4)
                ->with('Count5',$Count5)->with('Count6',$Count6)->with('Count7',$Count7)->with('Count8',$Count8)
                ->with('Count9',$Count9)->with('Count10',$Count10)->with('Count11',$Count11)->with('Count12',$Count12)
                ->with('Count13',$Count13)->with('Count14',$Count14)->with('Count15',$Count15)->with('Count16',$Count16)
                ->with('Count17',$Count17)->with('Count18',$Count18)->with('Count19',$Count19)->with('Count22',$Count22)
                ->with('tuoichong',$tuoichong)->with('tuoivo',$tuoivo)
                ->with('ngaytu',$ngaytu)
                ->with('ngayden',$ngayden)
                ->with('khaisinh',$khaisinh)
                ->with('khaitu',$khaitu)
                ->with('kethon',$kethon)
                ->with('tencq',$tencq)
                ->with('kybaocao',$kybaocao)
                ->with('xa',$xa)->with('ngaythangnam',$ngaythangnam)
                ->with('pageTitle','Kết quả đăng ký Khai sinh, Khai tử, Kết hôn (Cấp Xã)');

        } else {
            return view('errors.notlogin');
        }
    }

    public function getAge($birthdate = '0000-00-00') {
        if ($birthdate == '0000-00-00') return 'Unknown';

        $bits = explode('-', $birthdate);
        $age = date('Y') - $bits[0] - 1;

        $arr[1] = 'm';
        $arr[2] = 'd';

        for ($i = 1; $arr[$i]; $i++) {
            $n = date($arr[$i]);
            if ($n < $bits[$i])
                break;
            if ($n > $bits[$i]) {
                ++$age;
                break;
            }
        }
        return $age;
    }

    public function bchotichkhac(Request $request){
        if (Session::has('admin')) {

            $inputs = $request->all();
            //dd($inputs);
            $ngaytu = date('Y-m-d',strtotime(str_replace('/', '-', $inputs['ngaytu'])));
            $ngayden = date('Y-m-d',strtotime(str_replace('/', '-', $inputs['ngayden'])));

            $xa = Towns::where('maxa',$inputs['xa'])->first()->tenxa;
            $huyen = Districts::where('mahuyen',$inputs['huyen'])
                ->first()->tenhuyen;
            $tinh = GeneralConfigs::first()->tendv;
            $tencq = $xa.' - '.$huyen .' - '.$tinh;


            $model = ThongTinThayDoi::where('trangthai','Duyệt')
                ->where('maxa',$inputs['xa'])
                ->where('mahuyen',$inputs['huyen'])
                ->whereBetween('ngaydk', [$ngaytu, $ngayden])
                ->get();

            return view('reports.bcth.bchotichkhac')
                ->with('inputs',$inputs)
                ->with('model',$model)
                ->with('tencq',$tencq)
                ->with('pageTitle','Kết quả đăng ký các việc Hộ tịch khác (Cấp Xã)');

        } else {
            return view('errors.notlogin');
        }
    }

    public function bcconnuoi(Request $request){
        if (Session::has('admin')) {
            $inputs = $request->all();
            $nam = $inputs['nam'];
            $ngaythangnam = date('Y-m-d');
            $kybaocao = $inputs['kybaocao'];
            $Count1=0;$Count2=0;$Count3=0;$Count4=0;$Count5=0;$Count6=0;$Count7=0;
            $Count8=0;$Count9=0;$Count10=0;$Count11=0;$Count12=0;
            if(isset($inputs['ngaytu']) || isset($inputs['ngayden']))
            {
                $ngaytu = date('Y-m-d',strtotime(str_replace('/', '-', $inputs['ngaytu'])));
                $ngayden = date('Y-m-d',strtotime(str_replace('/', '-', $inputs['ngayden'])));
            }
            else
            {
                if($inputs['kybaocao']=="Báo cáo 6 tháng đầu năm") {$ngaytu=$nam.'-01-01';$ngayden=$nam.'-06-06';}
                elseif ($inputs['kybaocao']=="Báo cáo năm") {$ngaytu=$nam.'-01-01';$ngayden=$nam.'-11-07';}
                elseif ($inputs['kybaocao']=="Báo cáo năm chính thức") {$nam2 = $nam + 1;$ngaytu=$nam.'-01-20';$ngayden=$nam2.'-01-20';}
            }

            $xa = Towns::where('maxa',$inputs['xa'])->first()->tenxa;
            $huyen = Districts::where('mahuyen',$inputs['huyen'])
                ->first()->tenhuyen;
            $tinh = GeneralConfigs::first()->tendv;
            $tencq = $xa.' - '.$huyen .' - '.$tinh;
            $connuoi = ConNuoi::where('trangthai','Duyệt')
                ->where('maxa',$inputs['xa'])->where('mahuyen',$inputs['huyen'])->whereBetween('ngaythangdk', [$ngaytu, $ngayden])->get();

            foreach($connuoi as $cn){if(isset($cn->id)){$Count1++;}}
            foreach($connuoi as $cn){if($cn->gioitinhconnuoi="Nam" && $cn->tuoiconnuoi=="Dưới 01 tuổi"){$Count2++;}}
            foreach($connuoi as $cn){if($cn->gioitinhconnuoi="Nữ" && $cn->tuoiconnuoi=="Dưới 01 tuổi"){$Count3++;}}
            foreach($connuoi as $cn){if($cn->gioitinhconnuoi="Nam" && $cn->tuoiconnuoi=="Từ 01 đến dưới 5 tuổi"){$Count4++;}}
            foreach($connuoi as $cn){if($cn->gioitinhconnuoi="Nữ" && $cn->tuoiconnuoi=="Từ 01 đến dưới 5 tuổi"){$Count5++;}}
            foreach($connuoi as $cn){if($cn->gioitinhconnuoi="Nam" && $cn->tuoiconnuoi=="Từ 5 tuổi trở lên"){$Count6++;}}
            foreach($connuoi as $cn){if($cn->gioitinhconnuoi="Nữ" && $cn->tuoiconnuoi=="Từ 5 tuổi trở lên"){$Count7++;}}
            foreach($connuoi as $cn){if($cn->tinhtrangsk=="Bình thường"){$Count8++;}}
            foreach($connuoi as $cn){if($cn->tinhtrangsk=="Trẻ em có nhu cầu đặc biệt"){$Count9++;}}
            foreach($connuoi as $cn){if($cn->thuongtrucn=="Cơ sở nuôi dưỡng"){$Count10++;}}
            foreach($connuoi as $cn){if($cn->thuongtrucn=="Gia đình"){$Count11++;}}
            foreach($connuoi as $cn){if($cn->thuongtrucn=="Nơi khác"){$Count12++;}}

            return view('reports.bcth.bcconnuoi')
                ->with('inputs',$inputs)->with('Count1',$Count1)->with('Count2',$Count2)->with('Count3',$Count3)->with('Count4',$Count4)
                ->with('Count5',$Count5)->with('Count6',$Count6)->with('Count7',$Count7)->with('Count8',$Count8)
                ->with('Count9',$Count9)->with('Count10',$Count10)->with('Count11',$Count11)->with('Count12',$Count12)
                ->with('tencq',$tencq)
                ->with('xa',$xa)
                ->with('connuoi',$connuoi)
                ->with('ngaytu',$ngaytu)
                ->with('ngayden',$ngayden)
                ->with('kybaocao',$kybaocao)
                ->with('ngaythangnam',$ngaythangnam)
                ->with('pageTitle','Kết quả đăng ký nuôi con nuôi trong nước (Cấp Xã)');

        } else {
            return view('errors.notlogin');
        }
    }
}
