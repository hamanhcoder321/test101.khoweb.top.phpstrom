{{-- ===== FLOATING AI CHAT BUBBLE (GLOBAL - xuất hiện mọi trang) ===== --}}
<style>
    /* === Nút mở chat === */
    #ai-chat-toggle {
        position: fixed;
        bottom: 24px;
        right: 24px;
        width: 54px;
        height: 54px;
        border-radius: 50%;
        background: #0d6efd;
        color: #fff;
        font-size: 24px;
        border: none;
        box-shadow: 0 4px 16px rgba(0,0,0,0.25);
        cursor: pointer;
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s;
    }
    #ai-chat-toggle:hover { background: #0b5ed7; }

    /* === Hộp chat === */
    #ai-chat-widget {
        position: fixed;
        bottom: 88px;
        right: 24px;
        width: 360px;
        border-radius: 12px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.18);
        font-family: Arial, sans-serif;
        background: #fff;
        z-index: 9998;
        display: none;
        flex-direction: column;
        overflow: hidden;
        border: 1px solid #ddd;
    }
    #ai-chat-widget.open { display: flex; }

    .ai-chat-header {
        padding: 12px 14px;
        background: #0d6efd;
        color: #fff;
        font-weight: bold;
        font-size: 15px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .ai-chat-header .ai-close-btn {
        cursor: pointer;
        font-size: 18px;
        line-height: 1;
        opacity: 0.8;
    }
    .ai-chat-header .ai-close-btn:hover { opacity: 1; }

    #ai-chat-body {
        height: 300px;
        overflow-y: auto;
        padding: 12px;
        background: #f7f7f7;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .ai-msg {
        padding: 8px 12px;
        border-radius: 8px;
        max-width: 85%;
        font-size: 13px;
        line-height: 1.5;
        word-break: break-word;
    }
    .ai-msg.user {
        background: #d1e7dd;
        margin-left: auto;
        text-align: right;
    }
    .ai-msg.bot {
        background: #fff;
        border: 1px solid #ddd;
    }
    .ai-msg.typing {
        color: #888;
        font-style: italic;
        background: #fff;
        border: 1px solid #ddd;
    }

    .ai-chat-footer {
        display: flex;
        border-top: 1px solid #ddd;
        background: #fff;
    }
    .ai-chat-footer input {
        flex: 1;
        padding: 10px 12px;
        border: none;
        outline: none;
        font-size: 13px;
    }
    .ai-chat-footer button {
        padding: 0 16px;
        background: #0d6efd;
        color: #fff;
        border: none;
        cursor: pointer;
        font-size: 13px;
        font-weight: bold;
    }
    .ai-chat-footer button:hover { background: #0b5ed7; }
</style>

{{-- Nút bong bóng --}}
<button id="ai-chat-toggle" title="Trợ lý Sales AI" onclick="toggleAIChat()">🤖</button>

{{-- Cửa sổ chat --}}
<div id="ai-chat-widget">
    <div class="ai-chat-header">
        <span>🤖 Trợ lý Sales</span>
        <span class="ai-close-btn" onclick="toggleAIChat()">✕</span>
    </div>
    <div id="ai-chat-body">
        <div class="ai-msg bot">Xin chào 👋 Tôi có thể hỗ trợ tư vấn khách hàng này.</div>
    </div>
    <div class="ai-chat-footer">
        <input type="text" id="ai-question" placeholder="Hỏi trợ lý sales..." onkeydown="if(event.key==='Enter') sendAI()">
        <button onclick="sendAI()">Gửi</button>
    </div>
</div>

<script>
    function toggleAIChat() {
        const w = document.getElementById('ai-chat-widget');
        w.classList.toggle('open');
        if (w.classList.contains('open')) {
            document.getElementById('ai-question').focus();
        }
    }

    function aiAppendMsg(role, text) {
        const body = document.getElementById('ai-chat-body');
        const div  = document.createElement('div');
        div.className = 'ai-msg ' + role;
        div.innerText  = text;
        body.appendChild(div);
        body.scrollTop = 99999;
        return div;
    }

    function getLeadId() {
        // Lấy lead_id từ hidden input (nếu đang ở trang lead/edit)
        const el = document.getElementById('lead_id');
        return el ? el.value : '';
    }

    function sendAI() {
        const input = document.getElementById('ai-question');
        const question = input.value.trim();
        if (!question) return;

        aiAppendMsg('user', question);
        input.value = '';

        const typingDiv = aiAppendMsg('typing', '...');

        fetch('/api/ai/lead', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify({
                lead_id: getLeadId(),
                question: question
            })
        })
        .then(async r => {
            const text = await r.text();
            try {
                const data = JSON.parse(text);
                typingDiv.className = 'ai-msg bot';
                typingDiv.innerText = data.answer || data.error || 'Không có phản hồi';
            } catch (e) {
                typingDiv.className = 'ai-msg bot';
                typingDiv.innerText = '❌ Lỗi kết nối. Thử lại sau.';
            }
            document.getElementById('ai-chat-body').scrollTop = 99999;
        })
        .catch(() => {
            typingDiv.className = 'ai-msg bot';
            typingDiv.innerText = '❌ Không kết nối được server.';
        });
    }
</script>
