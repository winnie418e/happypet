<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class DetailProductController extends Controller
{
    function select(Request $request){
        $pdSeries = $request->query('pdSeries'); //?查詢字符串用query
        Log::info('pdSeries變數',['pdSeries',$pdSeries]);
        $product = DB::select("SELECT * FROM  product p 
                    JOIN product_series ps 
                    ON p.series_ai_id = ps.series_ai_id 
                    WHERE series_id = ?",[$pdSeries]);
        return response()->json(["product" => $product ]);

    }
    function store(Request $request){
        try {
            $pdSeries = $request->input('pdSeries');

            $fullPdIDs = $request->input('fullID',[]);
            $flavors = $request->input('flavor',[]);
            $weights = $request->input('weight',[]);
            $sizes = $request->input('size',[]);
            $colors = $request->input('color',[]);
            $prices = $request->input('price',[]);
            $GTINs = $request->input('GTIN',[]);

            Log::info('請求資料', [
                'fullPdIDs' => $fullPdIDs,
                'flavors' => $flavors,
                'weights' => $weights,
                'sizes' => $sizes,
                'colors' => $colors,
                'prices' => $prices,
                'GTINs' => $GTINs
            ]);
            Log::info('$request:', ["request = " => $request->all()]); 
            Log::info('全產品ID', ['fullPdIDs' => $fullPdIDs]);
            
            // 開啟事務
            DB::beginTransaction();
            
            $series_num = DB::table('product_series')
                ->select('series_ai_id')
                ->where('series_id',$pdSeries)
                ->first();
            Log::info('series_num:', ["series_num =" => $series_num]);
            Log::info('series_num:', ["series_num['series_ai_id'] =" => $series_num->series_ai_id]);
            
            
            // $n = DB::insert("INSERT INTO product(series_AINUM,id,flavor,weight,size,color,price,GTIN,created_at)
            // VALUES(?,?,?,?,?,?,?,?,date(NOW()))",[$pdSeries,$fullPdIDs,$flavors,$weights,$sizes,$colors,$prices,$GTINs]);
            // $insertedCount = 0;
            foreach($fullPdIDs as $index=>$fullPdID){
                Log::info('處理料號', ['fullPdID' => $fullPdID]);
                $validateError = $this->inputValidation($fullPdID,$prices,$GTINs,$index,$colors,$sizes,$flavors,$weights);
                if($validateError){
                    DB::rollBack();
                    return response()->json(["error" => $validateError]);
                }
                // start
                // if(strlen($fullPdID) != 13 ){
                //     // echo json_encode(["error" => "料號不是有效格式：種類兩碼+年份四碼+月份兩碼+系列編碼流水號三碼+料號流水號兩碼，如：ds20240700101"]);
                //     DB::rollBack();
                //     return response()->json(["error" => "料號不是有效格式：種類兩碼+年份四碼+月份兩碼+系列編碼流水號三碼+料號流水號兩碼，如：ds20240700101"]);
                //     // die();
                // }elseif(empty($prices[$index])){
                //     // echo json_encode(["error" => "價格不可以為空"]);
                //     DB::rollBack();
                //     return response()->json(["error" => "價格不可以為空"]);
                //     // die();
                // }elseif(empty($GTINs[$index])){
                //     // echo json_encode(["error" => "國際條碼不可以為空"]);
                //     DB::rollBack();
                //     return response()->json(["error" => "國際條碼不可以為空"]);
                //     // die();
                // }elseif(strlen($GTINs[$index]) != 13 && strlen($GTINs[$index]) != 8 && strlen($GTINs[$index]) != 14){
                //     // echo json_encode(["error" => "國際條碼分為GTIN-8、GTIN-13、GTIN-14"]);
                //     DB::rollBack();
                //     return response()->json(["error" => "國際條碼分為GTIN-8、GTIN-13、GTIN-14"]);
                //     // die();
                // }
                


                // // 找不到會返回false
                // $reg = "/df|dc|dt|dh|cf|cc|ct|ch/";
                // // print_r(preg_match_all($reg,$fullPdID,$match));
                // // preg_match_all() 函數用於在字串中搜尋符合的模式，傳回所有符合項目
    
                // // ???????
                // if( (strpos($fullPdID,'ds') !== false || strpos($fullPdID,'cs') !== false) &&  ( empty($colors[$index])  ||  empty($sizes[$index]) )  ){
                //     // echo json_encode(["error" => "用品類尺寸與款式其中一個不得為空"]);
                //     DB::rollBack();
                //     return response()->json(["error" => "用品類尺寸與款式不得為空"]);
                //     // die();
                // }elseif(preg_match_all($reg,$fullPdID,$match) && (empty($flavors[$index]) || empty($weights[$index]))){
                //     // echo json_encode(["error" => "食品類淨重與口味不得為空"]);
                //     DB::rollBack();
                //     return response()->json(["error" => "食品類淨重與口味不得為空"]);
                //     // die();
                // }

                // if ((strpos($fullPdID, 'ds') !== false || strpos($fullPdID, 'cs') !== false) && (empty($colors[$index]) || empty($sizes[$index]))) {
                //     throw new \Exception("用品類尺寸與款式其中一個不得為空");
                // } elseif (preg_match_all($reg, $fullPdID, $match) && (empty($flavors[$index]) || empty($weights[$index]))) {
                //     throw new \Exception("食品類淨重與口味不得為空");
                // }
                // // end

                // 回傳布林值
                $insertSuccess = DB::insert('INSERT INTO product(series_ai_id,product_id,flavor,weight,size,style,price,GTIN,create_date)
                        VALUES(?,?,?,?,?,?,?,?,NOW())',[
                            $series_num->series_ai_id,
                            $fullPdID,
                            $flavors[$index] ?? '',
                            $weights[$index] ?? '',
                            $sizes[$index] ?? '',
                            $colors[$index] ?? '',
                            $prices[$index] ?? '',
                            $GTINs[$index] ?? '',
                        ]);
                
                Log::info('異動筆數:', ["異動筆數" => $insertSuccess]); // 日誌查詢異動筆數
                
                // if($n > 0){
                //     DB::commit();
                //     return response()->json(["message" => "產品新增成功"]);
                // }
                // if ($n) {
                //     $insertedCount++;
                // } else {
                //     throw new \Exception("插入產品失敗");
                // }
            };
            
            if( $insertSuccess){
                DB::commit();
                return response()->json(["message" => "產品新增成功"]);

            }
            // Log::info('Insertion Result:', ["inserted_count" => $insertedCount]);

            // echo json_encode(["message" => "產品系列新增成功"]);
        }catch(\Exception $e) {
            Log::error($e->getMessage());
            Log::error($e->errorInfo[1]);
            if(isset($e->errorInfo[1]) && $e->errorInfo[1] == 1062){
                DB::rollBack();
                return response()->json(["error" => "此料號已被使用"]);
            }
            DB::rollBack();
            return response()->json(["error" => "發生錯誤" . $e->getMessage()]);
        };
    }    

    function modify(Request $request){
        try {
            $pdSeries = $request->input('pdSeries');
            $pdName = $request->input('pdName');
            Log::info('取得系列號:', ["-------------------->" => $pdSeries]);
            Log::info('取得系列名:', ["-------------------->" => $pdName]);
            $fullPdIDs = $request->input('fullID',[]);
            $flavors = $request->input('flavor',[]);
            $weights = $request->input('weight',[]);
            $sizes = $request->input('size',[]);
            $colors = $request->input('color',[]);
            $prices = $request->input('price',[]);
            $GTINs = $request->input('GTIN',[]);
            // 開啟事務
            DB::beginTransaction();
            $updatePdName = DB::update('UPDATE product_series SET series_name = ? WHERE series_id = ?',[$pdName,$pdSeries]);
            Log::info('修改pdname:', ["-------------------->" => $updatePdName]);

            foreach($fullPdIDs as $index=>$fullPdID){
                $validateError = $this->inputValidation($fullPdID,$prices,$GTINs,$index,$colors,$sizes,$flavors,$weights);
                if($validateError){
                    DB::rollBack();
                    return response()->json(["error" => $validateError]);
                }
                $updateSuccess = DB::update("UPDATE product
                                SET flavor = ?, weight = ?, size = ?,style = ? ,price = ?, GTIN = ?, update_date = Now()
                                WHERE product_id = ? ",[
                                        $flavors[$index] ?? '',
                                        $weights[$index] ?? '',
                                        $sizes[$index] ?? '',
                                        $colors[$index] ?? '',
                                        $prices[$index] ?? '',
                                        $GTINs[$index] ?? '',
                                        $fullPdID
                                    ]);
            }
            if($updateSuccess){
                DB::commit();
                Log::info('update Result:', ["update" => $updateSuccess]);

                return response()->json(["message" => "產品修改成功"]);
            }
        }catch(\Exception $e) {
            Log::error($e->getMessage());
            // Log::error($e->errorInfo[1]);
            // if(isset($e->errorInfo[1]) && $e->errorInfo[1] == 1062){
            //     DB::rollBack();
            //     return response()->json(["error" => "此料號已被使用"]);
            // }
            DB::rollBack();
            return response()->json(["error" => "發生錯誤" . $e->getMessage()]);
        };
    }
    private function inputValidation($fullPdID,$prices,$GTINs,$index,$colors,$sizes,$flavors,$weights){
        // if(empty($pdSeries)){ 
        //     return "產品系列編號不可為空"; 
        // }else if(strlen($pdSeries) != 11){
        //     return "產品系列編號為11碼";
        // }else if(empty($category)){ 
        //     return "產品類別不可為空";
        // }else if(empty($pdName)){ 
        //     return "產品系列名稱不可為空";
        // }else if(empty($description1)){
        //     return "至少輸入一條敘述";
        // }
        if(strlen($fullPdID) != 13 ){
            return "料號不是有效格式：種類兩碼+年份四碼+月份兩碼+系列編碼流水號三碼+料號流水號兩碼，如：ds20240700101";
        }elseif(empty($prices[$index])){
            return "價格不可以為空";
        }elseif(empty($GTINs[$index])){
            return "國際條碼不可以為空";
        }elseif(strlen($GTINs[$index]) != 13 && strlen($GTINs[$index]) != 8 && strlen($GTINs[$index]) != 14){
            return "國際條碼分為GTIN-8、GTIN-13、GTIN-14";

        }

        // 找不到會返回false
        $reg = "/df|dc|dt|dh|cf|cc|ct|ch/";
        // print_r(preg_match_all($reg,$fullPdID,$match));
        // preg_match_all() 函數用於在字串中搜尋符合的模式，傳回所有符合項目

        if( (strpos($fullPdID,'ds') !== false || strpos($fullPdID,'cs') !== false) &&  ( empty($colors[$index])  ||  empty($sizes[$index]) )  ){
            return "用品類尺寸與款式其中一個不得為空";
        }elseif(preg_match_all($reg,$fullPdID,$match) && (empty($flavors[$index]) || empty($weights[$index]))){
            return "食品類淨重與口味不得為空";
        }

        // if ((strpos($fullPdID, 'ds') !== false || strpos($fullPdID, 'cs') !== false) && (empty($colors[$index]) || empty($sizes[$index]))) {
        //     throw new \Exception("用品類尺寸與款式其中一個不得為空");
        // } elseif (preg_match_all($reg, $fullPdID, $match) && (empty($flavors[$index]) || empty($weights[$index]))) {
        //     throw new \Exception("食品類淨重與口味不得為空");
        // }
      


        return null;
    }
}
