<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;

class WalletController extends Controller
{
    function getWallet(Request $request, $user_id){
        $wallet = DB::table('accounts')->where('user_id', $user_id)->first();
        if(!$wallet){
            $stmt = DB::table('accounts')->insert([
                'user_id' => $user_id,
                'balance' => 0,
                
            ]);
        }
        $walletList = DB::table('accounts')->where('user_id', $user_id)->first();
        // dd($walletList);
        return json_encode($walletList);
    }
}
// 'created_at' => Carbon::now->toDateString()
