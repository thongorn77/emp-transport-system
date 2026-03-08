<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class SecurityController extends Controller
{
    public function display($fac)
    {
        // สร้าง Token แบบสุ่ม และเก็บไว้ใน Cache 1 นาที เพื่อใช้ตรวจสอบตอนคนขับสแกน
        $token = Str::random(32);
        Cache::put('qr_token_' . $token, $fac, 60); 

        return view('security.display', [
            'token' => $token,
            'fac' => $fac
        ]);
    }
}