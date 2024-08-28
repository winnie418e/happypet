<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PetInfo extends Model
{
    use HasFactory;

    protected $table = 'pet_info';
    protected $primaryKey = 'pid';
    public $incrementing = true;
     // 批量賦值
    protected $fillable = ['pet_name'];
   
}
