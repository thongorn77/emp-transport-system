<div style="text-align: center; font-family: sans-serif; margin-top: 50px;">
    <h1>โรงงาน: {{ strtoupper($fac) }}</h1>
    <div style="margin-bottom: 20px;">
        {!! QrCode::size(300)->generate($token) !!}
    </div>
    <p>Token: {{ $token }}</p>
    <p>สแกนเพื่อลงเวลาเข้างาน (QR จะเปลี่ยนทุกครั้งที่ Refresh)</p>
    <script>
        // ให้หน้าจอ Refresh ทุก 30 วินาทีเพื่อเปลี่ยน Token ใหม่
        setTimeout(function(){
           location.reload();
        }, 30000);
    </script>
</div>