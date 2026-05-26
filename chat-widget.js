// Toggle Chat Box
function toggleChat() {
  const box = document.getElementById('stayora-chat-box');
  box.style.display = box.style.display === 'none' ? 'flex' : 'none';
}

// Send Message
async function staySend() {
  const input = document.getElementById('stay-input');
  const msgs = document.getElementById('stay-msgs');
  const btn = document.getElementById('stay-send-btn');
  const msg = input.value.trim();
  if (!msg) return;

  // User message دکھائیں
  msgs.innerHTML += `<div class="stay-msg user">${msg}</div>`;
  input.value = '';
  btn.disabled = true;
  btn.textContent = '...';

  // Typing indicator
  msgs.innerHTML += `<div class="stay-msg bot" id="typing">ٹائپ ہو رہا ہے...</div>`;
  msgs.scrollTop = msgs.scrollHeight;

  try {
    const res = await fetch('/stayora/claude-chat.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({message: msg})
    });
    const data = await res.json();
    const reply = data.content?.[0]?.text || 'معذرت، کوئی مسئلہ ہوا۔';
    document.getElementById('typing')?.remove();
    msgs.innerHTML += `<div class="stay-msg bot">${reply}</div>`;
  } catch(e) {
    document.getElementById('typing')?.remove();
    msgs.innerHTML += `<div class="stay-msg bot">Connection error. براہ کرم دوبارہ کوشش کریں۔</div>`;
  }

  btn.disabled = false;
  btn.textContent = 'بھیجیں';
  msgs.scrollTop = msgs.scrollHeight;
}

// Enter key سے بھی بھیجیں
document.addEventListener('DOMContentLoaded', () => {
  document.getElementById('stay-input')?.addEventListener('keypress', (e) => {
    if (e.key === 'Enter') staySend();
  });
});
