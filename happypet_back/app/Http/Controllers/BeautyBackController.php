<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use finfo; // 檢測文件的 MIME 類型

class BeautyBackController extends Controller
{
    function get_beauty_history_order_onepet($pid, $date) {
        $history_order = DB::select('select beauty_order.date ,beauty_plan.plan_title from beauty_order left join beauty_plan on beauty_order.planid = beauty_plan.planid where pid = ? and date < ? order by date desc', [$pid, $date]);
        return response()->json($history_order);
    }

    function get_beauty_order_oneweek($first_date, $last_date) {
        $oneweek_order = DB::select('select beauty_order.boid, beauty_order.pid , beauty_order.start_time, beauty_order.use_time, beauty_order.end_time, beauty_plan.plan_title, pet_info.pet_name,pet_info.pet_birthday,
        pet_info.others, user_info.cname, user_info.cellphone, pet_info.pet_headphoto
        from beauty_order 
        LEFT JOIN pet_info ON beauty_order.pid = pet_info.pid
        left join beauty_plan on beauty_order.planid = beauty_plan.planid
        left join user_info on pet_info.uid = user_info.uid where date between ? and ?', [$first_date, $last_date]);

        $result = collect($oneweek_order)->map(function($row) {
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
}
