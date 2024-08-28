<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserInfo extends Model
{
    use HasFactory;

    protected $table = 'user_info';
    protected $primaryKey = 'uid';
    public $incrementing = true;
  // 批量賦值
    protected $fillable = ['cname'];
}
