<?php
// ENV لوڈ کریں
function loadEnv($path) {
  if (!file_exists($path)) return false;
  $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
  foreach ($lines as $line) {
    if (strpos(trim($line),'#')===0) continue;
    if (strpos($line,'=')!==false) {
      [$k,$v]=explode('=',$line,2);
      putenv(trim($k).'='.trim($v));
    }
  }
  return true;
}

$envPath = '/home/noorgeec/cred/so.env';
$envLoaded = loadEnv($envPath);
$api_key = getenv('ANTHROPIC_API_KEY');

echo "<h3>Debug Results</h3>";
echo "ENV File Found: " . ($envLoaded ? "✅ YES" : "❌ NO - Path wrong!") . "<br>";
echo "API Key Loaded: " . ($api_key ? "✅ YES (starts with: ".substr($api_key,0,10)."...)" : "❌ NO - Key missing!") . "<br>";
echo "cURL Available: " . (function_exists('curl_init') ? "✅ YES" : "❌ NO") . "<br>";

// Test API Call
if ($api_key) {
  $ch = curl_init('https://api.anthropic.com/v1/messages');
  curl_setopt_array($ch,[
    CURLOPT_POST=>true, CURLOPT_RETURNTRANSFER=>true, CURLOPT_TIMEOUT=>15,
    CURLOPT_HTTPHEADER=>["x-api-key: {$api_key}","anthropic-version: 2023-06-01","Content-Type: application/json"],
    CURLOPT_POSTFIELDS=>json_encode(['model'=>'claude-sonnet-4-20250514','max_tokens'=>50,'messages'=>[['role'=>'user','content'=>'Say OK']]])
  ]);
  $res=curl_exec($ch);
  $err=curl_error($ch);
  curl_close($ch);
  echo "API Test: " . ($err ? "❌ CURL Error: $err" : "Response: $res") . "<br>";
}
?>
