<?php

namespace App\Http\Controllers;

use App\Models\BusInOutLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function index()
    {
        // ดึงข้อมูลโดย Join กับตาราง Buses และ Routes
        $reports = DB::table('Bus_In_Out_Log as log')
            ->join('emp_buses as bus', 'log.bus_id', '=', 'bus.bus_id')
            ->join('emp_bus_routes as route', 'log.route_id', '=', 'route.route_id')
            ->select(
                'bus.vender_id',        // บริษัทรถ
                'log.factory_id',       // โรงงาน
                'route.route_name',     // สายรถ
                'log.actual_bus_type',  // ประเภทรถที่มาจริง
                DB::raw('COUNT(*) as total_trips'),
                DB::raw('SUM(log.applied_price) as total_amount'),
                DB::raw('SUM(log.passenger_count) as total_passengers')
            )
            ->groupBy('bus.vender_id', 'log.factory_id', 'route.route_name', 'log.actual_bus_type')
            // เรียงตาม บริษัท > โรงงาน > สายรถ ตามที่คุณต้องการ
            ->orderBy('bus.vender_id')
            ->orderBy('log.factory_id')
            ->orderBy('route.route_name')
            ->get();

        return view('admin.report', compact('reports'));
    }
}