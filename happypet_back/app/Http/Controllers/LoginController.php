<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        try {
            $email = $request->input('email');
            $password = $request->input('password');

            // Retrieve the user from the database
            $user = DB::table('user_info')->where('email', $email)->first();

            if ($user && Hash::check($password, $user->password)) {
                // Password matches
                return response()->json([
                    'success' => true,
                    'message' => '登入成功！',
                    'uid' => $user->uid,
                ], 200);
            } else {
                // Password does not match or user does not exist
                return response()->json([
                    'success' => false,
                    'message' => '無效的電子信箱或密碼。',
                ], 401);
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(["error" => "註冊過程中發生錯誤"]);
        }

    }
}