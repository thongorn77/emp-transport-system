<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bus;
use App\Models\BusInOutLog;
use App\Models\Driver;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DriverController extends Controller
{
    // ─── Helper: ดึง logs + monthly summary พร้อม save session ──────────────
    private function buildPayload(Driver $driver): array
    {
        session(['driver_line_id' => $driver->line_user_id]);

        // รถที่ driver ขับได้
        $buses = DB::table('emp_driver_buses as db')
            ->join('emp_buses as b', 'db.bus_id', '=', 'b.bus_id')
            ->leftJoin('Bus_Route as r', 'b.route_id', '=', 'r.Route_ID')
            ->select('b.bus_id', 'b.plate_no', 'b.bus_no', 'r.Route_Name', 'b.route_id')
            ->where('db.line_user_id', $driver->line_user_id)
            ->get();

        $logs = DB::table('Bus_In_Out_Log as log')
            ->leftJoin('emp_buses as b', 'log.bus_id', '=', 'b.bus_id')
            ->leftJoin('Bus_Route as r', 'log.route_id', '=', 'r.Route_ID')
            ->select('log.log_date_time', 'log.factory_id', 'log.actual_bus_type',
                     'log.passenger_count', 'log.applied_price', 'log.shift',
                     'b.plate_no', 'r.Route_Name')
            ->where('log.line_user_id', $driver->line_user_id)
            ->orderBy('log.log_date_time', 'desc')
            ->limit(30)
            ->get()
            ->map(fn($l) => [
                ...(array)$l,
                'log_date_time' => \Carbon\Carbon::parse($l->log_date_time)->format('d/m/Y H:i'),
            ]);

        $monthly = DB::table('Bus_In_Out_Log')
            ->where('line_user_id', $driver->line_user_id)
            ->where('log_date_time', '>=', now()->subMonths(3))
            ->selectRaw("FORMAT(log_date_time, 'yyyy-MM') as month,
                         COUNT(*) as total_trips,
                         SUM(passenger_count) as total_passengers,
                         SUM(applied_price) as total_amount")
            ->groupByRaw("FORMAT(log_date_time, 'yyyy-MM')")
            ->orderByRaw("FORMAT(log_date_time, 'yyyy-MM') DESC")
            ->get();

        return [
            'success' => true,
            'driver'  => $driver,
            'buses'   => $buses,
            'logs'    => $logs,
            'monthly' => $monthly,
        ];
    }

    // ─── 1. หน้า Register ────────────────────────────────────────────────────
    public function showRegisterForm()
    {
        $busesByRoute = DB::table('emp_buses as b')
            ->leftJoin('Bus_Route as r', 'b.route_id', '=', 'r.Route_ID')
            ->select('b.bus_id', 'b.plate_no', 'b.bus_no', 'r.Route_ID', 'r.Route_Name')
            ->whereNotNull('b.plate_no')
            ->where('b.route_id', '<>', '00')
            ->orderBy('r.Route_Name')
            ->orderBy('b.plate_no')
            ->get()
            ->groupBy('Route_Name');

        return response()
            ->view('driver.register', compact('busesByRoute'))
            ->withHeaders([
                'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
                'Pragma'        => 'no-cache',
                'Expires'       => 'Thu, 01 Jan 1970 00:00:00 GMT',
            ]);
    }

    // ─── 2. บันทึกการลงทะเบียน ──────────────────────────────────────────────
    public function storeDriver(Request $request)
    {
        $request->validate([
            'line_user_id' => 'required|string',
            'driver_name'  => 'required|string|max:100',
            'phone'        => 'required|string|max:20',
            'bus_ids'      => 'required|array|min:1',
            'bus_ids.*'    => 'required|integer',
        ], [
            'bus_ids.required' => 'กรุณาเลือกรถที่คุณขับอย่างน้อย 1 คัน',
            'bus_ids.min'      => 'กรุณาเลือกรถที่คุณขับอย่างน้อย 1 คัน',
        ]);

        $lineId = $request->line_user_id;

        // ตรวจสอบว่ามีแล้วหรือยัง
        $existing = Driver::where('line_user_id', $lineId)->first();
        if ($existing) {
            return response()->json([
                'status'   => $existing->is_approved ? 'approved' : 'pending',
                'message'  => $existing->is_approved
                    ? 'ลงทะเบียนแล้วและได้รับการอนุมัติแล้ว'
                    : 'ลงทะเบียนแล้ว รอการอนุมัติจาก Admin',
            ]);
        }

        DB::transaction(function () use ($request, $lineId) {
            Driver::create([
                'line_user_id'      => $lineId,
                'line_display_name' => $request->line_display_name,
                'driver_name'       => $request->driver_name,
                'phone'             => $request->phone,
                'is_approved'       => false,
            ]);

            $rows = collect($request->bus_ids)->map(fn($busId) => [
                'line_user_id' => $lineId,
                'bus_id'       => $busId,
            ])->toArray();

            DB::table('emp_driver_buses')->insert($rows);
        });

        session(['driver_line_id' => $lineId]);

        return response()->json([
            'status'  => 'registered',
            'message' => 'ลงทะเบียนสำเร็จ รอ Admin อนุมัติ',
        ]);
    }

    // ─── 3. เช็คสถานะ driver จาก LINE ID (เหมือน checkStatus) ──────────────
    public function checkStatus(Request $request)
    {
        $lineId = $request->line_user_id;
        if (!$lineId) {
            return response()->json(['status' => 'error', 'message' => 'ไม่พบ LINE ID']);
        }

        $driver = Driver::where('line_user_id', $lineId)->first();
        if (!$driver) {
            return response()->json(['status' => 'new']);
        }
        // บันทึก session ให้ทุก driver (ทั้ง pending และ approved)
        session(['driver_line_id' => $lineId]);

        if (!$driver->is_approved) {
            return response()->json(['status' => 'pending', 'driver_name' => $driver->driver_name]);
        }

        return response()->json(['status' => 'approved', 'driver_name' => $driver->driver_name]);
    }

    // ─── 4. หน้า Check-in ───────────────────────────────────────────────────
    public function index()
    {
        return response()
            ->view('driver.checkin')
            ->withHeaders([
                'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
                'Pragma'        => 'no-cache',
                'Expires'       => 'Thu, 01 Jan 1970 00:00:00 GMT',
            ]);
    }

    // ─── 5. ดึงรถที่ driver คนนี้ขับได้ (สำหรับ check-in dropdown) ──────────
    public function myBuses(Request $request)
    {
        $lineId = $request->line_user_id;
        if (!$lineId) {
            return response()->json(['success' => false, 'message' => 'ไม่พบ LINE ID']);
        }

        $buses = DB::table('emp_driver_buses as db')
            ->join('emp_buses as b', 'db.bus_id', '=', 'b.bus_id')
            ->leftJoin('Bus_Route as r', 'b.route_id', '=', 'r.Route_ID')
            ->leftJoin('emp_bus_routes as er', 'b.route_id', '=', 'er.route_id')
            ->select('b.bus_id', 'b.plate_no', 'b.bus_no', 'b.route_id',
                     'r.Route_Name', 'er.price_bus', 'er.price_van')
            ->where('db.line_user_id', $lineId)
            ->orderBy('r.Route_Name')
            ->orderBy('b.plate_no')
            ->get();

        if ($buses->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'ไม่พบรถที่ลงทะเบียนไว้']);
        }

        return response()->json(['success' => true, 'buses' => $buses]);
    }

    // ─── 6. บันทึก Check-in (scan QR) ───────────────────────────────────────
    public function store(Request $request)
    {
        // ตรวจ QR Token
        $fac = Cache::get('qr_token_' . $request->token);
        if (!$fac) {
            return response()->json(['status' => 'error', 'message' => 'QR Code หมดอายุ หรือใช้ไปแล้ว'], 400);
        }

        // ตรวจ LINE ID
        $lineId = $request->line_user_id;
        if (!$lineId) {
            return response()->json(['status' => 'error', 'message' => 'ไม่พบ LINE ID กรุณาเปิดผ่าน LINE'], 400);
        }

        // ตรวจ Driver
        $driver = Driver::where('line_user_id', $lineId)->first();
        if (!$driver) {
            return response()->json(['status' => 'error', 'message' => 'ยังไม่ได้ลงทะเบียน'], 404);
        }
        if (!$driver->is_approved) {
            return response()->json(['status' => 'error', 'message' => 'รอ Admin อนุมัติก่อนใช้งาน'], 403);
        }

        // ตรวจว่า bus_id นี้เป็นรถของ driver คนนี้จริงไหม
        $busId = $request->bus_id;
        $isMyBus = DB::table('emp_driver_buses')
            ->where('line_user_id', $lineId)
            ->where('bus_id', $busId)
            ->exists();

        if (!$isMyBus) {
            return response()->json(['status' => 'error', 'message' => 'รถคันนี้ไม่ได้ลงทะเบียนไว้'], 403);
        }

        // ดึงข้อมูลรถ + สาย
        $bus = DB::table('emp_buses as b')
            ->leftJoin('Bus_Route as r', 'b.route_id', '=', 'r.Route_ID')
            ->leftJoin('emp_bus_routes as er', 'b.route_id', '=', 'er.route_id')
            ->select('b.bus_id', 'b.plate_no', 'b.bus_no', 'b.route_id',
                     'r.Route_Name', 'er.price_bus', 'er.price_van')
            ->where('b.bus_id', $busId)
            ->first();

        if (!$bus) {
            return response()->json(['status' => 'error', 'message' => 'ไม่พบข้อมูลรถ'], 404);
        }

        if ($bus->route_id == '00') {
            return response()->json(['status' => 'error', 'message' => 'สาย "เดินทางมาเอง" ไม่สามารถบันทึกได้'], 400);
        }

        $appliedPrice = ($request->bus_type === 'Van')
            ? ($bus->price_van ?? 0)
            : ($bus->price_bus ?? 0);

        try {
            $log = new BusInOutLog();
            $log->bus_id          = $bus->bus_id;
            $log->route_id        = $bus->route_id;
            $log->line_user_id    = $lineId;
            $log->log_date_time   = now();
            $log->actual_bus_type = $request->bus_type;
            $log->applied_price   = $appliedPrice;
            $log->passenger_count = $request->passenger_count;
            $log->factory_id      = $fac;
            $log->shift           = (now()->hour >= 6 && now()->hour < 18) ? 'Day' : 'Night';
            $log->token           = $request->token;
            $log->save();

            Cache::forget('qr_token_' . $request->token);

            return response()->json([
                'status'  => 'success',
                'message' => "บันทึกสำเร็จ (โรงงาน $fac · {$bus->Route_Name} · {$bus->plate_no})",
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'DB Error: ' . $e->getMessage()], 500);
        }
    }

    // ─── 7. Profile: เช็ค PHP session ───────────────────────────────────────
    public function profileSession()
    {
        $lineId = session('driver_line_id');
        if (!$lineId) return response()->json(['success' => false]);

        $driver = Driver::where('line_user_id', $lineId)->first();
        if (!$driver) {
            session()->forget('driver_line_id');
            return response()->json(['success' => false]);
        }

        return response()->json($this->buildPayload($driver));
    }

    // ─── 8. Profile: login ด้วย LINE ID ─────────────────────────────────────
    public function lineLogin(Request $request)
    {
        $lineId = $request->line_user_id;
        if (!$lineId) return response()->json(['success' => false, 'message' => 'ไม่พบ LINE ID']);

        $driver = Driver::where('line_user_id', $lineId)->first();
        if (!$driver) return response()->json(['success' => false, 'message' => 'ยังไม่ได้ลงทะเบียน']);

        return response()->json($this->buildPayload($driver));
    }

    // ─── 9. Profile: clear session ──────────────────────────────────────────
    public function profileSessionClear()
    {
        session()->forget('driver_line_id');
        return response()->json(['success' => true]);
    }

    // ─── 10. เช็ค session แบบเบา (ไม่โหลด logs) ─────────────────────────────
    public function sessionCheck()
    {
        $lineId = session('driver_line_id');
        if (!$lineId) return response()->json(['success' => false]);

        $driver = Driver::where('line_user_id', $lineId)->first();
        if (!$driver) {
            session()->forget('driver_line_id');
            return response()->json(['success' => false]);
        }

        return response()->json([
            'success'      => true,
            'line_user_id' => $driver->line_user_id,
            'driver_name'  => $driver->driver_name,
            'display_name' => $driver->line_display_name,
            'is_approved'  => $driver->is_approved,
        ]);
    }
}
