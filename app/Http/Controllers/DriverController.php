<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BusRoute;
use App\Models\Bus;
use App\Models\BusInOutLog;
use Illuminate\Support\Facades\Cache;

class DriverController extends Controller
{
    // 1. หน้าจอ LIFF สำหรับคนขับ (Blade View)
    public function index()
    {
        return view('driver.checkin');
    }

    // 2. ฟังก์ชันรับข้อมูลสแกน และบันทึกลงฐานข้อมูล
    public function store(Request $request)
    {
        // ตรวจสอบ Token จาก QR Code ใน Cache
        $fac = Cache::get('qr_token_' . $request->token);
        
        if (!$fac) {
            return response()->json([
                'status' => 'error', 
                'message' => 'QR Code หมดอายุ หรือไม่ถูกต้อง กรุณาสแกนใหม่'
            ], 400);
        }

        // ค้นหาข้อมูลรถ (ตอนนี้ใช้ Hardcode ทะเบียนรถเพื่อทดสอบก่อน)
        // ในอนาคตจะเปลี่ยนเป็นค้นหาจาก line_user_id
        $bus = Bus::where('plate_no', 'นข-1234')->first(); 
        
        if (!$bus) {
            return response()->json(['status' => 'error', 'message' => 'ไม่พบข้อมูลรถในระบบ'], 404);
        }

        // ดึงข้อมูลสายรถเพื่อเอา "ราคา" มาบันทึก
        $route = BusRoute::find($bus->route_id);

        // คำนวณราคาตามประเภทรถที่คนขับเลือกมาจริง (Logic A)
        $appliedPrice = ($request->bus_type === 'Van') ? $route->price_van : $route->price_bus;

        // บันทึกลงตาราง Bus_In_Out_Log
        $log = new BusInOutLog();
        $log->bus_id = $bus->bus_id;
        $log->route_id = $bus->route_id;
        $log->log_date_time = now();
        $log->actual_bus_type = $request->bus_type; // 'Bus' หรือ 'Van'
        $log->applied_price = $appliedPrice;
        $log->passenger_count = $request->passenger_count;
        $log->factory_id = $fac; // ได้ค่า Fac1 หรือ Fac2 จาก Cache
        $log->shift = (now()->hour >= 6 && now()->hour < 18) ? 'Day' : 'Night';
        $log->token = $request->token;
        $log->save();

        // ลบ Token ทิ้งทันทีเพื่อป้องกันการนำ Token เดิมไปสแกนซ้ำ (Anti-Cheat)
        Cache::forget('qr_token_' . $request->token);

        return response()->json([
            'status' => 'success', 
            'message' => "บันทึกข้อมูลเรียบร้อยที่โรงงาน $fac"
        ]);
    }
}