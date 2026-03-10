<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    // 1. Dashboard / Report
    public function index()
    {
        $reports = DB::table('Bus_In_Out_Log as log')
            ->join('emp_buses as bus', 'log.bus_id', '=', 'bus.bus_id')
            ->join('emp_bus_routes as route', 'log.route_id', '=', 'route.route_id')
            ->select(
                'bus.vender_id',
                'log.factory_id',
                'route.route_name',
                'log.actual_bus_type',
                DB::raw('COUNT(*) as total_trips'),
                DB::raw('SUM(log.applied_price) as total_amount'),
                DB::raw('SUM(log.passenger_count) as total_passengers')
            )
            ->groupBy('bus.vender_id', 'log.factory_id', 'route.route_name', 'log.actual_bus_type')
            ->orderBy('bus.vender_id')
            ->orderBy('log.factory_id')
            ->orderBy('route.route_name')
            ->get();

        return view('admin.report', compact('reports'));
    }

    // 2. รายชื่อคนขับ + Approve
    public function driverList()
    {
        // ดึง drivers ทั้งหมดพร้อมรถที่แต่ละคนลงทะเบียนไว้
        $drivers = DB::table('emp_drivers as d')
            ->orderBy('d.is_approved')
            ->orderBy('d.created_at', 'desc')
            ->get();

        // ดึงรถของแต่ละคน (group by line_user_id)
        $driverBuses = DB::table('emp_driver_buses as db')
            ->join('emp_buses as b', 'db.bus_id', '=', 'b.bus_id')
            ->leftJoin('Bus_Route as r', 'b.route_id', '=', 'r.Route_ID')
            ->select('db.line_user_id', 'b.plate_no', 'r.Route_Name')
            ->get()
            ->groupBy('line_user_id');

        return view('admin.drivers', compact('drivers', 'driverBuses'));
    }

    // 3. Approve คนขับ (ใช้ id จาก emp_drivers)
    public function approveDriver($id)
    {
        $affected = DB::table('emp_drivers')
            ->where('id', $id)
            ->where('is_approved', false)
            ->update(['is_approved' => true]);

        $msg = $affected ? 'อนุมัติคนขับเรียบร้อยแล้ว' : 'ไม่พบข้อมูล หรืออนุมัติไปแล้ว';
        return back()->with('success', $msg);
    }

    // 4. จัดการทะเบียนรถ — list
    public function busesList()
    {
        $routes = DB::table('Bus_Route')
            ->where('Route_ID', '<>', '00')
            ->orderBy('Route_Name')
            ->get();

        $buses = DB::table('emp_buses as b')
            ->leftJoin('Bus_Route as r', 'b.route_id', '=', 'r.Route_ID')
            ->select('b.*', 'r.Route_Name')
            ->orderBy('r.Route_Name')
            ->orderBy('b.plate_no')
            ->get();

        return view('admin.buses', compact('buses', 'routes'));
    }

    // 5. เพิ่มรถใหม่
    public function busStore(Request $request)
    {
        $request->validate([
            'route_id' => 'required',
            'plate_no' => 'required|string|max:20',
            'bus_no'   => 'nullable|string|max:20',
            'capacity' => 'nullable|integer|min:1',
            'vender_id'=> 'nullable|string|max:50',
        ]);

        $exists = DB::table('emp_buses')->where('plate_no', $request->plate_no)->exists();
        if ($exists) {
            return back()->withInput()->withErrors(['plate_no' => 'ทะเบียนนี้มีอยู่แล้ว']);
        }

        DB::table('emp_buses')->insert([
            'route_id'  => $request->route_id,
            'plate_no'  => strtoupper(trim($request->plate_no)),
            'bus_no'    => $request->bus_no,
            'capacity'  => $request->capacity,
            'vender_id' => $request->vender_id,
        ]);

        return back()->with('success', "เพิ่มทะเบียน {$request->plate_no} เรียบร้อย");
    }

    // 6. แก้ไขรถ
    public function busUpdate(Request $request, $id)
    {
        $request->validate([
            'route_id' => 'required',
            'plate_no' => 'required|string|max:20',
            'bus_no'   => 'nullable|string|max:20',
            'capacity' => 'nullable|integer|min:1',
            'vender_id'=> 'nullable|string|max:50',
        ]);

        DB::table('emp_buses')->where('bus_id', $id)->update([
            'route_id'  => $request->route_id,
            'plate_no'  => strtoupper(trim($request->plate_no)),
            'bus_no'    => $request->bus_no,
            'capacity'  => $request->capacity,
            'vender_id' => $request->vender_id,
        ]);

        return back()->with('success', 'แก้ไขข้อมูลรถเรียบร้อย');
    }

    // 7. ลบรถ
    public function busDestroy($id)
    {
        // ตรวจสอบก่อนว่ามีคนขับลงทะเบียนรถคันนี้ไว้ไหม
        $hasDriver = DB::table('emp_driver_buses')
            ->where('bus_id', $id)
            ->exists();

        if ($hasDriver) {
            return back()->withErrors(['delete' => 'ไม่สามารถลบได้ มีคนขับลงทะเบียนรถคันนี้อยู่']);
        }

        DB::table('emp_buses')->where('bus_id', $id)->delete();
        return back()->with('success', 'ลบข้อมูลรถเรียบร้อย');
    }
    public function logsList(Request $request)
    {
        $query = DB::table('Bus_In_Out_Log as log')
            ->leftJoin('emp_buses as b', 'log.bus_id', '=', 'b.bus_id')
            ->leftJoin('Bus_Route as r', 'log.route_id', '=', 'r.Route_ID')
            ->leftJoin('emp_drivers as d', 'log.line_user_id', '=', 'd.line_user_id')
            ->select(
                'log.id',
                'log.log_date_time',
                'log.factory_id',
                'log.actual_bus_type',
                'log.passenger_count',
                'log.applied_price',
                'log.shift',
                'b.plate_no',
                'r.Route_Name',
                'log.line_user_id',
                DB::raw("ISNULL(d.driver_name, '-') as driver_name")
            )
            ->orderBy('log.log_date_time', 'desc');

        // Filter วันที่
        if ($request->date_from) {
            $query->whereDate('log.log_date_time', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('log.log_date_time', '<=', $request->date_to);
        }
        // Filter สายรถ
        if ($request->route_id) {
            $query->where('log.route_id', $request->route_id);
        }
        // Filter โรงงาน
        if ($request->factory_id) {
            $query->where('log.factory_id', $request->factory_id);
        }

        $logs   = $query->paginate(50);
        $routes = DB::table('Bus_Route')->where('Route_ID', '<>', '00')->orderBy('Route_Name')->get();

        return view('admin.logs', compact('logs', 'routes'));
    }
}