<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Http\Controllers\SeriesProductController; 
use App\Http\Controllers\DetailProductController; 
use App\Http\Controllers\HotelOrderController;
use App\Http\Controllers\BeautyFrontController; 
use App\Http\Controllers\BeautyBackController; 
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MyPetController;
use App\Http\Controllers\UserinfoController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
//                         N                              //
//                         N                              //
// main_info區 查詢產品系列號是否已有、類別下拉選單
Route::get('/product_back/info/select/{seriesID?}', function ($seriesID = null) {
    $seriesIDCount = DB::scalar("SELECT count(*) FROM product_series WHERE series_id = ?", [$seriesID]);
    $categories = DB::select('SELECT category_id,description FROM product_category');
    
    // Log::info('我是seriesIDCount',['seriesIDCount',$seriesIDCount]);
    // Cannot use object of type stdClass as array ，解決↓ (編碼在解碼為Array)
    $categories = json_decode(json_encode($categories), true);
    // 預設message為null
    $message = null;
    if ($seriesID !== null && $seriesIDCount > 0) {
        // return response()->json(["message" => "此產品系列編號已使用"]);
        $message = (["message" => "此產品系列編號已被使用"]);
        // echo json_encode($row['id']);
    }
    $categoryArr = [];
    foreach ($categories as $category) {
        $categoryArr[] = $category['category_id'] . "-" . $category['description'];
    }
    // print_r($categories);

    return response()->json([
        'message' => $message,
        'categories' => $categoryArr,
    ]);
});
Route::post('/product_back/info/update/{seriesID?}',function($seriesID = null){
    $seriesProduct = DB::select("SELECT * FROM  product_series ps 
                                JOIN product_seriesimg psi 
                                ON ps.series_id  = psi.series_id
                                WHERE ps.series_id = ?",[$seriesID]);
    foreach($seriesProduct as &$spdImg){
        // print_r($pd);
        if(isset($spdImg->img)){
            $mime_type = (new finfo(FILEINFO_MIME_TYPE))->buffer($spdImg->img);
            $spdImg->img = base64_encode($spdImg->img);
            $src = "data:{$mime_type};base64,{$spdImg->img}";
            $spdImg->img = $src;
        }
    };
    if(empty($seriesProduct)){
        return response()->json([
            'message'=>"查無此系列編號",
        ]);
    }else{
        Log::info('系列產品查詢結果',['seriesProduct'=>empty($seriesProduct)]);
        return response()->json([
            'seriesProduct'=>$seriesProduct,
        ]);
    }
});

// 產品主要資訊插入(系列產品)
// Route::post('/product_back/info/update',[SeriesProductController::class,'update']);

Route::prefix('/product_back/info')->group(function () {
    Route::post('/create',[SeriesProductController::class,'store'] );
    Route::post('/modify',[SeriesProductController::class,'modify']);
});

// 產品主要資訊插入(系列產品)
// Route::post('/product_back/info/create',[SeriesProductController::class,'store']);

// 產品詳細資訊：查詢系列編號
Route::post('/product_back/detail/show', function (Request $request) {
    $pdSeries = $request->input('pdSeries');
    Log::info('查詢產品系列ID:', ['pdSeries' => $pdSeries]); // 日誌查詢的ID
    $existPdSeries = DB::table('product_series')
        ->select('series_id', 'series_name')
        ->where('series_id', $pdSeries)
        ->first(); //// 使用 first() 取得單一結果
    if ($existPdSeries) {

        return response()->json($existPdSeries);
    } else {
        return response()->json(["error" => "查無此系列產品"]);
    }
});
// php artisan make:controller DetailProductInsertController
// Route::post('/product_back/detail/create',[DetailProductInsertController::class,'store']);
// Route::post('/product_back/detail/modify',[DetailProductInsertController::class,'modify']);

Route::prefix('/product_back/detail')->group(function () {
    Route::get('/select',[DetailProductController::class,'select'] );
    Route::post('/create',[DetailProductController::class,'store'] );
    Route::post('/modify',[DetailProductController::class,'modify']);
});

// 查詢種類(狗狗、貓貓專區 product.html)
Route::get('/product/{c}',function($c){
    // $products = DB::select('select * from seriespdimg_view');
    // $products = DB::select("SELECT * FROM seriespdimg_view WHERE product_id LIKE '{$c}%'");
    $products = DB::select("SELECT * FROM seriespdimg_view WHERE product_id LIKE ? ",["{$c}%"]);
    // json_decode($products);
    // print_r($products);
    
    foreach($products as &$pd){
        // print_r($pd);
        if(isset($pd->cover_img)){
            $mime_type = (new finfo(FILEINFO_MIME_TYPE))->buffer($pd->cover_img);
            $pd->cover_img = base64_encode($pd->cover_img);
            $src = "data:{$mime_type};base64,{$pd->cover_img}";
           $pd->cover_img = $src;
        }
    }

    // print_r($products);
    return response()->json($products);
    // return view('product1')->with('jsonString', json_encode($products, JSON_UNESCAPED_UNICODE));
});

// 查詢此產品資訊(product_item.html)
Route::get('/product/{c}/{seriesProduct}',function($c,$seriesProduct){
    // $products = DB::select("SELECT * FROM seriespdimg_view WHERE product_id like '{$c}%' and series_AINUM = '{$seriesProduct}'");
    $products = DB::select("SELECT * FROM seriespdimg_view WHERE product_id like ? and series_ai_id = ?", ["{$c}%", $seriesProduct]);
    $productImgs = DB::select("SELECT psi.*,spv.series_ai_id
                                FROM seriespdimg_view spv
                                JOIN product_series ps
                                ON spv.series_ai_id = ps.series_ai_id
                                JOIN product_seriesimg psi 
                                ON ps.series_id = psi.series_id 
                                WHERE spv.series_ai_id = ?
                                GROUP BY psi.id ,psi.series_id,psi.img,psi.pic_category_id ,psi.create_date, psi.update_date
                            ",[$seriesProduct]);
    // $category = DB::scalar("SELECT description FROM product_category WHERE id = '{$c}'");
    $category = DB::scalar("SELECT description FROM product_category WHERE category_id = ?",["{$c}"]);

    // print_r($products);
    foreach($products as &$pd){
        // print_r($pd);
        if(isset($pd->cover_img)){
            $mime_type = (new finfo(FILEINFO_MIME_TYPE))->buffer($pd->cover_img);
            $pd->cover_img = base64_encode($pd->cover_img);
            $src = "data:{$mime_type};base64,{$pd->cover_img}";
           $pd->cover_img = $src;
        }
    }
    foreach($productImgs as &$pdImg){
        // print_r($pd);
        if(isset($pdImg->img)){
            $mime_type = (new finfo(FILEINFO_MIME_TYPE))->buffer($pdImg->img);
            $pdImg->img = base64_encode($pdImg->img);
            $src = "data:{$mime_type};base64,{$pdImg->img}";
            $pdImg->img = $src;
        }
    }
    // return response()->json($products);
    return response()->json([
        'products' => $products,
        'productImgs' => $productImgs,
        'categoryName' => $category
    
    ]);
});

// 插入購物車
// Route::get('/product/insert/{poductID}/{quantity}',function($poductID,$quantity){
Route::get('/product/insert/{user}/{poductID}/{quantity}',function($user,$poductID,$quantity){
    Log::info('====>', ['productcart的user' => $user,'我是pdID'=>$poductID,'我是數量'=>$quantity]); 

    // 會傳回異動筆數
    if(!$user){
        echo '尚未登入';
    }
    // Log::info('我是登入user編號 ====>',$user);
    Log::info('我是登入user編號 :', ['user' => $user]); // 日誌查詢的ID

    if(!isset($poductID) || !isset($quantity)){
        echo '尚未選擇產品';
    }else{
        $pdCount = DB::scalar("SELECT count(*) FROM shopping_cart_item WHERE product_id = ?",[$poductID]);
        // echo 'pdCount'.json_encode($pdCount);
        // echo 'pdCount'.$pdCount;
        // echo $pdCount;
        if($pdCount >= 1){
            $n = DB::update("UPDATE shopping_cart_item 
                            SET quantity = quantity + ?
                            WHERE product_id = ?",[$quantity,$poductID]);

            echo $n;
        }else{
            // $n = DB::insert("insert into userinfo (uid,cname) values(?,?)",[$uid,$cname]);
            // uid寫死
            // $aaa = DB::select('SELECT count(uid) FROM shopping_cart_item WHERE uid = ?',["qwe123"]);
            // if($aaa >0){
                // }
            $ordernumber_old = DB::select('SELECT order_number FROM shopping_cart_item WHERE uid = ? limit 1',[$user]);
            Log::info('ordernumber_old :', ['ordernumber_old' => $ordernumber_old]); 
            //  {"ordernumber_old":[{"stdClass":{"order_number":"20240814001"}}]} 
            // Log::info('ordernumber_old :', ['ordernumber_old[0]->order_number' => $ordernumber_old[0]->order_number]); 

            if($ordernumber_old){
                $ordernumber_old = $ordernumber_old[0]->order_number;
                $n = DB::insert("INSERT INTO shopping_cart_item(order_number,uid,product_id,quantity,create_time)
                        VALUES(?,?,?,?,NOW())",[$ordernumber_old,$user,$poductID,$quantity]);   
                Log::info('====>', ['我是if的insert數量' => $n]); 
                 
            }else{
                DB::select("call giveOrderNumber(@current_order)");
                $callProcedure = DB::select('select @current_order');
                
                // Log::info('callProcedure:', $callProcedure);
                // Log::info('orderNumber_1 :', $callProcedure[0]->{'@current_order'});
                $orderNumber = $callProcedure[0]->{'@current_order'}; //取得orderNumber
                // Log::info('orderNumber_2 :', ['orderNumber' => $orderNumber]); 
                // Log::info('今天日期:', ['今天日期' => now()]); 

                $n = DB::insert("INSERT INTO shopping_cart_item(order_number,uid,product_id,quantity,create_time)
                            VALUES(?,?,?,?,NOW())",[$orderNumber,$user,$poductID,$quantity]);
                // echo "異動筆數".$n;
                Log::info('====>', ['我是else的insert數量' => $n]); 

            }

            echo $n;
        }
    }
});
// 查詢購物車
Route::get('/productcart/{user}',function($user){
    if(!isset($user)) return;
    
    $totalAmount = DB::scalar("SELECT COALESCE(SUM(quantity), 0) FROM shopping_cart_item WHERE uid = ?",[$user]);
    Log::info('====>', ['productcart的user' => $user,'我是數量'=>$totalAmount]); 
    echo $totalAmount;
});

// 產品入庫
Route::post('/product_back/warehouse',function(Request $request){
    $productID = $request->input('productID');
    $action = $request->input('action');
    
    if($action === 'fetch'){
        $products = DB::select("SELECT p.product_id,CONCAT_WS(' / ', nullif(ps.series_name,''),nullif(flavor,''),
                                    nullif(weight,''),nullif(size,''),nullif(style,'')) AS full_name
                                FROM product p
                                JOIN product_series ps 
                                ON p.series_ai_id = ps.series_ai_id
                                WHERE p.product_id = ? ",[$productID]);
        if(count($products) > 0){
            // foreach($products as $product){
            return response()->json($products[0]);
            // }
        }else{
            return response()->json(["error" => "查無此產品"]);
        }
    }else if($action === 'insert'){
        // $productID = $request->input('productID');
        $mfd = $request->input('mfd');
        $exp = $request->input('exp');
        $inventory = $request->input('inventory');
        $restockDate = $request->input('restockDate');
        // $data = $request->all();
        // DB::table('product_warehouse')->insert($data);
        try{
            DB::insert("INSERT INTO product_warehouse (product_id,inventory,mfd,exp,restock_date) 
                    VALUES(?,?,?,?,?)",[$productID,$inventory,$mfd,$exp,$restockDate]);
            return response()->json(["message" => "產品已入庫"]);
        }catch(\Exception $e){
            Log::error($e->getMessage());
            return response()->json(["error" => "產品入庫失敗"]);
    
        }
    }
    
});
// 搜尋
Route::post('/productall/select',function(Request $request){
    $nameKeyword = $request->input('nameKeyword'); //
    // Log::info('nameKeyword = ',['nameKeyword----->',$nameKeyword]);
    $results = DB::select("SELECT series_id ,ps.series_ai_id,ps.series_name ,spv.cover_img ,spv.price,ps.category_id
                            FROM seriespdimg_view spv join product_series ps 
                            on spv.series_ai_id = ps.series_ai_id 
                            WHERE ps.series_name LIKE ?
                            group by ps.series_id,ps.series_ai_id,spv.price,ps.series_name,ps.category_id;
                            ",["%{$nameKeyword}%"]);
    // 將封面圖轉base64
    foreach($results as &$result){
        if(isset($result->cover_img)){
            $mime_type = (new finfo(FILEINFO_MIME_TYPE))->buffer($result->cover_img);
            $result->cover_img = base64_encode($result->cover_img);
            $src = "data:{$mime_type};base64,{$result->cover_img}";
           $result->cover_img = $src;
        }
    }
    // Log::info(['result----->',$result]);
    return response()->json(["result" => $results]);
});
// all_product查詢
Route::get('/product_back/allproducts',function(Request $request){
    $productName = $request->query('productName');
    Log::info('查詢產品系列ID:', ['productName' => $productName]); // 日誌查詢的ID
    $all = DB::select(' SELECT * FROM all_product_view 
                    WHERE full_name LIKE ?',["%$productName%"]);
    return response()->json($all);
});
//                         N                              //



//////////////////////////////////// HUEI ////////////////////////////////////////


Route::get("/shelves", function (Request $request) {
    $status= $request->input('status');
    $shelves_products = DB::select("select * from VW_shelves where shelves_status=?",[$status]);
    return response(json_encode($shelves_products))
        ->header("content-type", "application/json")
        ->header("charset", "utf-8")
        ->header("Access-Control-Allow-Origin", "*")     
        ->header("Access-Control-Allow-Methods", "GET");
});

Route::put("/shelves/status_update/{product_id}", function (Request $request, $product_id) {
    $status = $request->input('status');
    $price = $request->input('price');
    $inventory = $request->input('inventory');
    $shelves_product_update = DB::update("update product set shelves_status =? , price = ? , update_date= now() where product_id =?", [$status, $price, $product_id]);
    $shelves_inventory_update = DB::update("update product_warehouse set inventory =? where product_id =?", [$inventory, $product_id]);
    if (($shelves_product_update + $shelves_inventory_update) > 0) {
        return response()->json(['message' => 'Product updated successfully']);
    } else {
        return response()->json(['message' => 'No product found or updated'], 404);
    }
});


Route::post("/orders_search", function (Request $request) {
    $status = $request->input('status');
    $searchOrdernumber = $request->input('searchOrdernumber');
    $phone = $request->input('phone');
    $sql = "select * FROM orders ";
    $orderby=" order by create_time desc";

    if ($status == 'all') {
        if (Str::length($searchOrdernumber)) {
            $sql = $sql . "where order_number like ?" .$orderby;
            $orders = DB::select($sql, [$searchOrdernumber]);
        } elseif (Str::length($phone)) {
            $sql = $sql . "where user_phone like ? " .$orderby;
            $orders = DB::select($sql, [$phone]);
        } else {
            $sql = $sql . $orderby;
            $orders = DB::select($sql);
        }
    } else {
        if (Str::length($searchOrdernumber)) {
            $sql = $sql . "where order_status=? and  order_number like ?" . $orderby;
            $orders = DB::select($sql, [$status, $searchOrdernumber]);
        } else {
            $sql = $sql . "where order_status=?" . $orderby;
            $orders = DB::select($sql, [$status]);
        }
    }

    return response(json_encode($orders))
        ->header("content-type", "application/json")
        ->header("charset", "utf-8")
        ->header("Access-Control-Allow-Origin", "*")
        ->header("Access-Control-Allow-Methods", "POST");
});

Route::post("/orders_detail_search", function (Request $request) {
    $order_number = $request->input('order_number');
    $sql = "select * FROM vw_orderdetail where order_number= ?";
    $order_details = DB::select($sql, [$order_number]);
    foreach ($order_details as &$order) {
        if (!empty($order->product_pic)) {
            $order->product_pic = base64_encode($order->product_pic);
        }
    }

    return response(json_encode($order_details))
        ->header("content-type", "application/json")
        ->header("charset", "utf-8")
        ->header("Access-Control-Allow-Origin", "*")
        ->header("Access-Control-Allow-Methods", "POST");
});




Route::put("/orders/note_update/{order_number}", function (Request $request, $order_number) {
    $note = $request->input('note');
    $note = $note ?? null;
    $order_status = $request->input('order_status');
    $note_update = DB::update("update orders set note =? , order_status=?  where order_number =?", [$note, $order_status, $order_number]);

    if ($note_update > 0) {
        return response()->json(['message' => 'Product updated successfully']);
    } else {
        return response()->json(['message' => 'No product found or updated'], 404);
    }
});


Route::post("/orderdetailwithuid", function (Request $request) {
    $uid = $request->input('uid');

    $sql = "select * FROM vw_orderdetail where uid= ? and buy_status='N'";

    $order_details = DB::select($sql, [$uid]);

    foreach ($order_details as &$order) {
        if (!empty($order->product_pic)) {
            $order->product_pic = base64_encode($order->product_pic);
        }
    }

    return response(json_encode($order_details))
        ->header("content-type", "application/json")
        ->header("charset", "utf-8")
        ->header("Access-Control-Allow-Origin", "*")
        ->header("Access-Control-Allow-Methods", "POST");
});


Route::delete("/orderdetail_delete", function (Request $request) {
    $uid = $request->input('uid');
    $pid = $request->input('product_id');

    $sql = "delete FROM shopping_cart_item where uid= ? and product_id =?";

    $delete_item = DB::delete($sql, [$uid, $pid]);

    if ($delete_item > 0) {
        return response()->json(['message' => 'Product updated successfully']);
    } else {
        return response()->json(['message' => 'No product found or updated'], 404);
    }
});


Route::post("/userinfoforbill", function (Request $request) {
    $uid = $request->input('uid');
    $sql = "select * FROM user_info where uid= ?";
    $userinfo = DB::select($sql, [$uid]);

    return response(json_encode($userinfo))
        ->header("content-type", "application/json")
        ->header("charset", "utf-8")
        ->header("Access-Control-Allow-Origin", "*")
        ->header("Access-Control-Allow-Methods", "POST");
});


Route::post("/orderinsert", function (Request $request) {
    $order_number = $request->input('order_number');
    $user_name = $request->input('user_name');
    $user_phone = $request->input('user_phone');
    $user_email = $request->input('user_email');
    $consignee_name = $request->input('consignee_name');
    $consignee_phone = $request->input('consignee_phone');
    $send = $request->input('send');
    $send_address = $request->input('send_address');
    $invoice = $request->input('invoice');
    $pay = $request->input('pay');
    $total = $request->input('total');

    $sql = "insert INTO orders (order_number, user_name, user_phone, user_email, consignee_name, consignee_phone, send, send_address, invoice, pay, total, order_status, create_time, note)
            VALUES( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, '2', now(), NULL);";

    $orders = DB::insert($sql, [$order_number, $user_name, $user_phone, $user_email, $consignee_name, $consignee_phone, $send, $send_address, $invoice, $pay, $total]);

    if ($orders > 0) {
        return response()->json($orders);
    } else {
        return response()->json($orders, 404);
    }
});


Route::post("/productqupdate", function (Request $request) {
    $nupdatedata = $request->input('nupdatedata');

    foreach ($nupdatedata as $product) {
        if ($product['product_id']) {
            $sql = "update shopping_cart_item 
            SET  quantity=?
            WHERE product_id=? and order_number =? ;";
            $pupdate = DB::update($sql, [$product['quantity'], $product['product_id'], $product['order_number']]);
        }

        // DB::table('shopping_cart_item')->where('product_id', 1)->update(['quantity' => $product['quantity']]);
    }
    if ($pupdate > 0) {
        return response()->json(['message' => 'Product updated successfully']);
    } else {
        return response()->json(['message' => 'No product found or updated'], 404);
    }
});
//////////////////////////////////// HUEI ////////////////////////////////////////

//////////////////////////////////// LIN ////////////////////////////////////////

Route::get("/beauty_front2_get_schedule_fortime/{date}", function($date) {
    $result = DB::table("beauty_order")
                ->where("date", $date)
                ->get();
    return response()->json($result);
});

Route::post('/beauty_front2_insert_order', function (Request $request) {
    // 取得請求中的資料
    $data = $request->all();

    // 插入資料到 beauty_order 表
    DB::table('beauty_order')->insert([
        'uid'        => $data['uid'],
        'pid'        => $data['pid'],
        'planid'     => $data['planid'],
        'branch'     => $data['branch'],
        'date'       => $data['date'],
        'start_time' => $data['start_time'],
        'use_time'   => $data['use_time'],
        'end_time'   => $data['end_time'],
        'price'      => $data['price'],
    ]);

    return response()->json(['message' => 'Data inserted successfully']);
});

Route::get('/back_beauty_get_history_order_onepet/{pid}/{date}',  [BeautyBackController::class, 'get_beauty_history_order_onepet']);

Route::get('back_beauty_get_order_oneweek/{first_date}/{last_date}', [BeautyBackController::class, 'get_beauty_order_oneweek']);

Route::get('/front_beauty_plan_info', [BeautyFrontController::class, 'front_beauty_plan_info']);

Route::post('/front_beauty_plan_price_time', [BeautyFrontController::class, 'front_beauty_plan_price_time']);

Route::get('/front_beauty_pet_info/{uid}', [BeautyFrontController::class, 'front_beauty_pet_info']);

Route::get('/front2_beauty_select_beauty_plan_price_time/{pet_species}/{pet_weight}/{pet_fur}/{planid}', [BeautyFrontController::class, 'front2_beauty_select_beauty_plan_price_time']);

//////////////////////////////////// LIN ////////////////////////////////////////

/////////////////////////////////////LEE/////////////////////////////////////////
//會員註冊
Route::post('/member_register', RegisterController::class);

//會員登入
Route::post('/member_login', [LoginController::class, 'login']);

//會員新增寵物
Route::post('/member_add_pet', [MyPetController::class, 'add_pet']);

//查看我的寵物資料
Route::post('/member_mypet', [MyPetController::class, 'mypet_card']);

//編輯我的寵物資料
Route::post('/member_edit_pet', [MyPetController::class, 'edit_petinfo']);


//查看會員資料
Route::post('/member_userinfo', [UserinfoController::class, 'show_userinfo']);


//編輯會員資料
Route::post('/member_userinfo_update', [UserinfoController::class, 'edit_userinfo']);
/////////////////////////////////////LEE/////////////////////////////////////////


/////////////////////////////////////chen//////////////////////////////////////


// 查日期
// 顯示訂單列表
// http://localhost/happypet_back/public/api/hotel_orders
Route::get('/hotel_orders_day', [HotelOrderController::class, 'ordersByDate']);

// 顯示房型跟數量
Route::get('/check-availability', [HotelOrderController::class, 'checkAvailability']);

// 訂單進資料庫
Route::post('/hotel_orders', [HotelOrderController::class, 'store']);

// 排房間
Route::post('/assign_rooms', [HotelOrderController::class, 'assignRoomNumber']);

// 後台選日期
Route::get('/chooseRoomNumber', [HotelOrderController::class, 'chooseRoomNumber']);

// 抓model訂單號
Route::get('/getOrderNumberByRoomNumber', [HotelOrderController::class, 'getOrderNumberByRoomNumber']);

// 抓user_info
Route::get('/getUidByRoomNumber', [HotelOrderController::class, 'getUidByRoomNumber']);
Route::get('/getUserDetailsByUid', [HotelOrderController::class, 'getUserDetailsByUid']);

// 抓寵物資料
Route::get('/getPetIdByRoomNumber', [HotelOrderController::class, 'getPetIdByRoomNumber']);
Route::get('/getPetDetailsById', [HotelOrderController::class, 'getPetDetailsById']);

// 抓訂單明細
Route::get('/getOrderDetailsByRoomNumber', [HotelOrderController::class, 'getOrderDetailsByRoomNumber']);


// 查全部-後台
Route::get('/hotel_orders_all', [HotelOrderController::class, 'allOrders']);


// 查詢訂購人
Route::get('/hotel_orders_by_user', [HotelOrderController::class, 'ordersByUser']);

// 選擇該使用者的寵物
Route::get('/hotel_user_pets', [HotelOrderController::class, 'userPetName']);

/////////////////////////////////////chen//////////////////////////////////////
