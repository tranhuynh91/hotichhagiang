<?php

namespace App\Http\Controllers;

use App\Districts;
use App\Users;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;

class DistrictsController extends Controller
{
    public function index(){
        if (Session::has('admin')) {
            $model = Districts::all();
            return view ('system.districts.index')
                ->with('model',$model)
                ->with('pageTitle','Thông tin quận huyện');
        }else
            return view('errors.notlogin');
    }

    public function create(){
        if (Session::has('admin')) {
            return view ('system.districts.create')
                ->with('pageTitle','Thông tin quận huyện');
        }else
            return view('errors.notlogin');
    }

    public function store(Request $request){
        if (Session::has('admin')) {
            $inputs = $request->all();
            $model = new Districts();
            if($model->create($inputs)){
                $user = new Users();
                $user->name = $inputs['tenhuyen'];
                $user->username = $inputs['username'];
                $user->password = md5($inputs['password']);
                $user->phone = $inputs['dienthoai'];
                $user->email = $inputs['email'];
                $user->status = 'Kích hoạt';
                $user->mahuyen = $inputs['mahuyen'];
                $user->level = 'H';
                $user->save();
            }
            return redirect('districts');
        }else
            return view('errors.notlogin');
    }

    public function edit($id){
        if (Session::has('admin')) {
            $model = Districts::findOrFail($id);
            return view ('system.districts.edit')
                ->with('model',$model)
                ->with('pageTitle','Chỉnh sửa thông tin quận huyện');
        }else
            return view('errors.notlogin');
    }

    public function update(Request $request, $id){
        if (Session::has('admin')) {
            $inputs = $request->all();
            $model = Districts::find($id);
            $model->update($inputs);
            return redirect('districts');
        }else
            return view('errors.notlogin');
    }

    public function destroy(Request $request){
        if (Session::has('admin')) {
            $inputs = $request->all();
            $id = $inputs['iddelete'];
            $model = Districts::find($id);
            $model->delete();
            return redirect('districts');
        }else
            return view('errors.notlogin');
    }
}
