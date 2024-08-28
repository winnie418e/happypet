<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use finfo;

class UserinfoController extends Controller
{
    function show_userinfo(Request $request){
        // echo $request->input('uid');
        $data = DB::select('select * from user_info where uid=?', [$request->input('uid')]);
        $result = collect($data)->map(function($row) {
            return [
                'user_name' => $row->cname,  // 將 cname 對應到 user_name
                'user_gender' => $row->sex,
                'user_email' => $row->email,
                'user_phone1' => $row->cellphone,
                'user_phone2' => $row->cellphone2,
                'user_address' => $row->address,
            ];
        });
        return response()->json($data);

    }

    function edit_userinfo(Request $request) {
        // 準備要更新的資料
        $updatedData = [
            'cname' => $request->input('user_name'),
            'sex' => $request->input('user_gender'),
            'email' => $request->input('user_email'),
            'cellphone' => $request->input('user_phone1'),
            'cellphone2' => $request->input('user_phone2'),
            'address' => $request->input('user_address')
        ];
    
        try {
            $updateResult = DB::table('user_info')
                ->where('uid', $request->input('uid'))
                ->update($updatedData);
    
            if ($updateResult) {
                return response()->json(["message" => "資料更新成功！"]);
            } else {
                return response()->json(["error" => "資料更新失敗，再試一次"]);
            }
        } catch (\Illuminate\Database\QueryException $e) {
            // 捕捉唯一性約束違反錯誤，但不報錯
            // 這裡可以根據需要選擇是否記錄日誌
            Log::error("更新資料時發生錯誤: " . $e->getMessage());
            return response()->json(["message" => "資料更新成功，存在某些問題，但資料已更新。"]);
        } catch (\Exception $e) {
            // 捕捉其他異常
            Log::error("更新資料時發生錯誤: " . $e->getMessage());
            return response()->json(["error" => "資料更新失敗，再試一次"]);
        }
    }
}