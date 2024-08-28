<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use finfo; // 檢測文件的 MIME 類型


class BeautyFrontController extends Controller
{
    function front_beauty_plan_info() {
        $plan_info = DB::select('select * from beauty_plan');
        return response()->json($plan_info);
    }

    function front_beauty_plan_price_time(Request $request) {
        $pet_species = $request->input("pet_species");
        $pet_weight = $request->input("pet_weight");
        $pet_fur = $request->input("pet_fur");
        $weight_range;
        if($pet_species == '狗') {
            switch ($pet_weight) {
                case $pet_weight < 10 :
                    $weight_range = '0010';
                    break;
                case $pet_weight < 20 :
                    $weight_range = '1020';
                    break;
                case $pet_weight < 40 :
                    $weight_range = '2040';
                    break;
            }
        } else {
            $weight_range = '0000';
        }
        $result = DB::select('select * from beauty_plan_price_time where pet_species = ? and weight_range = ? and pet_fur = ?', [$pet_species, $weight_range, $pet_fur]);

        return response()->json($result);
    }

    function front_beauty_pet_info($uid) {
        $pet_info = DB::select("select pid, pet_name, pet_headphoto, pet_species, pet_weight, pet_fur from pet_info where uid = ?", [$uid]);
        $result = collect($pet_info)->map(function($row) {
            if ($row->pet_headphoto) {
                // 找副檔名
                $mime_type = (new finfo(FILEINFO_MIME_TYPE))->buffer($row->pet_headphoto);
                // 圖片檔轉字串
                $base64 = base64_encode($row->pet_headphoto);
                $row->pet_headphoto = "data:${mime_type};base64,${base64}";
            }
            return $row;
        });
        return response()->json($result);
    }

    function front2_beauty_select_beauty_plan_price_time($pet_species, $pet_weight, $pet_fur, $planid) {
        $weight_range;
        if($pet_species == '狗') {
            switch ($pet_weight) {
                case $pet_weight < 10 :
                    $weight_range = '0010';
                    break;
                case $pet_weight < 20 :
                    $weight_range = '1020';
                    break;
                case $pet_weight < 40 :
                    $weight_range = '2040';
                    break;
            }
        } else {
            $weight_range = '0000';
        }

        $sel_plan_price_time = DB::select('select * from beauty_plan_price_time where pet_species = ? and weight_range = ? and pet_fur = ? and planid = ?', [$pet_species, $weight_range, $pet_fur, $planid]);
        return response()->json($sel_plan_price_time);
    }
}
