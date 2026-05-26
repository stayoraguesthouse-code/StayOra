<?php
// اصل server path معلوم کریں
echo "<b>PHP چل رہی یہاں سے:</b> " . __FILE__ . "<br>";
echo "<b>Home Directory:</b> " . getenv('HOME') . "<br>";
echo "<b>Document Root:</b> " . $_SERVER['DOCUMENT_ROOT'] . "<br>";

// مختلف paths چیک کریں
$paths = [
  '/home/noorgeec/cred/so.env',
  '/home/noorgeec/cred/so.env',
  '/var/www/noorgeec/cred/so.env',
  dirname(dirname($_SERVER['DOCUMENT_ROOT'])) . '/cred/so.env',
  dirname($_SERVER['DOCUMENT_ROOT']) . '/cred/so.env',
];

echo "<br><b>Path Check:</b><br>";
foreach($paths as $p) {
  echo (file_exists($p) ? "✅ FOUND" : "❌ NOT FOUND") . " → $p <br>";
}
?>
