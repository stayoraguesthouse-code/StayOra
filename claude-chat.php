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

// ✅ فرنٹ اینڈ سے ان پٹ ڈیٹا حاصل کرنا
$input = json_decode(file_get_contents('php://input'), true);
$action = isset($input['action']) ? $input['action'] : 'chat'; // ڈیفالٹ ایکشن چیٹ ہے

// =======================================================
// 🌟 فلیش فیچر 1: کسٹمر لیڈ فارم ڈیٹا کو محفوظ کرنا (Save Lead Info)
// =======================================================
if ($action === 'save_lead') {
    $name  = isset($input['name']) ? trim($input['name']) : '';
    $phone = isset($input['phone']) ? trim($input['phone']) : '';
    $email = isset($input['email']) ? trim($input['email']) : '';
    $city  = isset($input['city']) ? trim($input['city']) : '';

    if (empty($name) || empty($phone)) {
        echo json_encode(['success' => false, 'error' => 'نام اور فون نمبر لازمی ہیں!']);
        exit;
    }

    // محفوظ کرنے کے لیے ڈیٹا کی ساخت (Data Structure)
    $lead_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'name'      => $name,
        'phone'     => $phone,
        'email'     => $email,
        'city'      => $city,
        'ip'        => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'Unknown'
    ];

    $file_path = __DIR__ . '/leads.json';
    $leads_list = [];

    // اگر پہلے سے فائل موجود ہو تو پرانا ڈیٹا لوڈ کریں
    if (file_exists($file_path)) {
        $file_content = file_get_contents($file_path);
        $leads_list = json_decode($file_content, true);
        if (!is_array($leads_list)) {
            $leads_list = [];
        }
    }

    // نیا لیڈ ریکارڈ لسٹ میں شامل کریں
    $leads_list[] = $lead_entry;

    // ڈیٹا کو خوبصورت فارمیٹ (JSON_PRETTY_PRINT) میں سیو کریں
    if (file_put_contents($file_path, json_encode($leads_list, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'سرور پر ڈیٹا لکھنے میں ناکامی۔']);
    }
    exit;
}

// ✅ ENV فائل لوڈ کرنے کا طریقہ
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
        if (strpos($line, '#') === 0) continue; 
        
        if (strpos($line, '=') !== false) {
            $parts = explode('=', $line, 2);
            $key = trim($parts[0]);
            $val = trim($parts[1]);
            $val = trim($val, "\"'");
            
            putenv("$key=$val");
            $_ENV[$key] = $val;
            $_SERVER[$key] = $val;
            $ENV_VARS[$key] = $val;
        }
    }
    return true;
}

// .env فائل لوڈ کریں
$env_path = '/home/noorgeec/cred/so.env';
if (!loadEnv($env_path)) {
    send_error("فائل {$env_path} پر نہیں ملی۔");
}

$api_key = getenv('GEMINI_API_KEY') ?: (isset($_ENV['GEMINI_API_KEY']) ? $_ENV['GEMINI_API_KEY'] : (isset($_SERVER['GEMINI_API_KEY']) ? $_SERVER['GEMINI_API_KEY'] : (isset($ENV_VARS['GEMINI_API_KEY']) ? $ENV_VARS['GEMINI_API_KEY'] : '')));

if (empty($api_key)) {
    send_error("so.env فائل میں GEMINI_API_KEY موجود نہیں ہے۔");
}

$user_msg = isset($input['message']) ? $input['message'] : '';

if (empty($user_msg)) {
    send_error("صارف کی طرف سے کوئی پیغام موصول نہیں ہوا۔");
}

// ✅ ڈائنامک نالج بیس (stayora-kb.md) لوڈر
$kb_file = __DIR__ . '/stayora-kb.md';
$kb_content = '';
if (file_exists($kb_file)) {
    $kb_content = file_get_contents($kb_file);
} else {
    $kb_content = "StayOra Guest House Karachi SMCHS.";
}

// کسٹمر کا نام اگر چیٹ اسکرپٹ سے بھیجا گیا ہو تو پرامپٹ کو ذاتی نوعیت کا بنائیں
$guest_name = isset($input['guest_name']) ? $input['guest_name'] : 'Guest';

$system_prompt = "You are 'StayOra Bot', the warm, professional, and highly welcoming virtual assistant for 'StayOra Guest House' in Pakistan.

The active guest you are talking to is named: " . $guest_name . " (Always refer to them respectfully as " . $guest_name . " sahib or " . $guest_name . " sahiba).

Your strict behavioral guidelines:
1. GREETING & TONE: Be extremely polite and hospitable. Use terms like 'Sir', 'Ma'am', 'G' or 'Jee'.
2. LANGUAGE MATCHING: Respond in the exact language style used by the guest (Urdu script, Roman Urdu, or English).
3. CALL TO ACTION: Always guide guests toward making a booking on WhatsApp.
4. CONCISENESS: Organize answers in clean bullet points. Keep mobile screens in mind.

Use the official StayOra Knowledge Base below to answer all questions:

" . $kb_content;

// Gemini API Payload
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

if ($err) {
    send_error("سرور کنکشن کی خرابی: " . $err);
}

if ($http_code !== 200) {
    $api_err = json_decode($response, true);
    $error_msg = isset($api_err['error']['message']) ? $api_err['error']['message'] : "HTTP Code $http_code";
    send_error("جیمنائی API کی خرابی: " . $error_msg);
}

$result = json_decode($response, true);
$bot_reply = isset($result['candidates'][0]['content']['parts'][0]['text']) ? $result['candidates'][0]['content']['parts'][0]['text'] : '';

if (empty($bot_reply)) {
    send_error("خالی جواب موصول ہوا۔");
}

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
