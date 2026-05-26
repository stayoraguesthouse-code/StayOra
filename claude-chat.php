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

// API Key خود بخود load ہو جائے گی
$api_key = getenv('ANTHROPIC_API_KEY');

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

$system_prompt = "You are a helpful assistant for StayOra Guest House in Pakistan. Help guests with room bookings, availability, pricing, and facilities. Be friendly and respond in Urdu or English based on what the guest uses.";

$data = [
  'model' => 'claude-sonnet-4-20250514',
  'max_tokens' => 1024,
  'system' => $system_prompt,
  'messages' => [['role' => 'user', 'content' => $user_msg]]
];

$ch = curl_init('https://api.anthropic.com/v1/messages');
curl_setopt_array($ch, [
  CURLOPT_POST => true,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTPHEADER => [
    "x-api-key: {$api_key}",
    "anthropic-version: 2023-06-01",
    "Content-Type: application/json"
  ],
  CURLOPT_POSTFIELDS => json_encode($data)
]);

$response = curl_exec($ch);
$err = curl_error($ch);
curl_close($ch);

if ($err) {
  echo json_encode(['error' => $err]);
} else {
  echo $response;
}
?>
