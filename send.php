<?php
// Faqat POST so'rovlarni qabul qilamiz
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // BOT TOKEN VA CHAT ID SHU YERGA YOZILADI (Serverda bo'lgani uchun 100% xavfsiz)
    $botToken = "";
    $chatId = "";

    // Xavfsizlik: XSS hujumlarining oldini olish uchun belgilarni tozalaymiz
    $ism = htmlspecialchars(trim($_POST['ism']));
    $tel = htmlspecialchars(trim($_POST['tel']));
    $spamTrap = htmlspecialchars(trim($_POST['spam_trap']));

    // Honeypot (Spam botlar tuzog'i) tekshiruvi
    if (!empty($spamTrap)) {
        http_response_code(400);
        echo "Spam bot aniqlandi!";
        exit;
    }

    // Hech narsa yozilmagan bo'lsa
    if (empty($ism) || empty($tel)) {
        http_response_code(400);
        echo "Ma'lumotlar to'liq emas!";
        exit;
    }

    // Telegramga yuboriladigan xabar matni
    $text = "🚕 Yangi haydovchi arizasi!\n\n👤 Ism: " . $ism . "\n📞 Telefon: " . $tel;

    // Telegram API ga CURL orqali xavfsiz so'rov yuborish
    $url = "https://api.telegram.org/bot" . $botToken . "/sendMessage";
    $data = array('chat_id' => $chatId, 'text' => $text);
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response) {
        http_response_code(200);
        echo "Muvaffaqiyatli yuborildi";
    } else {
        http_response_code(500);
        echo "Telegramga yuborishda xatolik";
    }
} else {
    http_response_code(403);
    echo "Ruxsat etilmagan so'rov";
}
?>