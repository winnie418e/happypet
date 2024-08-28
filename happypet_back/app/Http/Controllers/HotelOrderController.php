<?php

namespace App\Http\Controllers;

use App\Models\HotelOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class HotelOrderController extends Controller
{
  

     // 日期訂單
     public function ordersByDate(Request $request)
     {
        // 確保 checkin 參數存在
        if ($request->has('checkin')) {
            $checkinDate = $request->input('checkin');
     
            $orders = DB::select("
                SELECT 
                    o.oid, 
                    u.cname AS user_name, 
                    p.pet_name, 
                    o.room_type, 
                    o.checkin, 
                    o.checkout
                FROM hotel_orders o
                LEFT JOIN user_info u ON o.uid = u.uid 
                LEFT JOIN pet_info p ON o.pid = p.pid
                WHERE ? BETWEEN o.checkin AND o.checkout;
            ", [$checkinDate]);
     
            return response()->json($orders);
        }
     
        // 如果沒有提供 checkin 參數，返回空的 JSON
        return response()->json([]);
    }

    // 找當天訂單-後台
    public function allOrders(Request $request)
    {
       
    $orders = HotelOrder::with(['pet', 'user'])->get();
    
           $orders = DB::select("
               SELECT 
                   o.oid, 
                   u.cname AS user_name, 
                   p.pet_name, 
                   o.room_type, 
                   o.checkin, 
                   o.checkout
               FROM hotel_orders o
               LEFT JOIN user_info u ON o.uid = u.uid 
               LEFT JOIN pet_info p ON o.pid = p.pid
           ");
    
           return response()->json($orders);
    
       // 如果沒有提供 checkin 參數，返回空的 JSON
       return response()->json([]);
   }
   // 找當天訂單-前端
   public function allOrdersFont(Request $request)
   {
     
   $orders = HotelOrder::with(['pet', 'user'])->get();
   
          return response()->json($orders);
   
      // 如果沒有提供 checkin 參數，返回空的 JSON
      return response()->json([]);
  }
 
    
    // 刪除訂單
    public function destroy($uid)
    {
        // 查找訂單
        $order = HotelOrder::where('uid', $uid)->first();
    
        if ($order) {
            $order->delete();
            return response()->json(['message' => '訂單刪除成功']);
        } else {
            return response()->json(['message' => '訂單刪除失敗'], 404);
        }
    }
    
    // 找空房

    public function checkAvailability(Request $request)
    {

        $checkInDate = $request->query('checkin'); // 使用 query() 方法讀取 GET 請求參數
        $checkOutDate = $request->query('checkout'); // 使用 query() 方法讀取 GET 請求參數
        $totalRoomsPerType = 3;  // 每個房型的總房間數
    
        // 定義所有房型 ,array_merge合併為一個陣列
        $roomTypes = ['深景房', '奢華房', '總統房', '喵皇房'];
    
        $results = DB::select("
        SELECT r.room_type, 
            ($totalRoomsPerType - COALESCE(h.spare_rooms, 0)) AS available_rooms 
        FROM (
            SELECT ? AS room_type
            UNION ALL
            SELECT ? AS room_type
            UNION ALL
            SELECT ? AS room_type
            UNION ALL
            SELECT ? AS room_type
        ) AS r
        LEFT JOIN (
            SELECT 
                room_type, COUNT(*) AS spare_rooms
            FROM hotel_orders 
            WHERE room_type IN ('深景房', '奢華房', '總統房', '喵皇房')
            AND ((? < checkout AND ? >= checkin)
                OR
                (? >= checkin AND ? < checkout))
            GROUP BY room_type
        ) AS h
        ON r.room_type = h.room_type
        WHERE ($totalRoomsPerType - COALESCE(h.spare_rooms, 0)) > 0;
        ", array_merge($roomTypes, [$checkInDate, $checkOutDate, $checkInDate, $checkInDate]));
    
        // 返回 JSON 格式的結果
        return response()->json([
            'room_availability' => $results
        ]);
    }


   // 查詢訂購人
   public function ordersByUser(Request $request)
   {
       // 確保 'name' 參數存在
       if ($request->has('name')) {
           $userName = $request->input('name');
   
           $orders = DB::select("
               SELECT 
                   o.oid, 
                   u.cname AS user_name, 
                   p.pet_name, 
                   o.room_type, 
                   o.checkin, 
                   o.checkout
               FROM 
                   hotel_orders o
               LEFT JOIN 
                   user_info u ON o.uid = u.uid
               LEFT JOIN 
                   pet_info p ON o.pid = p.pid
               WHERE 
                   u.cname LIKE ?", ['%' . $userName . '%']
           );
   
           // 返回 JSON 格式的訂單數據
           return response()->json($orders);
       }
       
       // 如果沒有提供 'name' 參數，返回空的 JSON
       return response()->json([]);
   }

   public function userPetName(Request $request)
{
        // 假設要查詢 UID 為 3 的寵物名稱
        $uid = 1;

        // 從請求中獲取 'uid' 參數->會員登入後再用
        // $uid = $request->input('uid');

        // 使用 DB::select 查詢
        $petNames = DB::select("
        SELECT 
            p.pet_name,
            p.pid,
            p.pet_weight,
            p.pet_species
        FROM 
            pet_info p
        WHERE 
            p.uid = ?", [$uid]
        );

        // 返回查詢結果作為 JSON
        return response()->json($petNames);
}

// 傳訂單 & 自動傳房間      
    public function store(Request $request)
    {
        $data = $request->input('hotelorders'); // 檢查是否正確接收到 `hotelorders`
        $data2 = $request->input('petIds'); // 檢查是否正確接收到 `petIds`

        if (is_array($data) && !empty($data)) {
            foreach ($data as $order) {
                Log::info('訂單詳細資訊', ['order' => $order]);

                // 查詢已訂房間號碼
                $room_number_result = DB::select('SELECT room_number FROM hotel_orders
                    WHERE room_type = ?
                    AND ((? < checkin AND ? >= checkin)
                    OR (? >= checkin AND ? < checkout))', [
                    $order['hotelName'],
                    $order['checkinDisplay'],
                    $order['checkoutDisplay'],
                    $order['checkinDisplay'],
                    $order['checkinDisplay']
                ]);

                // 將查詢結果轉換為純數值陣列
                $room_number = array_column($room_number_result, 'room_number');

                // 設置房間地圖
                if ($order['hotelName'] == '深景房') {
                    $roomMap = [101, 102, 103];
                } elseif ($order['hotelName'] == '奢華房') {
                    $roomMap = [201, 202, 203];
                } elseif ($order['hotelName'] == '總統房') {
                    $roomMap = [301, 302, 303];
                } elseif ($order['hotelName'] == '喵皇房') {
                    $roomMap = [401, 402, 403];
                }

                // 計算可用房間號碼
                $order_room_number = array_diff($roomMap, $room_number);

                // 取得第一個可用房間號碼
                $first_available_room_number = reset($order_room_number);

                if ($first_available_room_number === false) {
                    Log::error('找不到可用房間', ['order' => $order]);
                    continue; // 如果沒有可用房間，跳過當前訂單
                }

                // 儲存訂單
                HotelOrder::create([
                    'uid' => 'apple',
                    'uid' => '1',
                    'pid' => $data2, // 寵物 ID
                    'room_type' => $order['hotelName'],
                    'checkin' => $order['checkinDisplay'],
                    'checkout' => $order['checkoutDisplay'],
                    'nightday' => $order['nightdayDisplay'],
                    'roomquantity' => $order['hotelCartQuantity'],
                    'sameroom' => $order['sameRoomCount'],
                    'room_total' => $order['roomTotal'],
                    'sameroomNightday' => $order['sameroomNightday'],
                    'nid' => $request->input('nid'),
                    'room_number' => $first_available_room_number
                ]);

                Log::info('訂單已儲存', ['order' => $order]);
            }
            return response()->json(['message' => '訂單已成功儲存'], 200);
        } else {
            Log::error('無效的訂單資料', ['data' => $data]);
            return response()->json(['message' => '無效的訂單資料'], 400);
        }
    }

    // 寵物名稱顯示在表格
        public function chooseRoomNumber(Request $request)
    {
        if ($request->has('date')) {
            $selectedDate = $request->input('date');
            
            // 查詢包含所有需要的字段
            $orders = DB::select("
            SELECT 
                ho.room_number,
                pi.pid,
                pi.pet_name
            FROM 
                hotel_orders ho
            JOIN 
                pet_info pi ON FIND_IN_SET(pi.pid, ho.pid) > 0
            WHERE 
                ho.checkin <= ? AND ho.checkout >= ?
            ", [$selectedDate, $selectedDate]);

            Log::info('Detailed SQL Query Results: ', ['orders' => $orders]);

            return response()->json($orders);
        }
        
        return response()->json([]);
    }

    // 抓訂單號
    public function getOrderNumberByRoomNumber(Request $request)
{
    $roomNumber = $request->query('room_number');
    
    // 查詢資料庫根據 room_number 獲取對應的訂單編號
    $order = DB::table('hotel_orders')->where('room_number', $roomNumber)->first();
    
    if ($order) {
        return response()->json(['order_number' => $order->oid]);
    } else {
        return response()->json(['message' => 'Order not found'], 404);
    }
}


    // 抓user_info
    public function getUidByRoomNumber(Request $request)
    {
        $roomNumber = $request->query('room_number');
        
        // 查詢訂單信息以獲取 uid
        $order = DB::table('hotel_orders')
                ->where('room_number', $roomNumber)
                ->select('uid')
                ->first();
        
        if ($order) {
            return response()->json($order);
        } else {
            return response()->json(['message' => 'Order not found'], 404);
        }
    }

    public function getUserDetailsByUid(Request $request)
    {
        $uid = $request->query('uid');
        
        // 查詢用戶信息
        $user = DB::table('user_info')
                ->where('uid', $uid)
                ->select('cname', 'cellphone')
                ->first();
        
        if ($user) {
            return response()->json([
                'cname' => $user->cname,
                'cellphone' => $user->cellphone
            ]);
        } else {
            return response()->json(['message' => 'User not found'], 404);
        }
    }

    // 抓寵物資料
    public function getPetIdByRoomNumber(Request $request)
    {
        $roomNumber = $request->query('room_number');
        
        // 查詢訂單以獲取 pet_id
        $order = DB::table('hotel_orders')
                   ->where('room_number', $roomNumber)
                   ->select('pid as pet_id')
                   ->first();
        
        if ($order) {
            return response()->json(['pet_id' => $order->pet_id]);
        } else {
            return response()->json(['message' => 'Pet ID not found'], 404);
        }
    }
    
    // 抓房間資訊
    
        public function getPetDetailsById(Request $request)
    {
        $petId = $request->query('pet_id');
        
        // 查詢寵物信息
        $pet = DB::table('pet_info')
                ->where('pid', $petId)
                ->select('pet_name', 'pet_species', 'pet_weight', 'pet_variety', 'pet_gender', 'pet_birthday')
                ->first();
        
        if ($pet) {
            return response()->json($pet);
        } else {
            return response()->json(['message' => 'Pet not found'], 404);
        }
    }

    // 抓訂單
        public function getOrderDetailsByRoomNumber(Request $request)
    {
        $roomNumber = $request->query('room_number');
        
        // 查詢訂單詳細信息
        $order = DB::table('hotel_orders')
                ->where('room_number', $roomNumber)
                ->select('room_number', 'room_total', 'checkin', 'checkout', 'sameroom', 'nightday')
                ->first();
        
        if ($order) {
            return response()->json($order);
        } else {
            return response()->json(['message' => 'Order details not found'], 404);
        }
    }

}

        
    




    


    

