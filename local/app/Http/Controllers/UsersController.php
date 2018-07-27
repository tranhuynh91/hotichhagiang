<?php

namespace App\Http\Controllers;

use App\Districts;
use App\DmDvQl;
use App\DnDvLt;
use App\DnDvLtReg;
use App\DonViDvVt;
use App\DonViDvVtReg;
use App\Register;
use App\Users;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\Session;

class UsersController extends Controller
{
    public function login()
    {
        return view('system.users.login')
            ->with('pageTitle', 'Đăng nhập hệ thống');
    }

    public function signin(Request $request)
    {
        $input = $request->all();
        $check = Users::where('username', $input['username'])->count();
        if ($check == 0)
            return view('errors.invalid-user');
        else
            $ttuser = Users::where('username', $input['username'])->first();


        if (md5($input['password']) == $ttuser->password) {
            if ($ttuser->status == "Kích hoạt") {
                if ($ttuser->level == 'DVVT') {
                    $ttdnvt = DonViDvVt::where('masothue', $ttuser->mahuyen)
                        ->first();
                    $dvvt = $ttdnvt->setting;
                    $ttuser->dvvtcc = $dvvt;
                }
                Session::put('admin', $ttuser);

                return redirect('')
                    ->with('pageTitle', 'Tổng quan');
            } else
                return view('errors.lockuser');

        } else
            return view('errors.invalid-pass');
    }

    public function cp(){

        if (Session::has('admin')) {

            return view('system.users.change-pass')
                ->with('pageTitle', 'Thay đổi mật khẩu');

        } else
            return view('errors.notlogin');

    }

    public function cpw(Request $request)
    {

        $update = $request->all();

        $username = session('admin')->username;

        $password = session('admin')->password;

        $newpass2 = $update['newpassword2'];

        $currentPassword = $update['current-password'];

        if (md5($currentPassword) == $password) {
            $ttuser = Users::where('username', $username)->first();
            $ttuser->password = md5($newpass2);
            if ($ttuser->save()) {
                Session::flush();
                return view('errors.changepassword-success');
            }
        } else {
            dd('Mật khẩu cũ không đúng???');
        }
    }

    public function checkpass(Request $request)
    {
        $input = $request->all();
        $passmd5 = md5($input['pass']);

        if (session('admin')->password == $passmd5) {
            echo 'ok';
        } else {
            echo 'cancel';
        }
    }

    public function logout()
    {
        if (Session::has('admin')) {
            Session::flush();
            return redirect('/login');
        } else {
            return redirect('');
        }
    }

    public function index(Request $request)
    {
        if (Session::has('admin')) {
            $inputs = $request->all();
            $huyen = isset($inputs['mahuyen']) ?  $inputs['mahuyen'] : 'all';
            $level = isset($inputs['level']) ?  $inputs['level'] : 'T' ;


            $users = User::where('level',$level);
            if($level == 'T'){
                $users = $users->where('sadmin','<>','ssa');
            }elseif($level == 'X'){
                $mahuyendf = Districts::first()->mahuyen;
                $huyen = isset($inputs['mahuyen']) ?  $inputs['mahuyen'] : $mahuyendf;
                $users = $users->where('mahuyen', $huyen);
            }

            $model = $users->get();



            $listhuyen = Districts::all();

                return view('system.users.index')
                    ->with('model', $model)
                    ->with('listhuyen',$listhuyen)
                    ->with('level', $level)
                    ->with('mahuyen',$huyen)
                    ->with('pageTitle', 'Danh sách tài khoản');

        } else {
            return view('errors.notlogin');
        }
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (Session::has('admin')) {
            if (session('admin')->sadmin == 'ssa' || session('admin')->sadmin == 'sa') {
                $model = Users::findOrFail($id);
                return view('system.users.edit')
                    ->with('model', $model)
                    ->with('pageTitle', 'Chỉnh sửa thông tin tài khoản');
            }else{
                return view('errors.perm');
            }

        } else {
            return view('errors.notlogin');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (Session::has('admin')) {
            $input = $request->all();
            $model = Users::findOrFail($id);
            if(session('admin')->sadmin == 'ssa' || $model->cqcq == session('admin')->cqcq) {
                $model->name = $input['name'];
                $model->phone = $input['phone'];
                $model->email = $input['email'];
                $model->status = $input['status'];
                if ($input['newpass'] != '')
                    $model->password = md5($input['newpass']);
                $model->save();

                return redirect('users');
            }else
                return view('errors.noperm');

        } else {
            return redirect('');
        }
    }

    public function destroy(Request $request)
    {
        if (Session::has('admin')) {
            $id = $request->all()['iddelete'];
            $model = Users::findorFail($id);
            $model->delete();

            return redirect('users');

        } else
            return view('errors.notlogin');
    }

    public function permission($id)
    {
        if (Session::has('admin')) {

            $model = Users::findorFail($id);
            $permission = !empty($model->permission) ? $model->permission : getPermissionDefault($model->level);
            return view('system.users.perms')
                ->with('permission', json_decode($permission))
                ->with('model', $model)
                ->with('pageTitle', 'Phân quyền cho tài khoản');

        } else
            return view('errors.notlogin');
    }

    public function uppermission(Request $request)
    {
        if (Session::has('admin')) {
            $update = $request->all();

            $id = $update['id'];

            $model = Users::findOrFail($id);
            //dd($model);
            if (isset($model)) {
                $update['roles'] = isset($update['roles']) ? $update['roles'] : null;

                $model->permission = json_encode($update['roles']);
                $model->save();

                return redirect('users');

            } else
                dd('Tài khoản không tồn tại');

        } else
            return view('errors.notlogin');
    }

    public function lockuser($ids)
    {

        $arrayid = explode('-', $ids);
        foreach ($arrayid as $id) {
            $model = Users::findOrFail($id);
            $model->status = "Vô hiệu";
            $model->save();
        }
        return redirect('users');

    }

    public function unlockuser($ids)
    {
        $arrayid = explode('-', $ids);
        foreach ($arrayid as $id) {
            $model = Users::findOrFail($id);
            $model->status = "Kích hoạt";
            $model->save();
        }
        return redirect('users');

    }

    public function settinguser(){
        if (Session::has('admin')) {

            return view('system.users.usersetting')
                ->with('pageTitle', 'Thông tin tài khoản');

        } else
            return view('errors.notlogin');

    }

    public function settinguserw(Request $request){
        $update = $request->all();

        $username = session('admin')->username;

        $password = session('admin')->password;

        $currentPassword = $update['current-password'];

        if (md5($currentPassword) == $password) {
            $ttuser = Users::where('username', $username)->first();
            $ttuser->emailxt = $update['emailxt'];
            $ttuser->question = $update['question'];
            $ttuser->answer = $update['answer'];
            $ttuser->save();
            return redirect('');
        } else {
            dd('Mật khẩu cũ không đúng???');
        }
    }
}
