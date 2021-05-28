<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AuthController extends Controller
{
    
    function newLogin(Request $request){
        $facebook_id = $request->facebook_id;
        $token = uniqid();
        $response = null;
        $user_data = null;
        $wallet_data = null;
        // Check is user
        if($this->isMember($facebook_id)){
            $store_token = DB::table('users')->where('facebook_id', $facebook_id)->update(['token' => $token, 'last_login' => Carbon::now()]);
            $user_data = DB::table('users')->join('user_details', 'users.id', '=', 'user_details.uid')->where('facebook_id', $facebook_id)->first();
            $wallet_data = DB::table('accounts')->where('user_id', $user_data->uid)->first();
        } else {
            $register = DB::table('users')->insertGetId(['facebook_id' => $facebook_id, 'status' => '1', 'login_time' => 0, 'token' => $token]);
            $generate_info = DB::table('user_details')->insert(['uid' => $register]);
            $generate_wallet = DB::table('accounts')->insertGetId(['user_id' => $register]);
            $user_data = DB::table('users')->join('user_details', 'users.id', '=', 'user_details.uid')->where('facebook_id', $facebook_id)->first();
            $wallet_data = DB::table('accounts')->where('id', $generate_wallet)->first();
        }
        // 
        if($user_data){
            $response = [
                'code' => 1,
                'msg' => "Đăng nhập thành công",
                'new_token' => $token,
                'user_data' => $user_data,
                'wallet_data' => $wallet_data
            ];
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
    function storedLogin(Request $request) {
        $response = null;
        $login = DB::table('users')->join('user_details', 'users.id', '=', 'user_details.uid')->where('users.id', $request->uid)->first();
        $wallet = DB::table('accounts')->where('user_id', $login->uid)->first();

        if($login){

            $response = [
                'code' => 1,
                'msg' => "Đăng nhập thành công!",
                'new_token' => null,
                'user_data' => $login,
                'wallet_data' => $wallet
            ];
        } else {
            $response = [
                'code' => 1,
                'msg' => "Đăng nhập thất bại!",
                'new_token' => null,
                'user_data' => null,
                'wallet_data' => null
            ];
        }
        return $response;
    }
    function logout(Request $request){

    }
    function isMember($facebook_id){
        $login = DB::table('users')->where('facebook_id', $facebook_id)->first();
        if($login){
            return true;
        } else {
            return false;
        }
    }
    function oldLogin(Request $request, $uid, $token){

    }
}
