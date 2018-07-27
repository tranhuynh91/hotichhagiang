<?php

namespace App\Http\Controllers;

use App\Districts;
use App\Towns;
use App\Users;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;

class TownsController extends Controller
{
    public function index(Request $request){
        if (Session::has('admin')) {
            $inputs = $request->all();
            $huyendf = Districts::first()->mahuyen;
            $select_huyen = isset($inputs['mahuyen']) ? $inputs['mahuyen'] : $huyendf;

            $listhuyen = Districts::all();
            $model = Towns::where('mahuyen',$select_huyen)
                ->get();
            return view ('system.towns.index')
                ->with('model',$model)
                ->with('listhuyen',$listhuyen)
                ->with('select_huyen',$select_huyen)
                ->with('pageTitle','Thông tin xã phường');
        }else
            return view('errors.notlogin');
    }

    public function create(){
        if (Session::has('admin')) {
            $listhuyen = Districts::all();
            return view ('system.towns.create')
                ->with('listhuyen',$listhuyen)
                ->with('pageTitle','Thêm mới thông tin xã phường');
        }else
            return view('errors.notlogin');
    }

    public function store(Request $request){
        if (Session::has('admin')) {
            $inputs = $request->all();
            $inputs['tenhuyen'] = Districts::where('mahuyen',$inputs['mahuyen'])->first()->tenhuyen;
            $model = new Towns();
            if($model->create($inputs)){
                $user = new Users();
                $user->name = $inputs['tenxa'];
                $user->username = $inputs['username'];
                $user->password = md5($inputs['password']);
                $user->phone = $inputs['dienthoai'];
                $user->email = $inputs['email'];
                $user->status = 'Kích hoạt';
                $user->mahuyen = $inputs['mahuyen'];
                $user->maxa = $inputs['maxa'];
                $user->level = 'X';
                $user->save();
            }
            return redirect('towns');

        }else
            return view('errors.notlogin');
    }

    public function edit($id){
        if (Session::has('admin')) {
            $listhuyen = Districts::all();
            $model = Towns::find($id);
            return view ('system.towns.edit')
                ->with('model',$model)
                ->with('listhuyen',$listhuyen)
                ->with('pageTitle','Chỉnh sửa thông tin xã phường');
        }else
            return view('errors.notlogin');
    }

    public function update(Request $request,$id){
        if (Session::has('admin')) {
            $inputs = $request->all();
            $model = Towns::find($id);
            $model->update($inputs);
            return redirect('towns');
        }else
            return view('errors.notlogin');
    }

    public function destroy(Request $request){
        if (Session::has('admin')) {
            $inputs = $request->all();
            $id = $inputs['iddelete'];
            $model = Towns::find($id);
            $model->delete();
            return redirect('towns');
        }else
            return view('errors.notlogin');
    }
}
