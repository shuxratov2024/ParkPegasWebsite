<?php
// YANDEX API KALITLARINGIZ
$clientId = "";
$yandexApiKey = "";

$dbFile = 'db.json';
$applications = file_exists($dbFile) ? json_decode(file_get_contents($dbFile), true) : [];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'yandex_sync') {
    // Yandex Fleet API ga Haydovchi profilini qo'shish (Namuna so'rov)
    $driverData = [
        "driver_profile" => [
            "first_name" => $_POST['ism'],
            "phones" => [$_POST['tel']],
            "driver_license" => ["number" => $_POST['prava_seriya']]
        ]
    ];
    
    $ch = curl_init("https://fleet-api.taxi.yandex.net/v2/parks/contractors/driver-profile?park_id=" . $clientId);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($driverData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'X-Client-ID: ' . $clientId,
        'X-API-Key: ' . $yandexApiKey
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    
    echo "<script>alert('Yandexga yuborildi!'); window.location.href='admin.php';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <title>Admin Panel - Park Pegas</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-10">
    <h1 class="text-3xl font-bold mb-8">Kutilayotgan Arizalar (Vision API)</h1>
    <?php foreach($applications as $app): if($app['status'] == 'Kutilmoqda'): ?>
    <div class="bg-white p-6 rounded-lg shadow mb-6 flex gap-6">
        <div class="w-1/3">
            <img src="uploads/<?= $app['prava'] ?>" class="w-full rounded mb-2">
            <p class="text-xs text-gray-500">O'qilgan matn: <?= substr($app['ocr_text'], 0, 100) ?>...</p>
        </div>
        <form method="POST" class="w-2/3 space-y-4">
            <input type="hidden" name="action" value="yandex_sync">
            <input type="hidden" name="id" value="<?= $app['id'] ?>">
            <input type="text" name="ism" value="<?= $app['ism'] ?>" class="border p-2 w-full rounded">
            <input type="text" name="tel" value="<?= $app['tel'] ?>" class="border p-2 w-full rounded">
            <input type="text" name="prava_seriya" placeholder="Prava seriyasini tekshirib kiriting (Masalan: AF1234567)" class="border p-2 w-full rounded bg-yellow-50" required>
            <button type="submit" class="bg-black text-yellow-500 px-6 py-2 rounded font-bold">Yandex Fleet'ga Yuborish</button>
        </form>
    </div>
    <?php endif; endforeach; ?>
</body>
</html>