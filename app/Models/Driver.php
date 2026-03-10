<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    protected $table      = 'emp_drivers';
    protected $primaryKey = 'id';

    protected $fillable = [
        'line_user_id',
        'line_display_name',
        'driver_name',
        'phone',
        'is_approved',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
    ];

    /** รถที่คนขับคนนี้ลงทะเบียนไว้ */
    public function buses()
    {
        return $this->belongsToMany(
            Bus::class,
            'emp_driver_buses',
            'line_user_id',
            'bus_id',
            'line_user_id',
            'bus_id'
        );
    }
}
