<?php

namespace App\Http\Controllers;
use DB;
use Illuminate\Http\Request;

class ChangeController extends Controller
{
    function getChangeType(Request $request, $type){
        $stmt = DB::table('change_type')->where('method', $type)->get();
        return $stmt;
    }
}
