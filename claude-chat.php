<?php
// ✅ ENV فائل لوڈ کریں — GitHub سے باہر محفوظ
function loadEnv($path) {
    if (!file_exists($path)) return;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        if (strpos($line, '=') !== false) {
            [$key, $val] = explode('=', $line, 2);
            putenv(trim($key) . '=' . trim($val));
        }
    }
}

// ✅ آپ کا exact path
loadEnv('/home/noorgeec/cred/so.env');

// API Key خود بخود load ہو جائے گی (تبدیل شدہ برائے جیمنائی)
$api_key = getenv('GEMINI_API_KEY');

// CORS Headers
header('Access-Control-Allow-Origin: https://noorgee.pk');
header('Content-Type: application/json');

// User message
$input = json_decode(file_get_contents('php://input'), true);
$user_msg = $input['message'] ?? '';

if (empty($user_msg)) {
    echo json_encode(['error' => 'No message']);
    exit;
}

if (empty($api_key)) {
    echo json_encode(['error' => 'Gemini API Key is missing or empty in so.env']);
    exit;
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

// Gemini REST Endpoint — gemini-2.5-flash ماڈل کلاؤڈ پر چلے گا، سرور ریم پر بوجھ صفر
$ch = curl_init('https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . $api_key);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json"
    ],
    CURLOPT_POSTFIELDS => json_encode($data)
]);

$response = curl_exec($ch);
$err = curl_error($ch);
curl_close($ch);

if ($err) {
    echo json_encode(['error' => $err]);
    exit;
}

$result = json_decode($response, true);

// اگر Gemini API کی طرف سے کوئی خرابی/ایرر موصول ہو
if (isset($result['error'])) {
    echo json_encode(['error' => $result['error']['message'] ?? 'API Key or Endpoint issues.']);
    exit;
}

// Gemini کے رسپانس سے متن نکالنا
$bot_reply = $result['candidates'][0]['content']['parts'][0]['text'] ?? 'معذرت، میں آپ کے پیغام کا جواب نہیں دے پایا۔';

// ✅ انتہائی اہم: Gemini کے رسپانس کو Claude کے فارمیٹ میں تبدیل کرنا
// تاکہ آپ کی فرنٹ اینڈ 'chat-widget.js' فائل کو بالکل بھی تبدیل نہ کرنا پڑے!
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
