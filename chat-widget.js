// ✅ StayOra Chat Widget - Rich Text Support Included
(function () {
    // 1. چٹ بوٹ کے لیے CSS اسٹائلز خودکار طریقے سے لوڈ کریں
    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = '/stayora/chat-style.css';
    document.head.appendChild(link);

    // 2. ویجیٹ کا بنیادی HTML اسٹرکچر بنانا
    const widgetContainer = document.createElement('div');
    widgetContainer.id = 'stayora-chat-widget';
    widgetContainer.innerHTML = `
        <div id="chat-circle" class="btn btn-raised">
            <div id="chat-overlay"></div>
            <span class="chat-icon">💬</span>
        </div>
        
        <div class="chat-box">
            <div class="chat-box-header">
                StayOra Bot 🏨
                <span class="chat-box-toggle">❌</span>
            </div>
            <div class="chat-box-body">
                <div class="chat-logs">
                    <div class="chat-msg bot">
                        <div class="cm-msg-text">السلام علیکم! 🏨 StayOra Guest House میں خوش آمدید! میں آپ کی کیا مدد کر سکتا ہوں؟</div>
                    </div>
                </div>
            </div>
            <div class="chat-input-box">
                <form id="chat-submit-form">
                    <input type="text" id="chat-input" placeholder="اپنا پیغام یہاں لکھیں..." autocomplete="off"/>
                    <button type="submit" class="chat-submit" id="chat-submit">بھجیں</button>
                </form>
            </div>
        </div>
    `;
    document.body.appendChild(widgetContainer);

    // 3. سلیکٹرز (DOM Elements)
    const chatCircle = document.getElementById('chat-circle');
    const chatBox = document.querySelector('.chat-box');
    const chatBoxToggle = document.querySelector('.chat-box-toggle');
    const chatForm = document.getElementById('chat-submit-form');
    const chatInput = document.getElementById('chat-input');
    const chatLogs = document.querySelector('.chat-logs');

    // ٹوگل مینو (کھولنا اور بند کرنا)
    chatCircle.addEventListener('click', () => {
        chatBox.classList.add('chat-box-show');
        chatCircle.style.display = 'none';
    });

    chatBoxToggle.addEventListener('click', () => {
        chatBox.classList.remove('chat-box-show');
        chatCircle.style.display = 'block';
    });

    // =======================================================
    // 🌟 انتہائی اہم: لائٹ ویٹ اور محفوظ مارک ڈاؤن پارسر (Markdown Parser)
    // یہ فنکشن سادہ ٹیکسٹ کو ریچ ٹیکسٹ (Rich Text HTML) میں تبدیل کرتا ہے
    // =======================================================
    function parseMarkdownToHTML(text) {
        if (!text) return "";

        // XSS حملوں سے بچنے کے لیے ایچ ٹی ایم ایل کو محفوظ (Escape) کریں
        let safeText = text
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;");

        // 1. بولڈ ٹیکسٹ **text** کو <strong>text</strong> میں تبدیل کریں
        safeText = safeText.replace(/\*\*(.*?)\*\*/g, '<strong style="color: #c0392b; font-weight: bold;">$1</strong>');

        // 2. اٹیلک ٹیکسٹ *text* کو <em>text</em> میں تبدیل کریں
        safeText = safeText.replace(/\*(.*?)\*/g, '<em>$1</em>');

        // 3. بلٹ پوائنٹس (فہرست) کو HTML لسٹ میں تبدیل کریں
        let lines = safeText.split('\n');
        let inList = false;
        let htmlResult = [];

        for (let i = 0; i < lines.length; i++) {
            let line = lines[i].trim();
            
            // اگر لائن - یا * سے شروع ہو تو اسے لسٹ آئٹم بنائیں
            if (line.startsWith('- ') || line.startsWith('* ')) {
                if (!inList) {
                    htmlResult.push('<ul style="margin: 8px 0; padding-right: 20px; list-style-type: disc; direction: rtl; text-align: right;">');
                    inList = true;
                }
                // بلٹ مارکر ہٹا کر لکھیں
                let itemText = line.substring(2);
                htmlResult.push('<li style="margin: 4px 0; color: #555555;">' + itemText + '</li>');
            } else {
                if (inList) {
                    htmlResult.push('</ul>');
                    inList = false;
                }
                
                if (line === '') {
                    htmlResult.push('<div style="height: 8px;"></div>');
                } else {
                    htmlResult.push('<p style="margin: 5px 0; line-height: 1.6; color: #555555;">' + lines[i] + '</p>');
                }
            }
        }
        
        if (inList) {
            htmlResult.push('</ul>');
        }

        return htmlResult.join('');
    }

    // میسیج اسکرین پر ایڈ کرنے کا فنکشن (سپورٹ برائے HTML)
    function generateMessage(text, type) {
        const msgDiv = document.createElement('div');
        msgDiv.className = `chat-msg ${type}`;
        
        // اگر بوٹ کا جواب ہو تو مارک ڈاؤن کو پارس کر کے HTML دکھائیں، ورنہ سادہ ٹیکسٹ
        const formattedContent = (type === 'bot') ? parseMarkdownToHTML(text) : parseMarkdownToHTML(text);

        msgDiv.innerHTML = `
            <div class="cm-msg-text">${formattedContent}</div>
        `;
        
        chatLogs.appendChild(msgDiv);
        
        // اسکرولر کو ہمیشہ نیچے رکھیں
        chatLogs.scrollTop = chatLogs.scrollHeight;
    }

    // لوڈنگ انڈیکیٹر (تین ڈاٹ اینیمیشن)
    function showLoading() {
        const loadingDiv = document.createElement('div');
        loadingDiv.id = 'chat-loading';
        loadingDiv.className = 'chat-msg bot';
        loadingDiv.innerHTML = `
            <div class="cm-msg-text" style="padding: 10px 15px;">
                <span class="dot-loader">.</span>
                <span class="dot-loader">.</span>
                <span class="dot-loader">.</span>
            </div>
        `;
        chatLogs.appendChild(loadingDiv);
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

        // لوڈنگ شروع کریں
        showLoading();

        // بیک اینڈ پی ایچ پی فائل کو کال کریں
        fetch('/stayora/claude-chat.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ message: msg })
        })
        .then(response => response.json())
        .then(data => {
            removeLoading();
            
            // جواب نکالیں اور کلاڈ فارمیٹ یا ڈائریکٹ ٹیکسٹ کو ہینڈل کریں
            let reply = '';
            if (data.content && data.content[0] && data.content[0].text) {
                reply = data.content[0].text;
            } else if (data.error) {
                reply = "⚠️ سسٹم ایرر: " + data.error;
            } else {
                reply = "معذرت، کوئی جواب موصول نہیں ہوا۔";
            }

            // بوٹ کا جواب اسکرین پر پارس کر کے دکھائیں
            generateMessage(reply, 'bot');
        })
        .catch(error => {
            removeLoading();
            generateMessage("⚠️ سرور سے رابطہ منقطع ہو گیا ہے۔ براہ کرم انٹرنیٹ چیک کریں۔", 'bot');
            console.error('Error:', error);
        });
    });
})();
