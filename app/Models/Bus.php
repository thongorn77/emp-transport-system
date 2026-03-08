<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bus extends Model
{
    protected $table = 'emp_buses';
    protected $primaryKey = 'bus_id';
    protected $fillable = ['plate_no', 'route_id', 'vender_id', 'line_user_id', 'default_type'];

    public function route() {
        return $this->belongsTo(BusRoute::class, 'route_id');
    }
}
