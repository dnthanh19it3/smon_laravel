<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ClassicAuth extends Controller
{
    function register(Request $request){
        $response = null;

        $uid = DB::table('users')->insertGetId(['username' =>$request->username, 'password' =>$request->password]);
        $detail = DB::table('user_details')->insert(['uid' => $uid]);
        $wallet = DB::table('accounts')->insert(['user_id' => $uid]);

        if($uid && $wallet && $detail){
            $response = [
                'code' => 1,
                'msg' => "Đăng kí thành công",
                'new_token' => null,
                'user_data' => null,
                'wallet_data' => null];
        } else {
            $response = [
                'code' => 0,
                'msg' => "Đăng kí thất bại",
                'new_token' => null,
                'user_data' => null,
                'wallet_data' => null];
        }
        return $response;
    }
    function login_new(Request $request){
        $user_data = null;
        $token = uniqid();
        $wallet_data = null;
        $response = null;
        
        $user_data = DB::table('users')->join('user_details', 'users.id', '=', 'user_details.uid')
            ->where('username', $request->username)
            ->where('password', $request->password)
            ->first();

        if($user_data){
            $store_token = DB::table('users')->where('username', $request->username)->update(['token' => $token, 'last_login' => Carbon::now()]);
            $wallet_data = DB::table('accounts')->where('user_id', $user_data->uid)->first();
        }

        if($user_data && $store_token && $wallet_data){
            $response = [
                'code' => 1,
                'msg' => "Đăng nhập thành công",
                'new_token' => $token,
                'user_data' => $user_data,
                'wallet_data' => $wallet_data];
            } else {
                $response = [
                    'code' => 0,
                    'msg' => "Đăng nhập thất bại",
                    'new_token' => null,
                    'user_data' => null,
                    'wallet_data' => null
                ];
            }
        return $response;
    }
    function login_old(Request $request){
        $user_data = null;
        $wallet_data = null;
        $user_data = DB::table('users')->where('id', '=', $request->id)->where('token', $request->token)->first();

        if($user_data){
            $wallet_data = DB::table('accounts')->where('user_id', $user_data->id)->first();
        }
        if($wallet_data && $user_data){
            $response = [
                'code' => 1,
                'msg' => "Đăng nhập thành công",
                'new_token' => null,
                'user_data' => $user_data,
                'wallet_data' => $wallet_data];
        } else {
            $response = [
                'code' => 0,
                'msg' => "Đăng nhập thất bại",
                'new_token' => null,
                'user_data' => null,
                'wallet_data' => null
            ];
        }
        return $response;
    }
    function logout(Request $request, $user_id) {
       $logout = DB::table('users')->where('id', '=', $user_id)->update(['token' => null]);
       if($logout){
        $response = [
            'code' => 1,
            'msg' => "Đăng xuất thành công"];
       } else {
        $response = [
            'code' => 0,
            'msg' => "Đăng xuất thất bại"];
       }
       return $response;
    }
}
