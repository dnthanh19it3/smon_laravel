<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TransactionController extends Controller
{

    function addTransaction(Request $request, $uid, $transaction_type){
        $add = DB::table('change')->insert([
            'account_id' => $request->account_id,
            'type' => $request->type,
            'ammount' => $request->ammount,
            'content' => $request->content,
            'time' => $request->time,
            'location' => $request->location,
            'with' => $request->with   
        ]);
        if($add){
            $transaction_type = $request->transaction_type;
            $change_ammount = 0;
            $response= null;
            $balance = DB::table('accounts')->where('id', $request->account_id)->first()->balance;
            
            switch($transaction_type){
                case 0:
                    $change_ammount = DB::table('accounts')->where('id', $request->account_id)->update(['balance' => $balance - $request->ammount]);
                    break;
                case 1:
                    $change_ammount = DB::table('accounts')->where('id', $request->account_id)->update(['balance' => $balance + $request->ammount]);
                    break;
            }
            if($change_ammount){
                $response = [
                    'code' => 1,
                    'message' => 'Thêm giao dịch thành công!'
                ];
                return $response;
            } else {
                $response = [
                    'code' => 0,
                    'message' => 'Thêm giao dịch thất bại!'
                ];
                return $response;
            }
            
        }
    }

    function updateTransaction(Request $request, $uid){
        
        $update = DB::table('change')->where('change_id', $request->transaction_id)->update([
            'account_id' => $request->account_id,
            'type' => $request->type,
            'ammount' => $request->ammount,
            'content' => $request->content,
            'time' => $request->time,
            'location' => $request->location,
            'with' => $request->with   
        ]);

        if($update){
            $transaction_type = $request->transaction_type;
            $previous_ammount = $request->previous_ammount;

            $change_ammount = 0;
            $response = null;
            $balance = DB::table('accounts')->where('id', $request->account_id)->first()->balance;
            

            if(($transaction_type == 0) && ($previous_ammount > $request->ammount)){
                $change_ammount = DB::table('accounts')->where('id', $request->account_id)->update(['balance' => $balance + ($previous_ammount - $request->ammount)]);
                print_r("case1");
            } elseif (($transaction_type == 0) && ($previous_ammount < $request->ammount)){
                $change_ammount = DB::table('accounts')->where('id', $request->account_id)->update(['balance' => $balance - ($request->ammount - $previous_ammount)]);
                print_r("case2");
            }elseif(($transaction_type == 1) && ($previous_ammount > $request->ammount)){
                $change_ammount = DB::table('accounts')->where('id', $request->account_id)->update(['balance' => $balance - ($previous_ammount - $request->ammount)]);
                print_r("case3");
            } elseif (($transaction_type == 1) && ($previous_ammount < $request->ammount)){
                $change_ammount = DB::table('accounts')->where('id', $request->account_id)->update(['balance' => $balance + ($request->ammount - $previous_ammount)]);
                print_r("case4");
            } else {
                $response = [
                    'code' => 1,
                    'message' => 'Sửa giao dịch thành công!'
                ];
                return $response;
            }

            
            if($change_ammount){
                $response = [
                    'code' => 1,
                    'message' => 'Sửa giao dịch thành công!'
                ];
                return $response;
            } else {
                $response = [
                    'code' => 0,
                    'message' => 'Sửa giao dịch thất bại!'
                ];
                return $response;
            }
            
        } else {
            $response = [
                'code' => 1,
                'message' => 'Không có thay đổi!'
            ];
            return $response;
        }
    }



    function getAll(Request $request, $uid, $account_id){
        $months = DB::table('change')->select(DB::raw('DISTINCT MONTH(time) month'))->where('account_id', $account_id)->orderByRaw('MONTH(time) ASC')->get();
        foreach($months as $month){
            $sum_incom =  DB::table('change')->join('change_type', 'change.type', '=', 'change_type.id')->where('method', 1)->where('account_id', $account_id)->whereRaw('MONTH(time) ='. $month->month)->sum('ammount');
            $month->incom = $sum_incom;
            $sum_outgo =  DB::table('change')->join('change_type', 'change.type', '=', 'change_type.id')->where('method', 0)->where('account_id', $account_id)->whereRaw('MONTH(time) ='. $month->month)->sum('ammount');
            $month->outgo = $sum_outgo;
        }
        return ($months);
    }


    function getByMonth(request $request, $uid, $account_id, $month){
        $dates = DB::table('change')->select(DB::raw('DISTINCT time'))->where('account_id', $account_id)->whereRaw('MONTH(time) ='. $month)->orderByRaw('DAY(time) ASC')->get();
        foreach($dates as $date){
            // dd(DB::table('change')->join('change_type', 'change.type', '=', 'change_type.id')->where('account_id', $account_id)->whereRaw('MONTH(time) ='. $month)->whereRaw('DATE(time) ='. $date->day)->toSql());
            $date->diffence = DB::table('change')->join('change_type', 'change.type', '=', 'change_type.id')->where('account_id', $account_id)->where('time', $date->time)->where('method', 1)->sum('ammount') - DB::table('change')->join('change_type', 'change.type', '=', 'change_type.id')->where('account_id', $account_id)->where('time', $date->time)->where('method', 0)->sum('ammount');
            $date->data = DB::table('change')->join('change_type', 'change.type', '=', 'change_type.id')->where('account_id', $account_id)->where('time', $date->time)->get();
        }
        return $dates;
    }
    function monthlyReport(request $request, $uid, $account_id, $month){
        $sum_incom =  DB::table('change')->join('change_type', 'change.type', '=', 'change_type.id')->where('method', 1)->where('account_id', $account_id)->whereRaw('MONTH(time) ='. $month)->sum('ammount');
        $sum_outgo =  DB::table('change')->join('change_type', 'change.type', '=', 'change_type.id')->where('method', 0)->where('account_id', $account_id)->whereRaw('MONTH(time) ='. $month)->sum('ammount');
        $diffence = $sum_incom - $sum_outgo;
        $outgo_data = DB::table('change')->join('change_type', 'change.type', '=', 'change_type.id')->where('method', 0)->where('account_id', $account_id)->whereRaw('MONTH(time) ='. $month)->get(['name as x', 'ammount as value']);
        
        return ['incom' => $sum_incom, 'outgo' => $sum_outgo, 'diffence' => $diffence, 'outgo_data' => $outgo_data];
    }
}
