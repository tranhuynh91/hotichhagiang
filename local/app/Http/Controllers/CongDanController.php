<?php

namespace App\Http\Controllers;

use App\CongDan;
use App\DanToc;
use App\Districts;
use App\QuocTich;
use App\Towns;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;

class CongDanController extends Controller
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

            $huyens = $this->listHuyen();
            $xas = array();
            if($huyen != 'all'){
                $xas = $this->listXa($huyen);
            }

            $model = new CongDan();
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

            return view('manage.congdan.index')
                -> with('huyens', $huyens)
                -> with('xas', $xas)
                -> with('mahuyen', $huyen)
                -> with('maxa', $xa)
                -> with('model', $model)
                -> with('pageTitle','Thông tin công dân ('.$count.' công dân)');
        }else
            return view('errors.notlogin');
    }

    public function create(){
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

            $dantocs = DanToc::all();
            $quoctichs = QuocTich::all();

            return view('manage.congdan.create')
                ->with('huyens',$huyens)
                ->with('mahuyen',$huyendf)
                ->with('xas',$xas)
                ->with('maxa',$xadf)
                ->with('dantocs',$dantocs)
                ->with('quoctichs',$quoctichs)
                ->with('pageTitle','Thêm mới thông tin công dân');
        }else
            return view('errors.notlogin');

    }

    public function store(Request $request){
        if (Session::has('admin')) {
            $inputs = $request->all();
            $inputs['matinh'] = getmatinh();
            $inputs['macongdan'] = getmatinh().$inputs['mahuyen'].$inputs['maxa'].'CD'.getdate()[0];
            $inputs['ngaysinh'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaysinh'])));
            $model = new CongDan();
            $model->create($inputs);
            return redirect('congdan');
        }else
            return view('errors.notlogin');
    }

    public function edit($id){
        if (Session::has('admin')) {
            $model = CongDan::find($id);
            $huyens = Districts::all();
            $huyendf = $model->mahuyen;
            $xas = Towns::where('mahuyen',$huyendf)
                ->get();
            $xadf = $model->maxa;
            $dantocs = DanToc::all();
            $dantocdf = $model->dantoc;
            $quoctichs = QuocTich::all();
            $quoctichdf = $model->quoctich;

            if($model->tthonnhan == 'Chưa kết hôn'){
                $optiontthonnhan['Chưa kết hôn'] = 'Chưa kết hôn';
                $optiontthonnhan['Kết hôn lần đầu'] = 'Kết hôn lần đầu';
            }elseif($model->tthonnhan == 'Kết hôn lần đầu'){
                $optiontthonnhan['Kết hôn lần đầu'] = 'Kết hôn lần đầu';
                $optiontthonnhan['Ly hôn lần đầu'] = 'Ly hôn lần đầu';
            }elseif($model->tthonnhan == 'Ly hôn lần đầu'){
                $optiontthonnhan['Ly hôn lần đầu'] = 'Ly hôn lần đầu';
                $optiontthonnhan['Kết hôn lần hai'] = 'Kết hôn lần hai';
            }elseif($model->tthonnhan == 'Kết hôn lần hai'){
                $optiontthonnhan['Kết hôn lần hai'] = 'Kết hôn lần hai';
                $optiontthonnhan['Ly hôn lần hai'] = 'Ly hôn lần hai';
            }elseif($model->tthonnhan == 'Ly hôn lần hai'){
                $optiontthonnhan['Ly hôn lần hai'] = 'Ly hôn lần hai';
                $optiontthonnhan['Kết hôn lần ba'] = 'Kết hôn lần ba';
            }else{
                $optiontthonnhan['Chưa kết hôn'] = 'Chưa kết hôn';
                $optiontthonnhan['Kết hôn lần đầu'] = 'Kết hôn lần đầu';
            }



            return view('manage.congdan.edit')
                ->with('huyens',$huyens)
                ->with('mahuyen',$huyendf)
                ->with('xas',$xas)
                ->with('maxa',$xadf)
                ->with('dantocs',$dantocs)
                ->with('dantoc',$dantocdf)
                ->with('quoctichs',$quoctichs)
                ->with('quoctich',$quoctichdf)
                ->with('model',$model)
                ->with('optiontthonnhan',$optiontthonnhan)
                ->with('pageTitle','Chỉnh sửa thông tin công dân');
        }else
            return view('errors.notlogin');
    }

    public function update(Request $request,$id){
        if (Session::has('admin')) {
            $inputs = $request->all();
            $inputs['ngaysinh'] = date('Y-m-d', strtotime(str_replace('/', '-', $inputs['ngaysinh'])));
            $model = CongDan::find($id);
            $model->update($inputs);
            return redirect('congdan');

        }else
            return view('errors.notlogin');
    }

    public function destroy(Request $request){
        if (Session::has('admin')) {
            $inputs = $request->all();
            $id = $inputs['iddelete'];
            $model = CongDan::find($id);
            $model->delete();
            return redirect('congdan');
        }else
            return view('errors.notlogin');

    }

}
