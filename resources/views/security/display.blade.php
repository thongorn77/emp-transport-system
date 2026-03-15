<div style="
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
    height:100vh;
    margin:0;
    padding:10px;
    font-family:sans-serif;
    background:#ffffff;
">

    <!-- โรงงาน -->
    <h2 style="
        margin-bottom:10px;
        font-size:6vw;
        color:#333;
        text-align:center;
    ">
        โรงงาน: {{ strtoupper($fac) }}
    </h2>

    <!-- QR CODE -->
    <div style="
        width:90vw;
        height:90vw;
        max-width:600px;
        max-height:600px;
        border:6px solid #28a745;
        border-radius:25px;
        display:flex;
        align-items:center;
        justify-content:center;
        background:white;
        box-shadow:0 15px 40px rgba(0,0,0,0.15);
    ">
        {!! QrCode::size(900)->margin(1)->generate($token) !!}
    </div>

    <!-- ข้อความ -->
    <div style="margin-top:15px;text-align:center;width:90vw;">
        <div style="
            font-size:1.2rem;
            font-weight:bold;
            color:#d9534f;
            background:#fff5f5;
            padding:8px 20px;
            border-radius:50px;
            display:inline-block;
        ">
            📷 สแกนเพื่อลงเวลา
        </div>

        <!-- progress -->
        <div style="
            width:80%;
            height:6px;
            background:#eee;
            border-radius:10px;
            margin:15px auto 5px;
            overflow:hidden;
        ">
            <div id="bar" style="
                height:100%;
                background:#28a745;
                width:100%;
                transition:width 1s linear;
            "></div>
        </div>

        <small style="color:#999">
            เปลี่ยน QR ในอีก <span id="timer">30</span> วินาที
        </small>
    </div>
</div>

<script>
let seconds = 30;
const bar = document.getElementById('bar');
const timerText = document.getElementById('timer');

setInterval(function() {
    seconds--;
    timerText.innerText = seconds;
    bar.style.width = (seconds / 30 * 100) + '%';

    if(seconds <= 0){
        location.reload();
    }
},1000);
</script>