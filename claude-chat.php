// ✅ StayOra Chat Widget - Fully Self-Contained Lead Form & Handover System Included
(function () {
    // گفتگو کاؤنٹر (Turn Counter) اور مہمان کی معلومات کا ریکارڈ ان-میموری رکھنا
    let messageCount = 0;
    const maxFreeQuestions = 5; // اس حد کے بعد بوٹ خودکار طور پر لائیو مینیجر کو ٹرانسفر کرے گا
    let guestInfo = {
        name: '',
        phone: '',
        email: '',
        city: '',
        submitted: false
    };

    // 0. پرانے اور غیر فعال بٹنز کو ہٹانا
    function cleanupOldWidgets() {
        const oldButtons = document.querySelectorAll('#chat-circle, .chat-btn, #chat-btn');
        oldButtons.forEach(btn => {
            if (btn && btn.id !== 'stayora-chat-circle') {
                btn.style.setProperty('display', 'none', 'important');
            }
        });
    }
    
    cleanupOldWidgets();
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', cleanupOldWidgets);
    } else {
        cleanupOldWidgets();
    }

    // 1. پرو فیشنل اسکوپڈ اسٹائلز کو انجیکٹ کرنا (CSS Injection with Form Styles)
    const styleElement = document.createElement('style');
    styleElement.innerHTML = `
        #stayora-chat-widget, #stayora-chat-widget * {
            box-sizing: border-box !important;
            font-family: 'Calibri', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif !important;
            margin: 0;
            padding: 0;
        }

        #stayora-chat-circle {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #1a5276 0%, #2980b9 100%);
            border-radius: 50%;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 24px rgba(26, 82, 118, 0.35);
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            z-index: 999999;
        }

        #stayora-chat-circle:hover {
            transform: scale(1.08) translateY(-3px);
            box-shadow: 0 12px 28px rgba(26, 82, 118, 0.45);
        }

        #stayora-chat-circle svg {
            width: 28px;
            height: 28px;
            fill: currentColor;
        }

        .stayora-chat-box {
            position: fixed;
            bottom: 110px;
            right: 30px;
            width: 380px;
            height: 550px;
            max-height: calc(100vh - 140px);
            background: #ffffff;
            border-radius: 18px;
            box-shadow: 0 12px 36px rgba(0, 0, 0, 0.16);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            opacity: 0;
            transform: translateY(30px) scale(0.95);
            pointer-events: none;
            transition: all 0.35s cubic-bezier(0.175, 0.885, 0.32, 1.15);
            z-index: 999999;
        }

        .stayora-chat-box.show {
            opacity: 1;
            transform: translateY(0) scale(1);
            pointer-events: auto;
        }

        .stayora-chat-header {
            background: linear-gradient(135deg, #1a5276 0%, #2471a3 100%);
            padding: 16px 20px;
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .stayora-chat-header-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .stayora-chat-avatar {
            width: 38px;
            height: 38px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            border: 2px solid rgba(255, 255, 255, 0.6);
        }

        .stayora-chat-title {
            display: flex;
            flex-direction: column;
        }

        .stayora-chat-title-main {
            font-weight: bold;
            font-size: 16px;
            letter-spacing: 0.5px;
        }

        .stayora-chat-status {
            font-size: 11px;
            color: #d4efdf;
            display: flex;
            align-items: center;
            gap: 5px;
            margin-top: 2px;
        }

        .stayora-chat-status::before {
            content: "";
            display: inline-block;
            width: 7px;
            height: 7px;
            background-color: #2ecc71;
            border-radius: 50%;
        }

        .stayora-chat-close {
            background: none;
            border: none;
            color: rgba(255, 255, 255, 0.8);
            font-size: 20px;
            cursor: pointer;
            padding: 4px;
            transition: color 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .stayora-chat-close:hover {
            color: white;
        }

        .stayora-chat-body {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            background-color: #f8fafc;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .stayora-msg-row {
            display: flex;
            width: 100%;
            margin: 2px 0;
        }

        .stayora-msg-row.bot {
            justify-content: flex-start;
        }

        .stayora-msg-row.user {
            justify-content: flex-end;
        }

        .stayora-bubble {
            max-width: 85%;
            padding: 12px 16px;
            border-radius: 16px;
            font-size: 14.5px;
            line-height: 1.6;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
            word-wrap: break-word;
        }

        .stayora-msg-row.bot .stayora-bubble {
            background-color: #ffffff;
            color: #475569;
            border-bottom-left-radius: 4px;
            border: 1px solid #e2e8f0;
            direction: rtl;
            text-align: right;
        }

        .stayora-msg-row.user .stayora-bubble {
            background: linear-gradient(135deg, #1a5276 0%, #2471a3 100%);
            color: #ffffff;
            border-bottom-right-radius: 4px;
            direction: ltr;
            text-align: left;
        }

        /* 📝 کسٹمر لیڈ جنریشن فارم کے اسٹائلز */
        .stayora-lead-form-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 18px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            width: 100%;
            direction: rtl;
            text-align: right;
        }

        .stayora-form-header {
            font-size: 15px;
            font-weight: bold;
            color: #1a5276;
            margin-bottom: 12px;
            border-bottom: 1.5px solid #f1f5f9;
            padding-bottom: 8px;
        }

        .stayora-form-group {
            margin-bottom: 12px;
        }

        .stayora-form-group label {
            display: block;
            font-size: 12px;
            color: #64748b;
            margin-bottom: 4px;
            font-weight: bold;
        }

        .stayora-form-input {
            width: 100%;
            height: 38px;
            border: 1.5px solid #cbd5e1;
            border-radius: 8px;
            padding: 0 12px;
            font-size: 13.5px;
            outline: none;
            transition: all 0.2s;
            background-color: #f8fafc;
        }

        .stayora-form-input:focus {
            border-color: #1a5276;
            background-color: #ffffff;
            box-shadow: 0 0 0 3px rgba(26, 82, 118, 0.1);
        }

        .stayora-form-submit {
            width: 100%;
            height: 40px;
            background: linear-gradient(135deg, #1a5276 0%, #2471a3 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(26, 82, 118, 0.2);
            transition: all 0.2s;
            margin-top: 6px;
        }

        .stayora-form-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 14px rgba(26, 82, 118, 0.3);
        }

        /* 🟢 واٹس ایپ مینیجر ہینڈ اوور بٹن */
        .stayora-whatsapp-handoff-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #25d366 0%, #128c7e 100%);
            color: white !important;
            text-decoration: none !important;
            font-weight: bold;
            border-radius: 12px;
            font-size: 14.5px;
            box-shadow: 0 4px 15px rgba(37, 211, 102, 0.3);
            transition: all 0.2s;
            margin-top: 10px;
            cursor: pointer;
        }

        .stayora-whatsapp-handoff-btn:hover {
            transform: scale(1.03);
            box-shadow: 0 6px 20px rgba(37, 211, 102, 0.4);
        }

        .stayora-bubble strong {
            color: #c0392b !important;
            font-weight: bold;
        }

        .stayora-bubble ul {
            margin: 8px 0;
            padding-right: 20px;
            list-style-type: disc;
        }

        .stayora-bubble li {
            margin: 4px 0;
        }

        .stayora-loader-dots {
            display: flex;
            gap: 4px;
            align-items: center;
            height: 20px;
            padding: 4px 8px;
        }

        .stayora-loader-dots span {
            width: 8px;
            height: 8px;
            background-color: #94a3b8;
            border-radius: 50%;
            display: inline-block;
            animation: stayoraBounce 1.4s infinite ease-in-out both;
        }

        .stayora-loader-dots span:nth-child(1) { animation-delay: -0.32s; }
        .stayora-loader-dots span:nth-child(2) { animation-delay: -0.16s; }

        @keyframes stayoraBounce {
            0%, 80%, 100% { transform: scale(0); }
            40% { transform: scale(1.0); }
        }

        .stayora-chat-input-box {
            padding: 14px 18px;
            background: #ffffff;
            border-top: 1px solid #e2e8f0;
        }

        .stayora-chat-form {
            display: flex;
            gap: 10px;
            align-items: center;
            width: 100% !important;
        }

        .stayora-input-field {
            flex: 1;
            height: 42px;
            border: 1.5px solid #cbd5e1;
            border-radius: 24px;
            padding: 0 16px;
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s;
            background-color: #f8fafc;
        }

        .stayora-input-field:focus {
            border-color: #1a5276;
            background-color: #ffffff;
            box-shadow: 0 0 0 3px rgba(26, 82, 118, 0.1);
        }

        .stayora-send-btn {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, #1a5276 0%, #2471a3 100%);
            border: none;
            border-radius: 50%;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(26, 82, 118, 0.2);
            transition: all 0.2s;
            flex-shrink: 0;
        }

        .stayora-send-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 16px rgba(26, 82, 118, 0.3);
        }

        .stayora-send-btn svg {
            width: 18px;
            height: 18px;
            fill: currentColor;
            transform: rotate(-45deg) translate(2px, -1px);
        }

        @media (max-width: 480px) {
            .stayora-chat-box {
                bottom: 0 !important;
                right: 0 !important;
                width: 100% !important;
                height: 100% !important;
                max-height: 100% !important;
                border-radius: 0 !important;
            }
            #stayora-chat-circle {
                bottom: 20px;
                right: 20px;
            }
        }
    `;
    document.head.appendChild(styleElement);

    // 2. ویجیٹ کا بنیادی HTML اسٹرکچر بنانا
    const widgetContainer = document.createElement('div');
    widgetContainer.id = 'stayora-chat-widget';
    widgetContainer.innerHTML = `
        <div id="stayora-chat-circle">
            <svg viewBox="0 0 24 24">
                <path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H6l-3 3V4h17v12z"/>
            </svg>
        </div>
        
        <div class="stayora-chat-box">
            <div class="stayora-chat-header">
                <div class="stayora-chat-header-info">
                    <div class="stayora-chat-avatar">🏨</div>
                    <div class="stayora-chat-title">
                        <span class="stayora-chat-title-main">StayOra Bot</span>
                        <span class="stayora-chat-status">آن لائن نمائندہ</span>
                    </div>
                </div>
                <button class="stayora-chat-close" title="بند کریں">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            
            <div class="stayora-chat-body">
                <!-- یہاں یا تو لیڈ فارم کارڈ نظر آئے گا یا چیٹ بوٹ گفتگو -->
            </div>
            
            <div class="stayora-chat-input-box" style="display: none;">
                <form class="stayora-chat-form">
                    <input type="text" class="stayora-input-field" placeholder="اپنا پیغام یہاں لکھیں..." autocomplete="off"/>
                    <button type="submit" class="stayora-send-btn">
                        <svg viewBox="0 0 24 24">
                            <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    `;
    document.body.appendChild(widgetContainer);

    // 3. سلیکٹرز (DOM Elements)
    const chatCircle = document.getElementById('stayora-chat-circle');
    const chatBox = document.querySelector('.stayora-chat-box');
    const chatCloseBtn = document.querySelector('.stayora-chat-close');
    const chatForm = document.querySelector('.stayora-chat-form');
    const chatInput = document.querySelector('.stayora-input-field');
    const chatLogs = document.querySelector('.stayora-chat-body');
    const chatInputBox = document.querySelector('.stayora-chat-input-box');

    // ٹوگل مینو (کھولنا اور بند کرنا)
    chatCircle.addEventListener('click', () => {
        chatBox.classList.add('show');
        chatCircle.style.display = 'none';
        
        // اگر کسٹمر نے پہلے سے فارم فل نہیں کیا تو فورا فارم کارڈ لوڈ کریں
        if (!guestInfo.submitted) {
            renderLeadForm();
        } else {
            chatInput.focus();
        }
    });

    chatCloseBtn.addEventListener('click', () => {
        chatBox.classList.remove('show');
        chatCircle.style.display = 'flex';
    });

    // =======================================================
    // 🌟 فلیش فیچر 1: فارم دکھانے کا طریقہ (Render Lead Form)
    // =======================================================
    function renderLeadForm() {
        chatLogs.innerHTML = `
            <div class="stayora-lead-form-card">
                <div class="stayora-form-header">🏨 خوش آمدید! گفتگو شروع کرنے کے لیے تفصیلات درج کریں:</div>
                <form id="stayora-lead-submit-form">
                    <div class="stayora-form-group">
                        <label>آپ کا نام (لازمی):</label>
                        <input type="text" id="guest-name-field" class="stayora-form-input" placeholder="مثلاً: محمد علی" required />
                    </div>
                    <div class="stayora-form-group">
                        <label>موبائل نمبر (لازمی):</label>
                        <input type="tel" id="guest-phone-field" class="stayora-form-input" placeholder="مثلاً: 03001234567" required />
                    </div>
                    <div class="stayora-form-group">
                        <label>ای میل ایڈریس (آپشنل):</label>
                        <input type="email" id="guest-email-field" class="stayora-form-input" placeholder="example@gmail.com" />
                    </div>
                    <div class="stayora-form-group">
                        <label>شہر (آپشنل):</label>
                        <input type="text" id="guest-city-field" class="stayora-form-input" placeholder="مثلاً: کراچی" />
                    </div>
                    <button type="submit" class="stayora-form-submit">بکنگ ایجنٹ سے بات کریں 💬</button>
                </form>
            </div>
        `;

        // فارم کے سبمٹ ہونے کا ایونٹ
        const leadForm = document.getElementById('stayora-lead-submit-form');
        leadForm.addEventListener('submit', function (e) {
            e.preventDefault();
            
            // کسٹمر کا ڈیٹا محفوظ کرنا
            guestInfo.name = document.getElementById('guest-name-field').value.trim();
            guestInfo.phone = document.getElementById('guest-phone-field').value.trim();
            guestInfo.email = document.getElementById('guest-email-field').value.trim();
            guestInfo.city = document.getElementById('guest-city-field').value.trim();
            guestInfo.submitted = true;

            // بیک اینڈ پی ایچ پی کو ڈیٹا سینڈ کر کے فائل میں محفوظ کرنا
            fetch('/stayora/claude-chat.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'save_lead',
                    name: guestInfo.name,
                    phone: guestInfo.phone,
                    email: guestInfo.email,
                    city: guestInfo.city
                })
            })
            .then(res => res.json())
            .then(response => {
                if (response.success) {
                    console.log("Lead saved successfully on server.");
                } else {
                    console.error("Failed to save lead:", response.error);
                }
            })
            .catch(err => console.error("Error saving lead:", err));

            // چیٹ ایریا کو کلیئر کر کے نارمل چیٹ ونڈو آن کرنا
            chatLogs.innerHTML = '';
            chatInputBox.style.display = 'block'; // ٹیکسٹ ان پٹ ظاہر کریں
            
            // پہلا خوش آمدیدی کارڈ بھیجنا
            generateMessage(`شکریہ **${guestInfo.name} صاحب**! ہم نے آپ کی بنیادی تفصیلات محفوظ کر لی ہیں۔ میں StayOra کراچی کا آفیشل AI بوٹ ہوں۔ بتائیے میں آج آپ کی رہائش، کمروں کی دستیابی یا بکنگ کے حوالے سے کیا مدد کر سکتا ہوں؟`, 'bot');
            chatInput.focus();
        });
    }

    // =======================================================
    // 🌟 مارک ڈاؤن پارسر (Markdown Parser)
    // =======================================================
    function parseMarkdownToHTML(text) {
        if (!text) return "";
        let safeText = text
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;");

        safeText = safeText.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        safeText = safeText.replace(/\*(.*?)\*/g, '<em>$1</em>');

        let lines = safeText.split('\n');
        let inList = false;
        let htmlResult = [];

        for (let i = 0; i < lines.length; i++) {
            let line = lines[i].trim();
            if (line.startsWith('- ') || line.startsWith('* ')) {
                if (!inList) {
                    htmlResult.push('<ul>');
                    inList = true;
                }
                let itemText = line.substring(2);
                htmlResult.push('<li>' + itemText + '</li>');
            } else {
                if (inList) {
                    htmlResult.push('</ul>');
                    inList = false;
                }
                if (line === '') {
                    htmlResult.push('<div style="height: 8px;"></div>');
                } else {
                    htmlResult.push('<p>' + lines[i] + '</p>');
                }
            }
        }
        if (inList) htmlResult.push('</ul>');
        return htmlResult.join('');
    }

    // میسیج اسکرین پر ایڈ کرنے کا فنکشن
    function generateMessage(text, type) {
        const msgRow = document.createElement('div');
        msgRow.className = `stayora-msg-row ${type}`;
        const formattedContent = parseMarkdownToHTML(text);

        msgRow.innerHTML = `<div class="stayora-bubble">${formattedContent}</div>`;
        chatLogs.appendChild(msgRow);
        chatLogs.scrollTop = chatLogs.scrollHeight;
    }

    // لوڈنگ انڈیکیٹر
    function showLoading() {
        const loadingRow = document.createElement('div');
        loadingRow.id = 'chat-loading';
        loadingRow.className = 'stayora-msg-row bot';
        loadingRow.innerHTML = `
            <div class="stayora-bubble">
                <div class="stayora-loader-dots">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        `;
        chatLogs.appendChild(loadingRow);
        chatLogs.scrollTop = chatLogs.scrollHeight;
    }

    function removeLoading() {
        const loader = document.getElementById('chat-loading');
        if (loader) loader.remove();
    }

    // 4. API کو ریکوسٹ بھیجنا اور جواب وصول کرنا
    chatForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const msg = chatInput.value.trim();
        if (msg === '') return;

        // کسٹمر کا پیغام اسکرین پر دکھائیں
        generateMessage(msg, 'user');
        chatInput.value = '';

        // کسٹمر کے پیغامات کا کاؤنٹر بڑھانا
        messageCount++;

        // =======================================================
        // 🌟 فلیش فیچر 2: مینیجر ہینڈ اوور سسٹم (Manager Handoff Trigger)
        // =======================================================
        if (messageCount >= maxFreeQuestions) {
            showLoading();
            setTimeout(() => {
                removeLoading();
                
                // مینیجر ہینڈ اوور کا خوبصورت انٹرفیس شو کروانا
                const handoffText = `**لائیو ٹرانسفر پروٹوکول:** محترم **${guestInfo.name} صاحب**، میں نے اب تک کی آپ کی تمام تفصیلات اور سوالات نوٹ کر لیے ہیں۔ مزید لائیو ڈیلز، بکنگ کی تصدیق یا کسٹم ڈسکاؤنٹس فائنل کرنے کے لیے، میں آپ کو ہمارے لائیو ہوسٹ مینیجر کے پاس منتقل کر رہا ہوں۔ 

براہِ کرم نیچے دیے گئے بٹن پر کلک کر کے واٹس ایپ پر مینیجر سے لائیو چیٹ شروع کریں:`;
                
                generateMessage(handoffText, 'bot');

                // لائیو واٹس ایپ ہینڈ آف بٹن کو شامل کرنا
                const whatsappBtnRow = document.createElement('div');
                whatsappBtnRow.className = 'stayora-msg-row bot';
                
                // کسٹمر کی مینیجر کو بھیجنے والی واٹس ایپ ٹیکسٹ ٹیمپلیٹ
                const waText = encodeURIComponent(`السلام علیکم مینیجر StayOra! میرا نام ${guestInfo.name} ہے، میں ${guestInfo.city || 'کراچی'} سے بات کر رہا ہوں۔ میں نے بوٹ پر تفصیلات دیکھی ہیں اور لائیو کمرہ بک کرنا چاہتا ہوں۔ (فون: ${guestInfo.phone})`);
                const whatsappUrl = `https://wa.me/923282255771?text=${waText}`;

                whatsappBtnRow.innerHTML = `
                    <div class="stayora-bubble" style="width:100%; max-width:100%;">
                        <a href="${whatsappUrl}" target="_blank" class="stayora-whatsapp-handoff-btn">
                            💬 مینیجر سے واٹس ایپ پر لائیو بکنگ فائنل کریں
                        </a>
                    </div>
                `;
                chatLogs.appendChild(whatsappBtnRow);
                chatLogs.scrollTop = chatLogs.scrollHeight;

                // ان پٹ باکس کو مکمل طور پر بلاک اور غائب کرنا تاکہ کسٹمر واٹس ایپ پر جائے
                chatInputBox.style.display = 'none';
            }, 1200);
            return;
        }

        // لوڈنگ شروع کریں
        showLoading();

        // بیک اینڈ پی ایچ پی فائل کو کال کریں (بشمول کسٹمر کے نام کا ٹیگ)
        fetch('/stayora/claude-chat.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                message: msg,
                guest_name: guestInfo.name
            })
        })
        .then(response => response.json())
        .then(data => {
            removeLoading();
            
            let reply = '';
            if (data.content && data.content[0] && data.content[0].text) {
                reply = data.content[0].text;
            } else if (data.error) {
                reply = "⚠️ سسٹم ایرر: " + data.error;
            } else {
                reply = "معذرت، کوئی جواب موصول نہیں ہوا۔";
            }

            generateMessage(reply, 'bot');
        })
        .catch(error => {
            removeLoading();
            generateMessage("⚠️ سرور سے رابطہ منقطع ہو گیا ہے۔ براہ کرم انٹرنیٹ چیک کریں۔", 'bot');
            console.error('Error:', error);
        });
    });
})();
