function toggleChat() {
  const box = document.getElementById('claude-box');
  box.style.display = box.style.display === 'none' ? 'block' : 'none';
}

async function sendMsg() {
  const input = document.getElementById('claude-input');
  const msgs = document.getElementById('claude-msgs');
  const msg = input.value.trim();
  if (!msg) return;
  msgs.innerHTML += `<p><b>آپ:</b> ${msg}</p>`;
  input.value = '';
  const res = await fetch('claude-chat.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({message: msg})
  });
  const data = await res.json();
  const reply = data.content[0].text;
  msgs.innerHTML += `<p><b>Claude:</b> ${reply}</p>`;
}
