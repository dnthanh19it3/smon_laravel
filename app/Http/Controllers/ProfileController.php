<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    function getProfile(Request $request, $user_id)
    {
        $user_info = DB::table('user_details')->where('uid', '=', $user_id)->first();
        return json_encode($user_info);
    }
    function editProfile(Request $request, $user_id)
    {
        $response = null;
        $data = ['display_name' => $request->display_name, 'dob' => $request->dob, 'gender' => $request->gender];
        foreach ($data as $key => $value) {
            if ($value == null) {
                unset($data[$key]);
            }
        }
        if (count($data) > 0) {
            $update = DB::table('user_details')->where('uid', '=', $user_id)->update($data);
            if ($update) {
                $response = [
                    'code' => 1,
                    'msg' => 'Thao tác thành công!'
                ];
            } else {
                $response = [
                    'code' => 0,
                    'msg' => 'Thao tác thất bại!'
                ];
            }
        } else{
            $response = [
                'code' => 0,
                'msg' => 'Thao tác thất bại!'
            ];
        }
        return $response;
    }
}
