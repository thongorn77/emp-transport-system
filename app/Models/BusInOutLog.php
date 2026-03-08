<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusInOutLog extends Model
{
    protected $table = 'Bus_In_Out_Log';
    protected $primaryKey = 'ID';
    // ปิด timestamps เพราะตารางเดิมอาจไม่มี created_at/updated_at แบบ laravel
    public $timestamps = false; 

    protected $fillable = [
        'bus_id', 'route_id', 'log_date_time', 'actual_bus_type', 
        'applied_price', 'passenger_count', 'factory_id', 'shift', 'token'
    ];
}