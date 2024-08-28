<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\SeriesProductRequest;
class SeriesProductController extends Controller
{
    
    function store(Request $request){
        try {

            // 開啟事務
            DB::beginTransaction();
            // $request->validate([
            //     'coverimg' => 'required|file|mimes:jpeg,png,jpg|max:16384', // 最大 16MB
            //     'imgs.*' => 'required|file|mimes:jpeg,png,jpg|max:16384', 
            //     'descimgs.*' => 'required|file|mimes:jpeg,png,jpg|max:16384', 
            // ]);
            

            $pdSeries = $request->input('pdSeries');
            $category = $request->input('category');
            $pdName = $request->input('pdName');
            $description1 = $request->input('description1');
            $description2 = $request->input('description2');
            $description3 = $request->input('description3');
            $description4 = $request->input('description4');
            $description5 = $request->input('description5');
            $coverimg = $request->file('coverimg');
            $imgs = $request->file('imgs');
            $descimgs = $request->file('descimgs');
            // 驗證輸入的值，並返回錯誤給前端
            $validateError = $this->inputValidation($pdSeries,$category,$pdName,$description1,$imgs);
            if ($validateError) {
                DB::rollBack();
                return response()->json(["error" => $validateError]);
            };
            // if(empty($pdSeries)){ 
            //     return response()->json(["error"=>"產品系列編號不可為空"]); 
            //     DB::rollBack();
            // }
            // else if(empty($category)){ 
            //     return response()->json(["error"=>"產品類別不可為空"]);
            //     DB::rollBack();
            //  }
            // else if(empty($pdName)){ 
            //     return response()->json(["error"=>"產品系列名稱不可為空"]);
            //     DB::rollBack();
            // }
            // if(empty($description1)){
            //     DB::rollBack();
            //     return response()->json(["error" => "至少輸入一條敘述"]);
            // }
            
            
           
            // if($imgs && count($imgs) > 8 ){
            //     return response()->json(["error"=>"其他圖片至少一張且不可以超過八張"]);
            //     DB::rollBack();
            // }
            // if(strlen($pdSeries) != 11){
            //     DB::rollBack();
            //     return response()->json(["error" => "產品系列編號為11碼"]);
            // }
            $n = DB::insert("INSERT INTO product_series(series_id,category_id,series_name,description1,description2,description3,description4,description5,create_date,update_date)
            VALUES(?,?,?,?,?,?,?,?,Now(),null)",[$pdSeries,$category,$pdName,$description1,$description2,$description3,$description4,$description5]);
            

        
            // $sql = DB::insert("INSERT INTO product_seriesimg(series_id,img,pic_category_id,created_at)
            //         VALUES(?,?,?,NOW())",[]);

            // 處理封面圖
            if ($coverimg && $coverimg->isValid() ){
                $fileContent = $coverimg->get();
                // Log::info("coverimg",["fileContent"=>$fileContent]);
                
                Log::info("封面圖片有效，檔案大小: " . strlen($fileContent));
                DB::insert("INSERT INTO product_seriesimg(series_id,img,pic_category_id,create_date)
                    VALUES(?,?,?,NOW())",[$pdSeries, $fileContent, 1]);
                // $stmt->execute([$pdSeries, $fileContent, 1]);
            }else{
                DB::rollBack();
                return response()->json(["error"=>"封面圖片上傳失敗"]);
            }


            // 處理其他圖片(8張)
            Log::info("imgs的if有效",["imgs"=>$imgs]);
            if ($imgs !==null && count($imgs) <= 8){
                Log::info("imgs的if有效");
                foreach ($imgs as $img) {
                    if ($img->isValid()) {
                        $fileContent = $img->get();
                        Log::info("其他圖片有效，檔案大小: " . strlen($fileContent));
                        DB::insert("INSERT INTO product_seriesimg(series_id,img,pic_category_id,create_date)
                                    VALUES(?,?,?,NOW())",[$pdSeries, $fileContent, 2]);
                    }else{
                        DB::rollBack();
                        return response()->json(["error"=>"其他圖片上傳失敗"]);
                    }
                }
            }else{
                return response()->json(["error"=>"其他圖片至少一張且不可以超過八張"]);
            }
            // 處理敘述圖片
            if ($descimgs !==null ){
                foreach ($descimgs as $descimg) {
                    if ($descimg->isValid()) {
                        $fileContent = $descimg->get();
                        Log::info("敘述圖片有效，檔案大小: " . strlen($fileContent));
                        DB::insert("INSERT INTO product_seriesimg(series_id,img,pic_category_id,create_date)
                                    VALUES(?,?,?,NOW())",[$pdSeries, $fileContent, 3]);
                    }else{
                        DB::rollBack();
                        return response()->json(["error"=>"敘述圖片上傳失敗"]);
                    }
                }
            }else{
                return response()->json(["error"=>"敘述圖片至少上傳一張"]);
            }
            // $this->coverimgValidation($coverimg,$pdSeries);
            // $this->otherimgsValidation($imgs,$pdSeries);
            // $this->descimgsValidation($descimgs,$pdSeries);
            DB::commit();
            return response()->json(["message" => "產品系列新增成功"]);
            // echo json_encode(["message" => "產品系列新增成功"]);
        }catch(\Exception $e) {
            Log::error($e->getMessage());
            DB::rollBack();
            return response()->json(["error" => "發生未知錯誤"]);
        }
    }

    function modify(Request $request){
        try {
            $pdSeries = $request->input('pdSeries');
            $category = $request->input('category');
            $pdName = $request->input('pdName');
            $description1 = $request->input('description1');
            $description2 = $request->input('description2');
            $description3 = $request->input('description3');
            $description4 = $request->input('description4');
            $description5 = $request->input('description5');
            $coverimg = $request->file('coverimg');
            $imgs = $request->file('imgs');
            $descimgs = $request->file('descimgs');
            // 驗證輸入的值，並返回錯誤給前端
            $validateError = $this->inputValidation($pdSeries,$category,$pdName,$description1,$imgs);
            if ($validateError) {
                DB::rollBack();
                return response()->json(["error" => $validateError]);
            };

            $n = DB::update("UPDATE product_series 
                    SET series_id = ? ,category_id = ?,series_name = ?,description1 = ?,description2 = ?,description3 = ?,description4 = ?,description5 = ?,update_date = Now()
                    WHERE series_id = ?",[$pdSeries,$category,$pdName,$description1,$description2,$description3,$description4,$description5,$pdSeries]);
            


            if(!empty($coverimg)){
                if ($coverimg->isValid() ){
                    $fileContent = $coverimg->get();
                    
                    Log::info("封面圖片有效，檔案大小: " . strlen($fileContent));
                    DB::update("UPDATE product_seriesimg 
                    SET series_id = ? ,img = ? ,update_date = Now()
                    WHERE series_id = ? AND pic_category_id = 1",[$pdSeries,$fileContent,$pdSeries]);
                   
                }else{
                    DB::rollBack();
                    return response()->json(["error"=>"封面圖片上傳失敗"]);
                }
            }
            
            if (!empty($imgs)){
                if(count($imgs) <= 8){

                
                    DB::table('product_seriesimg')
                    ->where('series_id',$pdSeries)
                    ->where('pic_category_id',2)
                    ->delete();
                    foreach ($imgs as $img) {
                        if ($img->isValid()) {
                            $fileContent = $img->get();
                            Log::info("其他圖片有效，檔案大小: " . strlen($fileContent));
                            DB::insert("INSERT INTO product_seriesimg(series_id,img,pic_category_id,create_date)
                                        VALUES(?,?,?,NOW())",[$pdSeries, $fileContent, 2]);
                        }else{
                            DB::rollBack();
                            return response()->json(["error"=>"其他圖片上傳失敗"]);
                        }
                    }
                }else{

                    return response()->json(["error"=>"不可以超過八張"]);
                }
                
            }
            
            if (!empty($descimgs)){
                DB::table('product_seriesimg')
                ->where('series_id',$pdSeries)
                ->where('pic_category_id',3)
                ->delete();
                foreach ($descimgs as $descimg) {
                    if ($descimg->isValid()) {
                        $fileContent = $descimg->get();
                        Log::info("敘述圖片有效，檔案大小: " . strlen($fileContent));
                        DB::insert("INSERT INTO product_seriesimg(series_id,img,pic_category_id,create_date)
                                    VALUES(?,?,?,NOW())",[$pdSeries, $fileContent, 3]);
                    }else{
                        DB::rollBack();
                        return response()->json(["error"=>"敘述圖片上傳失敗"]);
                    }
                }
            }

            Log::info("我是修改:$n " , ['$n'=>$n]);
            if($n > 0){
                Log::info("我有進n>0: ");

                DB::commit();
                return response()->json(["message" => "產品修改成功！"]);
            }else{
                DB::rollBack();
            }

        }catch(\Exception $e) {
            Log::error($e->getMessage());
            DB::rollBack();
            return response()->json(["error" => "發生未知錯誤"]);
        }

        
    }



    private function inputValidation($pdSeries,$category,$pdName,$description1,$imgs){
        if(empty($pdSeries)){ 
            return "產品系列編號不可為空"; 
        }else if(strlen($pdSeries) != 11){
            return "產品系列編號為11碼";
        }else if(empty($category) || $category == 'default'){ 
            return "產品類別不可為空";
        }else if(empty($pdName)){ 
            return "產品系列名稱不可為空";
        }else if(empty($description1)){
            return "至少輸入一條敘述";
        }
        
        return null;
    }
    private function coverimgValidation($coverimg,$pdSeries){
        if ($coverimg && $coverimg->isValid() ){
            $fileContent = $coverimg->get();
            Log::info("封面圖片有效，檔案大小: " . strlen($fileContent));
            DB::insert("INSERT INTO product_seriesimg(series_id,img,pic_category_id,create_date)
                VALUES(?,?,?,NOW())",[$pdSeries, $fileContent, 1]);
        }else{
            DB::rollBack();
            return response()->json(["error"=>"封面圖片上傳失敗"]);
        }
    }
    private function otherimgsValidation($imgs,$pdSeries){
        Log::info("imgs的if有效",["imgs"=>$imgs]);
        if ($imgs && count($imgs) <= 8){
            Log::info("imgs的if有效");
            foreach ($imgs as $img) {
                if ($img->isValid()) {
                    $fileContent = $img->get();
                    Log::info("其他圖片有效，檔案大小: " . strlen($fileContent));
                    DB::insert("INSERT INTO product_seriesimg(series_id,img,pic_category_id,create_date)
                                VALUES(?,?,?,NOW())",[$pdSeries, $fileContent, 2]);
                }else{
                    DB::rollBack();
                    return response()->json(["error"=>"其他圖片上傳失敗"]);
                }
            }
        }else{
            return response()->json(["error"=>"其他圖片至少一張且不可以超過八張"]);
        }
    }
    private function descimgsValidation($descimgs,$pdSeries){
        if ($descimgs){
            foreach ($descimgs as $descimg) {
                if ($descimg->isValid()) {
                    $fileContent = $descimg->get();
                    Log::info("敘述圖片有效，檔案大小: " . strlen($fileContent));

                    DB::insert("INSERT INTO product_seriesimg(series_id,img,pic_category_id,create_date)
                                VALUES(?,?,?,NOW())",[$pdSeries, $fileContent, 3]);
                }else{
                    DB::rollBack();
                    return response()->json(["error"=>"敘述圖片上傳失敗"]);
                }
            }
        }else{
            return response()->json(["error"=>"敘述圖片至少上傳一張"]);
        }
    }
}
