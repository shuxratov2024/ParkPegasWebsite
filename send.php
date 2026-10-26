<?php
$googleApiKey = ""
$botToken = "";
$chatId = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ism = htmlspecialchars($_POST['ism']);
    $tel = htmlspecialchars($_POST['tel']);
    
    // Rasmlarni yuklash
    $uploadDir = 'uploads/';
    $pravaName = time() . '_prava_' . basename($_FILES['prava_img']['name'][0]);
    $texName = time() . '_tex_' . basename($_FILES['tex_pasport_img']['name'][0]);
    
    move_uploaded_file($_FILES['prava_img']['tmp_name'][0], $uploadDir . $pravaName);
    move_uploaded_file($_FILES['tex_pasport_img']['tmp_name'][0], $uploadDir . $texName);

    // Google Vision API orqali rasmdan matn o'qish (Prava uchun namuna)
    $imageData = base64_encode(file_get_contents($uploadDir . $pravaName));
    $postData = json_encode([
        "requests" => [
            ["image" => ["content" => $imageData], "features" => [["type" => "TEXT_DETECTION"]]]
        ]
    ]);
    
    $ch = curl_init("https://vision.googleapis.com/v1/images:annotate?key=" . $googleApiKey);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $visionResult = json_decode(curl_exec($ch), true);
    curl_close($ch);

    $extractedText = $visionResult['responses'][0]['fullTextAnnotation']['text'] ?? "O'qib bo'lmadi";

    // Ma'lumotlarni db.json ga saqlash
    $dbFile = 'db.json';
    $data = file_exists($dbFile) ? json_decode(file_get_contents($dbFile), true) : [];
    $data[] = [
        "id" => uniqid(), "ism" => $ism, "tel" => $tel, 
        "prava" => $pravaName, "tex" => $texName, 
        "ocr_text" => $extractedText, "status" => "Kutilmoqda"
    ];
    file_put_contents($dbFile, json_encode($data));

    // Telegramga xabar
    $msg = "🚕 Yangi ariza tushdi!\n👤 Ism: $ism\n📞 Tel: $tel\n💡 Admin panelga kirib Yandexga tasdiqlang!";
    file_get_contents("https://api.telegram.org/bot$botToken/sendMessage?chat_id=$chatId&text=" . urlencode($msg));

    http_response_code(200);
}
?>