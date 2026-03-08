<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>บันทึกเที่ยววิ่ง</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://static.line-scdn.net/liff/edge/2/sdk.js"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="p-6 max-w-md mx-auto">
        <h1 class="text-2xl font-bold text-center text-blue-800 mb-6">ลงเวลาเข้างาน</h1>

        <div id="checkinForm" class="bg-white rounded-2xl shadow-lg p-6">
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">วันนี้ใช้รถประเภทอะไร:</label>
                <div class="flex gap-4">
                    <label class="flex-1 text-center border p-4 rounded-xl cursor-pointer peer-checked:bg-blue-100">
                        <input type="radio" name="bus_type" value="Bus" checked class="mb-2"><br>รถบัส
                    </label>
                    <label class="flex-1 text-center border p-4 rounded-xl cursor-pointer">
                        <input type="radio" name="bus_type" value="Van" class="mb-2"><br>รถตู้
                    </label>
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 font-bold mb-2">จำนวนพนักงาน (คน):</label>
                <input type="number" id="passenger_count" class="w-full text-3xl text-center border-2 border-blue-400 rounded-xl p-3" placeholder="0">
            </div>

            <button onclick="startScan()" class="w-full bg-green-500 text-white text-xl font-bold py-4 rounded-2xl shadow-lg active:scale-95 transition">
                เปิดกล้องสแกน QR Code
            </button>
        </div>

        <p id="statusMsg" class="mt-4 text-center text-gray-600 font-medium"></p>
    </div>

    <script>
        // เริ่มต้น LIFF (ใส่ LIFF ID ของคุณที่นี่เมื่อพร้อม)
        async function initLiff() {
            await liff.init({ liffId: "YOUR_LIFF_ID_HERE" });
        }
        initLiff();

        async function startScan() {
            const count = document.getElementById('passenger_count').value;
            if(!count || count <= 0) {
                alert('กรุณากรอกจำนวนพนักงาน');
                return;
            }

            // เปิดกล้องสแกน QR Code ผ่าน LINE
            const result = await liff.scanCodeV2();
            const token = result.value;

            if (token) {
                sendData(token);
            }
        }

        function sendData(token) {
            const busType = document.querySelector('input[name="bus_type"]:checked').value;
            const passengerCount = document.getElementById('passenger_count').value;

            fetch('/api/driver/checkin', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({
                    token: token,
                    bus_type: busType,
                    passenger_count: passengerCount
                })
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    alert('บันทึกสำเร็จ: ' + data.message);
                    liff.closeWindow(); // ปิดหน้าจออัตโนมัติ
                } else {
                    alert('ผิดพลาด: ' + data.message);
                }
            });
        }
    </script>
</body>
</html>