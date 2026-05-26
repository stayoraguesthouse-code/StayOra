<?php
// ✅ سیشن سٹارٹ کریں برائے سیکیورٹی لاگ ان
session_start();

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
        if (empty($line) || strpos($line, '#') === 0) continue; 
        
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
loadEnv($env_path);

// سیکیورٹی پاس ورڈ حاصل کریں (پہلے .env فائل سے، ورنہ ڈیفالٹ StayOra@Admin ہوگا)
$secure_password = getenv('DASHBOARD_PASSWORD') ?: ($_ENV['DASHBOARD_PASSWORD'] ?? ($_SERVER['DASHBOARD_PASSWORD'] ?? ($ENV_VARS['DASHBOARD_PASSWORD'] ?? 'StayOra@Admin')));

// ✅ لاگ آؤٹ پروسیس
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    unset($_SESSION['stayora_logged_in']);
    session_destroy();
    header('Location: leads-portal.php');
    exit;
}

// ✅ لاگ ان ویلیڈیشن
$login_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    if ($_POST['password'] === $secure_password) {
        $_SESSION['stayora_logged_in'] = true;
    } else {
        $login_error = 'غلط پاس ورڈ! دوبارہ کوشش کریں۔';
    }
}

// ✅ اگر لاگ ان نہیں ہے تو لاگ ان فارم دکھائیں
if (!isset($_SESSION['stayora_logged_in']) || $_SESSION['stayora_logged_in'] !== true) {
?>
<!DOCTYPE html>
<html lang="ur" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StayOra Leads Portal - Security Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-2xl shadow-xl w-full max-w-md border border-slate-200">
        <div class="text-center mb-6">
            <span class="text-5xl">🏨</span>
            <h1 class="text-2xl font-bold text-[#1a5276] mt-3">StayOra Leads Portal</h1>
            <p class="text-slate-500 text-sm mt-1">سیکیورٹی لاگ ان فارم</p>
        </div>
        
        <?php if (!empty($login_error)): ?>
            <div class="bg-red-50 text-red-600 text-sm p-3 rounded-lg text-center mb-4 border border-red-200">
                <?php echo $login_error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">ڈیش بورڈ پاس ورڈ درج کریں:</label>
                <input type="password" name="password" required placeholder="••••••••" 
                       class="w-full h-12 px-4 rounded-xl border border-slate-300 focus:outline-none focus:border-[#1a5276] focus:ring-2 focus:ring-[#1a5276]/10 transition text-center font-mono text-lg">
            </div>
            <button type="submit" class="w-full h-12 bg-gradient-to-r from-[#1a5276] to-[#2471a3] text-white font-bold rounded-xl shadow-lg shadow-[#1a5276]/20 hover:opacity-95 transition">
                پورٹل لاگ ان کریں 🔑
            </button>
        </form>
        <div class="text-center mt-6 text-xs text-slate-400">
            StayOra Karachi SMCHS • Designed for Business Operations
        </div>
    </div>
</body>
</html>
<?php
    exit;
}

// ✅ [ڈیٹا ریڈ سسٹم] - leads.json فائل سے ڈیٹا لوڈ کریں
$leads_file = __DIR__ . '/leads.json';
$leads = [];
if (file_exists($leads_file)) {
    $file_data = file_get_contents($leads_file);
    $leads = json_decode($file_data, true);
    if (!is_array($leads)) {
        $leads = [];
    }
    // تازہ ترین لیڈز کو لسٹ میں سب سے اوپر لانے کے لیے ریورس کریں
    $leads = array_reverse($leads);
}

// ریئل ٹائم کاؤنٹرز کی فگرز بنانا
$total_leads = count($leads);
$today_leads = 0;
$unique_cities = [];
$today_date = date('Y-m-d');

foreach ($leads as $lead) {
    if (isset($lead['timestamp']) && strpos($lead['timestamp'], $today_date) === 0) {
        $today_leads++;
    }
    if (!empty($lead['city'])) {
        $unique_cities[strtolower(trim($lead['city']))] = true;
    }
}
$cities_count = count($unique_cities);
?>
<!DOCTYPE html>
<html lang="ur" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StayOra Guest House - Active Leads Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Noto+Nastaliq+Urdu&display=swap');
        .font-urdu { font-family: 'Calibri', 'Segoe UI', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen font-urdu text-slate-800">

    <!-- 🌐 ٹاپ ہیڈر نیویگیشن -->
    <header class="bg-gradient-to-r from-[#1a5276] to-[#2471a3] text-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 py-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="text-3xl">🏨</span>
                <div>
                    <h1 class="text-2xl font-bold tracking-wide">StayOra Guest House Karachi</h1>
                    <p class="text-xs text-[#d4efdf] mt-1">کسٹمر بکنگ اور انکوائری لیڈز پورٹل</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="bg-emerald-600 text-xs px-3 py-1.5 rounded-full font-bold flex items-center gap-1.5 animate-pulse">
                    <span class="w-2 h-2 bg-white rounded-full"></span> لائیو اپ ڈیٹ
                </span>
                <a href="leads-portal.php?action=logout" class="bg-red-600 hover:bg-red-700 text-xs font-bold px-4 py-2 rounded-lg transition">
                    لاگ آؤٹ 🚪
                </a>
            </div>
        </div>
    </header>

    <!-- 📦 مین باڈی کنٹینر -->
    <main class="max-w-7xl mx-auto px-4 py-8 sm:px-6 lg:px-8">
        
        <!-- 📊 سٹیٹسٹک کارڈز سیکشن -->
        <section class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- کارڈ 1: کل لیڈز -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-slate-400 text-sm font-semibold">کل موصول شدہ لیڈز</p>
                    <h3 class="text-3xl font-bold text-[#1a5276] mt-1"><?php echo $total_leads; ?></h3>
                </div>
                <div class="w-14 h-14 bg-[#1a5276]/10 text-[#1a5276] rounded-2xl flex items-center justify-center text-2xl">👥</div>
            </div>

            <!-- کارڈ 2: آج کی لیڈز -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-slate-400 text-sm font-semibold">آج کی نئی لیڈز</p>
                    <h3 class="text-3xl font-bold text-emerald-600 mt-1"><?php echo $today_leads; ?></h3>
                </div>
                <div class="w-14 h-14 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center text-2xl">📅</div>
            </div>

            <!-- کارڈ 3: مختلف شہروں کی تعداد -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-slate-400 text-sm font-semibold">مختلف شہروں سے مہمان</p>
                    <h3 class="text-3xl font-bold text-amber-600 mt-1"><?php echo $cities_count; ?></h3>
                </div>
                <div class="w-14 h-14 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center text-2xl">📍</div>
            </div>
        </section>

        <!-- 🔍 تلاش اور ایکشن فلٹرز -->
        <section class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm mb-6 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="w-full sm:max-w-md">
                <input type="text" id="table-search" onkeyup="filterLeads()" placeholder="کسٹمر کا نام، موبائل نمبر یا شہر تلاش کریں..." 
                       class="w-full h-11 px-4 rounded-xl border border-slate-300 focus:outline-none focus:border-[#1a5276] transition text-sm">
            </div>
            <div class="flex items-center gap-3 w-full sm:w-auto">
                <button onclick="exportToCSV()" class="w-full sm:w-auto px-5 h-11 bg-emerald-600 text-white font-bold rounded-xl shadow-md hover:bg-emerald-700 transition flex items-center justify-center gap-2 text-sm">
                    📥 ڈاؤن لوڈ کریں (Excel/CSV)
                </button>
            </div>
        </section>

        <!-- 📋 لیڈز کی تفصیلی ٹیبل لسٹ -->
        <section class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-right border-collapse" id="leads-table">
                    <thead>
                        <tr class="bg-slate-100 border-bottom border-slate-200 text-slate-500 text-sm font-bold">
                            <th class="p-4 text-center w-16">نمبر</th>
                            <th class="p-4">تاریخ اور وقت</th>
                            <th class="p-4">مہمان کا نام</th>
                            <th class="p-4">موبائل نمبر</th>
                            <th class="p-4">ای میل</th>
                            <th class="p-4">شہر</th>
                            <th class="p-4 text-center">فوری رابطہ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        <?php if (empty($leads)): ?>
                            <tr>
                                <td colspan="7" class="p-8 text-center text-slate-400">
                                    کوئی کسٹمر ریکارڈ ابھی موجود نہیں ہے۔ چیٹ بوٹ پر ڈیمو فارم فل کر کے چیک کریں!
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($leads as $index => $lead): 
                                $row_num = $total_leads - $index;
                                $name = htmlspecialchars($lead['name'] ?? 'Guest');
                                $phone = htmlspecialchars($lead['phone'] ?? '');
                                $email = htmlspecialchars($lead['email'] ?? '—');
                                $city = htmlspecialchars($lead['city'] ?? '—');
                                $timestamp = htmlspecialchars($lead['timestamp'] ?? '—');
                                
                                // واٹس ایپ کی کسٹم میسیج باڈی تشکیل دینا
                                $clean_phone = preg_replace('/[^0-9]/', '', $phone);
                                if (strpos($clean_phone, '0') === 0) {
                                    $clean_phone = '92' . substr($clean_phone, 1);
                                }
                                $wa_message = urlencode("السلام علیکم {$name} صاحب! میں StayOra کراچی گیسٹ ہاؤس کے مینیجر کی طرف سے بات کر رہا ہوں۔ ہمیں چٹ بوٹ پر آپ کی کمرے کی بکنگ کی انکوائری موصول ہوئی ہے۔ بتائیے ہم آپ کی بکنگ کو کنفرم کرنے میں کیا مدد کر سکتے ہیں؟");
                                $whatsapp_link = "https://wa.me/{$clean_phone}?text={$wa_message}";
                            ?>
                                <tr class="hover:bg-slate-50/80 transition text-sm">
                                    <td class="p-4 text-center font-bold text-slate-400"><?php echo $row_num; ?></td>
                                    <td class="p-4 text-slate-500 font-mono"><?php echo $timestamp; ?></td>
                                    <td class="p-4 font-bold text-[#1a5276]"><?php echo $name; ?></td>
                                    <td class="p-4 font-mono font-semibold"><?php echo $phone; ?></td>
                                    <td class="p-4 text-slate-500 font-mono"><?php echo $email; ?></td>
                                    <td class="p-4">
                                        <span class="bg-amber-50 text-amber-700 text-xs px-2.5 py-1 rounded-full font-bold">
                                            📍 <?php echo !empty($city) ? $city : '—'; ?>
                                        </span>
                                    </td>
                                    <td class="p-4">
                                        <div class="flex items-center justify-center gap-2">
                                            <!-- واٹس ایپ بٹن -->
                                            <a href="<?php echo $whatsapp_link; ?>" target="_blank" 
                                               class="bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs px-3 py-2 rounded-lg transition inline-flex items-center gap-1.5 shadow-sm shadow-emerald-500/10">
                                                💬 واٹس ایپ
                                            </a>
                                            <!-- ڈائریکٹ کال بٹن -->
                                            <a href="tel:<?php echo $phone; ?>" 
                                               class="bg-sky-500 hover:bg-sky-600 text-white font-bold text-xs px-3 py-2 rounded-lg transition inline-flex items-center gap-1.5 shadow-sm shadow-sky-500/10">
                                                📞 کال کریں
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <!-- 🌐 جاوا اسکرپٹ سرچ اور ایکسل ایکسپورٹ -->
    <script>
        // لائیو سرچ فنکشن
        function filterLeads() {
            const input = document.getElementById("table-search");
            const filter = input.value.toLowerCase();
            const table = document.getElementById("leads-table");
            const tr = table.getElementsByTagName("tr");

            for (let i = 1; i < tr.length; i++) {
                let show = false;
                // نام، نمبر اور شہر کے کالم اسکین کرنا
                const tdName = tr[i].getElementsByTagName("td")[2];
                const tdPhone = tr[i].getElementsByTagName("td")[3];
                const tdCity = tr[i].getElementsByTagName("td")[5];

                if (tdName || tdPhone || tdCity) {
                    const textName = (tdName.textContent || tdName.innerText).toLowerCase();
                    const textPhone = (tdPhone.textContent || tdPhone.innerText).toLowerCase();
                    const textCity = (tdCity.textContent || tdCity.innerText).toLowerCase();

                    if (textName.indexOf(filter) > -1 || textPhone.indexOf(filter) > -1 || textCity.indexOf(filter) > -1) {
                        show = true;
                    }
                }
                tr[i].style.display = show ? "" : "none";
            }
        }

        // ایکسل (CSV) ایکسپورٹر فنکشن (موبائل اور ڈیسک ٹاپ کے لیے لائٹ ویٹ اور محفوظ)
        function exportToCSV() {
            const rows = document.querySelectorAll("#leads-table tr");
            let csvContent = "\uFEFF"; // UTF-8 BOM تاکہ اردو ٹیکسٹ ایکسل میں بالکل درست نظر آئے
            
            // کالم ہیڈرز ڈالیں
            csvContent += "Serial,Date Time,Name,Phone,Email,City\n";

            for (let i = 1; i < rows.length; i++) {
                const cols = rows[i].querySelectorAll("td");
                if (cols.length < 6) continue;
                
                const serial = cols[0].innerText.trim();
                const datetime = cols[1].innerText.trim();
                const name = cols[2].innerText.trim().replace(/,/g, " "); // کوما ہٹائیں تاکہ کالم خراب نہ ہوں
                const phone = cols[3].innerText.trim();
                const email = cols[4].innerText.trim();
                const city = cols[5].innerText.trim().replace("📍", "").trim();

                csvContent += `"${serial}","${datetime}","${name}","${phone}","${email}","${city}"\n`;
            }

            const blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });
            const link = document.createElement("a");
            const url = URL.createObjectURL(blob);
            
            link.setAttribute("href", url);
            link.setAttribute("download", `StayOra_Leads_Report_${new Date().toISOString().split('T')[0]}.csv`);
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    </script>
</body>
</html>
