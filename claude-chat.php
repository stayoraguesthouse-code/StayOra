<?php
// ✅ کلاؤڈ اور ہوسٹنگ پرفارمنس کے لیے ایرر رپورٹنگ کنٹرول کریں
error_reporting(E_ALL);
ini_set('display_errors', 0); // ڈائریکٹ ایررز بند کریں تاکہ JSON فارمیٹ خراب نہ ہو

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
function send_error($msg) {
    echo json_encode([
        'error' => $msg,
        'content' => [
            [
                'type' => 'text',
                'text' => "⚠️ سسٹم ایرر: " . $msg
            ]
        ]
    ]);
    exit;
}

// ✅ سمارٹ اور گلوبل فیٹل ایرر کیپچر (Shutdown Handler)
// اگر سرور پر پی ایچ پی کا کوئی بھی پرانا ورژن ہونے کی وجہ سے کوڈ کریش ہوگا،
// تو یہ فنکشن اسے خاموش کریش (HTTP 500) ہونے کے بجائے چیٹ وزٹ کے اندر شو کروا دے گا۔
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== NULL && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        header('Content-Type: application/json');
        echo json_encode([
            'error' => 'PHP Fatal Error: ' . $error['message'],
            'content' => [
                [
                    'type' => 'text',
                    'text' => "⚠️ سرور کریش رپورٹ (PHP Error): " . $error['message'] . " (فائل: " . basename($error['file']) . "، لائن: " . $error['line'] . ")"
                ]
            ]
        ]);
        exit;
    }
});

// ✅ ENV فائل لوڈ کرنے کا انتہائی محفوظ اور ہر پی ایچ پی ورژن کے لیے مطابقت رکھنے والا طریقہ
$ENV_VARS = [];
function loadEnv($path) {
    global $ENV_VARS;
    if (!file_exists($path)) {
        return false;
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) return false;

    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;
        
        // 🔴 پی ایچ پی 7 اور پرانے ورژنز کے ساتھ مطابقت کے لیے str_starts_with کی جگہ strpos استعمال کیا گیا ہے
        if (strpos($line, '#') === 0) continue; 
        
        if (strpos($line, '=') !== false) {
            $parts = explode('=', $line, 2);
            $key = trim($parts[0]);
            $val = trim($parts[1]);
            
            // اگر ویلیو کے گرد کوٹس ہوں تو انہیں صاف کریں
            $val = trim($val, "\"'");
            
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

// API Key حاصل کرنے کے لیے 4 متبادل طریقے (بیک ورڈ مطابقت کے ساتھ)
$api_key = getenv('GEMINI_API_KEY');
if (!$api_key) $api_key = isset($_ENV['GEMINI_API_KEY']) ? $_ENV['GEMINI_API_KEY'] : '';
if (!$api_key) $api_key = isset($_SERVER['GEMINI_API_KEY']) ? $_SERVER['GEMINI_API_KEY'] : '';
if (!$api_key) $api_key = isset($ENV_VARS['GEMINI_API_KEY']) ? $ENV_VARS['GEMINI_API_KEY'] : '';

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

// اگر جیمنائی API کوئی ایرر واپس کرے
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
    send_error("جیمنائی کی طرف سے خالی جواب موصول ہوا۔");
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
