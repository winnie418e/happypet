<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HotelOrder extends Model
{
    use HasFactory;

    protected $table = 'hotel_orders';
    protected $primaryKey = 'nid'; // 更新為 `nid`，因為它是自動遞增主鍵
    public $incrementing = true; // `nid` 是自動遞增的欄位
    protected $keyType = 'int'; // `nid` 是整數型別

    // 禁用時間戳功能
    // public $timestamps = false;

    // 定義可批量填充的欄位
    protected $fillable = [
        'uid',
        'pid',
        'oid',
        'room_type',
        'checkin',
        'checkout',
        'nightday',
        'roomquantity',
        'sameroom',
        'room_total',
        'order_remark',
        'sameroomNightday',
        'nid',
        'room_number', 
    ];

    // 定義與 PetInfo 模型的關聯
    public function pet()
    {
        return $this->belongsTo(PetInfo::class, 'pid', 'pid');
    }

    // 定義與 UserInfo 模型的關聯
    public function user()
    {
        return $this->belongsTo(UserInfo::class, 'uid', 'uid');
    }
}
