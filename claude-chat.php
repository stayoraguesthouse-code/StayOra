<?php
// ✅ اپنی API Key یہاں لگائیں
$api_key = "YOUR_ANTHROPIC_API_KEY";

$user_message = json_decode(file_get_contents('php://input'), true)['message'] ?? '';

$data = [
  'model' => 'claude-sonnet-4-20250514',
  'max_tokens' => 1024,
  'messages' => [['role' => 'user', 'content' => $user_message]]
];

$ch = curl_init('https://api.anthropic.com/v1/messages');
curl_setopt_array($ch, [
  CURLOPT_POST => true,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_HTTPHEADER => [
    "x-api-key: $api_key",
    "anthropic-version: 2023-06-01",
    "Content-Type: application/json"
  ],
  CURLOPT_POSTFIELDS => json_encode($data)
]);

$response = curl_exec($ch);
header('Content-Type: application/json');
echo $response;
?>
