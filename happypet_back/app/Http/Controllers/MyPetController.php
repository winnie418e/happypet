<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use finfo;

class MyPetController extends Controller
{
    public function add_pet(Request $request)
    {
        Log::info('收到的請求數據:', $request->all());
        
        try {
             // 確保 pet_headphoto 是一個上傳文件
            // $request->hasFile('pet_headphoto')
            $pet_headphoto = $request->file('pet_headphoto');

            if($request->file('pet_headphoto')) {

                $bindata = file_get_contents($pet_headphoto);

                // 儲存文件並獲取路徑
                // $pet_headphoto_path = $pet_headphoto->store('pet_photos', 'public');
                
            } else {
                // $pet_headphoto_path = null; // 如果沒有上傳圖片，可以設定為 null 或空值
                $bindata = null; // 如果沒有上傳圖片，可以設定為 null 或空值
            }

        // 插入數據到數據庫
            
            $add_pet = DB::table('pet_info')->insert([
                'uid' => $request->input('uid'),
                'pet_name' => $request->input('pet_name'),
                'pet_species' => $request->input('pet_species'),
                'pet_weight' => $request->input('pet_weight'),
                'pet_variety' => $request->input('pet_variety'),
                'pet_fur' => $request->input('pet_fur'),
                'pet_gender' => $request->input('pet_gender'),
                'pet_birthday' => $request->input('pet_birthday'),
                'neutered' => $request->input('neutered'),
                'pet_headphoto' => $bindata,  // 確保這裡是字串
                'others' => $request->input('others')
                ]);


            if ($add_pet) {
                // 如果插入成功，返回成功消息
                return response()->json(["message" => "新增成功！"]);
            } else {
                // 如果插入失敗，返回錯誤消息
                return response()->json(["error" => "新增失敗，再試一次"]);
            }
        } catch (\Exception $e) {
            // 捕獲異常，返回錯誤消息
            Log::error($e->getMessage());
            return response()->json(["error" => "新增寵物過程中發生錯誤(MyPetController,'add_pet')"]);
        }
    }

    function mypet_card(Request $request){
        // echo $request->input('uid');
        $data = DB::select('select * from pet_info where uid=?', [$request->input('uid')]);
        $result = collect($data)->map(function($row) {
            if($row->pet_headphoto) {
                $mimetype = (new finfo(FILEINFO_MIME_TYPE))->buffer($row->pet_headphoto);

                $base64 = base64_encode($row->pet_headphoto);
                $row->pet_headphoto = "data:${mimetype};base64,${base64}";
            }
            return $row;
        });
        return response()->json($result);

    }

    function edit_petinfo(Request $request){

        try {

        $pid=$request->input("pid");
        $pet_name=$request->input('pet_name');
        $pet_species=$request->input('pet_species');
        $pet_variety=$request->input('pet_variety');
        $pet_weight=$request->input('pet_weight');
        $pet_fur=$request->input('pet_fur');
        $pet_gender=$request->input('pet_gender');
        $pet_birthday=$request->input('pet_birthday');
        $neutered=$request->input('neutered');
        $others=$request->input('others');

        $pet_headphoto = $request->file('pet_headphoto');
        if ($pet_headphoto) {
            $bindata = file_get_contents($pet_headphoto);
        } else {
            // 沒有上傳新照片，從資料庫中查找舊的照片
            $currentPhoto = DB::table('pet_info')->where('pid', $pid)->value('pet_headphoto');
            if ($currentPhoto) {
                $bindata = $currentPhoto;
            } else {
                $bindata = null; // 如果資料庫中也沒有照片，設置為 NULL
            }
        }
        // if($request->file('pet_headphoto')) {

        //      $bindata = file_get_contents($pet_headphoto);

        // } else {

        //     $bindata = null; // 如果沒有上傳圖片，可以設定為 null 或空值
        // }


        // 更新資料庫
           $edit_pet = DB::table('pet_info')
           ->where('pid', $request->input('pid'))
           ->update([
               'pet_name' => $request->input('pet_name'),
               'pet_species' => $request->input('pet_species'),
               'pet_weight' => $request->input('pet_weight'),
               'pet_variety' => $request->input('pet_variety'),
               'pet_fur' => $request->input('pet_fur'),
               'pet_gender' => $request->input('pet_gender'),
               'pet_birthday' => $request->input('pet_birthday'),
               'neutered' => $request->input('neutered'),
               'pet_headphoto' => $bindata,
               'others' => $request->input('others')
               ]);

            //    return response()->json(['message'=>'OK']);
           if ($edit_pet) {
               // 如果更新成功，返回成功消息
               return response()->json(["message" => "新資料儲存成功！"]);
           } else {
               // 如果都沒有資料異動或異動失敗，就回報這個訊息
               // 文字回報這樣給使用者，體驗會比較好 
               return response()->json(["error" => "資料已儲存"]);
           }
       } catch (\Exception $e) {
           // 捕獲異常，返回錯誤消息
           Log::error($e->getMessage());
           return response()->json(["error" => "更新寵物資料過程中發生錯誤(MyPetController,'edit_petinfo')"]);
       }
    }
    
}
