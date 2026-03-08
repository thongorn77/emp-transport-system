<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusRoute extends Model
{
    protected $table = 'emp_bus_routes';
    protected $primaryKey = 'route_id';
    protected $fillable = ['route_name', 'price_bus', 'price_van'];
}
