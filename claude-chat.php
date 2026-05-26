<?php
// ✅ CORS Headers — سب سے اوپر رکھیں تاکہ کوئی بھی ایرر آنے پر بلاک نہ ہو
header('Access-Control-Allow-Origin: https://noorgee.pk');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Content-Type: application/json');

// OPTIONS ریکوسٹ کو ہینڈل کریں (Preflight Request)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// ✅ فرنٹ اینڈ فرینڈلی ایرر ہینڈلر فنکشن
// یہ فنکشن کسی بھی خرابی کی صورت میں ایرر میسیج کو کلاڈ (Claude) کے فارمیٹ میں بھیجے گا
// تاکہ فرنٹ اینڈ وزٹ پر "معذرت، کوئی مسئلہ ہوا" کے بجائے اصل تکنیکی خرابی نظر آئے۔
function send_error($msg) {
    echo json_encode([
        'error' => $msg, // کونسول لاگنگ کے لیے
        'content' => [
            [
                'type' => 'text',
                'text' => "⚠️ سسٹم ایرر: " . $msg
            ]
        ]
    ]);
    exit;
}

// ✅ ENV فائل لوڈ کرنے کا انتہائی محفوظ اور مضبوط طریقہ
$ENV_VARS = [];
function loadEnv($path) {
    global $ENV_VARS;
    if (!file_exists($path)) {
        return false;
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || str_starts_with($line, '#')) continue;
        if (strpos($line, '=') !== false) {
            [$key, $val] = explode('=', $line, 2);
            $key = trim($key);
            $val = trim($val);
            // اگر ویلیو کے گرد کوٹس (Quotes) ہوں تو انہیں ہٹائیں
            $val = trim($val, "\"'");
            
            // سسٹم کے تمام ممکنہ انوائرمنٹ گلوبلز میں سیٹ کریں
            putenv("$key=$val");
            $_ENV[$key] = $val;
            $_SERVER[$key] = $val;
            $ENV_VARS[$key] = $val;
        }
    }
    return true;
}

// ✅ .env فائل لوڈ کریں اور تصدیق کریں
$env_path = '/home/noorgeec/cred/so.env';
if (!loadEnv($env_path)) {
    send_error("فائل {$env_path} پر نہیں ملی۔ براہ کرم پاتھ چیک کریں۔");
}

// API Key حاصل کرنے کے لیے 4 متبادل طریقے (تاکہ ہوسٹنگ سیکیورٹی بلاک نہ کرے)
$api_key = getenv('GEMINI_API_KEY') ?: ($_ENV['GEMINI_API_KEY'] ?? ($_SERVER['GEMINI_API_KEY'] ?? ($ENV_VARS['GEMINI_API_KEY'] ?? '')));

if (empty($api_key)) {
    send_error("so.env فائل میں GEMINI_API_KEY موجود نہیں ہے یا خالی ہے۔");
}

// فرنٹ اینڈ سے پیغام حاصل کرنا
$input = json_decode(file_get_contents('php://input'), true);
$user_msg = $input['message'] ?? '';

if (empty($user_msg)) {
    send_error("صارف کی طرف سے کوئی پیغام موصول نہیں ہوا۔ (Empty Message)");
}

$system_prompt = "You are a helpful assistant for StayOra Guest House in Pakistan. Help guests with room bookings, availability, pricing, and facilities. Be friendly and respond in Urdu or English based on what the guest uses.";

// Gemini API Payload ساخت
$data = [
    'contents' => [
        [
            'role' => 'user',
            'parts' => [
                ['text' => $user_msg]
            ]
        ]
    ],
    'systemInstruction' => [
        'parts' => [
            ['text' => $system_prompt]
        ]
    ]
];

// cURL کے ذریعے Gemini API کال کریں
$ch = curl_init('https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . $api_key);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 30,
    // 🔴 انتہائی اہم: cPanel ہوسٹنگ پر اکثر پرانے SSL سرٹیفکیٹس کی وجہ سے API کال فیل ہو جاتی ہے
    // اسے حل کرنے کے لیے عارضی طور پر SSL تصدیق کو بائی پاس کیا گیا ہے تاکہ کنکشن سو فیصد کامیاب ہو۔
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json"
    ],
    CURLOPT_POSTFIELDS => json_encode($data)
]);

$response = curl_exec($ch);
$err = curl_error($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// کنکشن فیل ہونے کا ایرر ہینڈل کریں
if ($err) {
    send_error("سرور کنکشن کی خرابی (cURL Error): " . $err);
}

// اگر جیمنائی API کوئی ایرر واپس کرے (جیسے انویلڈ کی یا کوٹہ ختم ہونا)
if ($http_code !== 200) {
    $api_err = json_decode($response, true);
    $error_msg = $api_err['error']['message'] ?? "HTTP Code $http_code (خام جواب: " . substr($response, 0, 150) . ")";
    send_error("جیمنائی API کی خرابی: " . $error_msg);
}

$result = json_decode($response, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    send_error("JSON ڈیٹا پارس کرنے میں ناکامی۔ خام جواب: " . substr($response, 0, 200));
}

// جیمنائی سے ٹیکسٹ حاصل کریں
$bot_reply = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';

if (empty($bot_reply)) {
    send_error("جیمنائی کی طرف سے خالی جواب موصول ہوا۔ مکمل ڈیٹا: " . json_encode($result));
}

// ✅ فرنٹ اینڈ 'chat-widget.js' کے لیے کلاڈ ہم آہنگ فارمیٹ
$mapped_response = [
    'content' => [
        [
            'type' => 'text',
            'text' => $bot_reply
        ]
    ]
];

echo json_encode($mapped_response);
?>
