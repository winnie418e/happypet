<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RegisterController extends Controller
{
    public function __invoke(Request $request)
    {
        try {
            // 取得請求的資料
            $cname = $request->input('cname');
            $myemail = $request->input('myemail');
            $mypassword = bcrypt($request->input('mypassword'));
            $mypassword_conf = $request->input('mypassword_conf');
            $sex = $request->input('sex');
            $cellphone = $request->input('cellphone');

            // 檢查電子郵件是否已經存在
            $exists = DB::table('user_info')->where('email', $myemail)->exists();

            if ($exists) {
                // 如果電子郵件已經存在，返回錯誤消息
                return response()->json(["error" => "信箱已經被註冊過"]);
            }

            // 插入新用戶資料
            $sign_in = DB::insert(
                'insert into user_info (cname, email, password, sex, cellphone) values (?, ?, ?, ?, ?)',
                [$cname, $myemail, $mypassword, $sex, $cellphone]
            );

            if ($sign_in) {
                // 如果插入成功，返回成功消息
                return response()->json(["message" => "註冊成功，即將跳轉至首頁"]);
            } else {
                // 如果插入失敗，返回錯誤消息
                return response()->json(["error" => "註冊失敗"]);
            }
        } catch (\Exception $e) {
            // 捕獲異常，返回錯誤消息
            Log::error($e->getMessage());
            return response()->json(["error" => "註冊過程中發生錯誤"]);
        }
    }

}
