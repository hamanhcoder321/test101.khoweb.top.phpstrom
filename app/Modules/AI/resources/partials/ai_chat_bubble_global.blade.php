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
        width: 380px;
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

    /* === Hint bar === */
    #ai-hint-bar {
        padding: 6px 12px;
        background: #e8f4fd;
        font-size: 11px;
        color: #555;
        border-bottom: 1px solid #d0e8f8;
    }
    #ai-hint-bar kbd {
        background: #d0e8f8;
        border-radius: 3px;
        padding: 1px 4px;
        font-size: 11px;
    }

    /* === Context badge === */
    #ai-context-bar {
        padding: 6px 12px;
        background: #d4edda;
        font-size: 12px;
        color: #155724;
        border-bottom: 1px solid #c3e6cb;
        display: none;
        justify-content: space-between;
        align-items: center;
    }
    #ai-context-bar.show { display: flex; }
    #ai-ctx-clear {
        cursor: pointer;
        color: #888;
        font-size: 16px;
        line-height: 1;
        background: none;
        border: none;
        padding: 0;
    }

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
        max-width: 88%;
        font-size: 13px;
        line-height: 1.5;
        word-break: break-word;
        white-space: pre-wrap;
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
    .ai-msg.summary {
        background: #d4edda;
        border: 1px solid #c3e6cb;
        color: #155724;
        font-size: 12px;
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

    {{-- Hint: gợi ý cách dùng --}}
    <div id="ai-hint-bar">
        💡 Nhập <kbd>SĐT</kbd> để tra cứu khách hàng, hoặc hỏi thẳng câu hỏi.
    </div>

    {{-- Context bar: hiện khi đã load khách hàng --}}
    <div id="ai-context-bar">
        <span id="ai-ctx-label">📋 Đang hỏi về: ...</span>
        <button id="ai-ctx-clear" title="Xóa ngữ cảnh" onclick="aiClearContext()">✕</button>
    </div>

    <div id="ai-chat-body">
        <div class="ai-msg bot">Xin chào 👋 Nhập SĐT khách hàng để tra cứu thông tin, hoặc hỏi trực tiếp về lead hiện tại.</div>
    </div>
    <div class="ai-chat-footer">
        <input type="text" id="ai-question" placeholder="Nhập SĐT hoặc câu hỏi..." onkeydown="if(event.key==='Enter') sendAI()">
        <button onclick="sendAI()">Gửi</button>
    </div>
</div>

<script>
    // ── Lưu context khách hàng hiện tại ──────────────────────────────────
    var aiPhoneContext = null;  // { tel, summary }

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
        const el = document.getElementById('lead_id');
        return el ? el.value : '';
    }

    // Nhận diện SĐT Việt Nam: bắt đầu 0 + 9-10 chữ số
    function isPhoneNumber(str) {
        return /^(0[0-9]{8,10})$/.test(str.replace(/\s/g, ''));
    }

    function aiClearContext() {
        aiPhoneContext = null;
        document.getElementById('ai-context-bar').classList.remove('show');
        aiAppendMsg('bot', '🗑️ Đã xóa ngữ cảnh. Nhập SĐT mới hoặc hỏi câu hỏi khác.');
    }

    function setAIContext(tel, summary) {
        aiPhoneContext = { tel: tel };
        document.getElementById('ai-ctx-label').innerText = '📋 ' + summary;
        document.getElementById('ai-context-bar').classList.add('show');
    }

    function sendAI() {
        const input    = document.getElementById('ai-question');
        const rawInput = input.value.trim();
        if (!rawInput) return;

        const cleanInput = rawInput.replace(/\s/g, '');

        // ── Trường hợp 1: Nhập SĐT → tra cứu khách hàng ──────────────
        if (isPhoneNumber(cleanInput)) {
            aiAppendMsg('user', '🔍 Tra cứu SĐT: ' + rawInput);
            input.value = '';
            const typingDiv = aiAppendMsg('typing', 'Đang tìm thông tin khách hàng...');

            fetch('/api/ai/phone', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({ tel: cleanInput, question: 'Tóm tắt thông tin khách hàng này' })
            })
            .then(async r => {
                const data = await r.json();
                typingDiv.remove();

                if (data.summary) {
                    // Hiện banner context
                    setAIContext(cleanInput, data.summary);
                    // Hiện summary trong chat
                    aiAppendMsg('summary', data.summary);
                }
                aiAppendMsg('bot', data.answer || data.error || 'Không có phản hồi');
                document.getElementById('ai-chat-body').scrollTop = 99999;
            })
            .catch(() => {
                typingDiv.className = 'ai-msg bot';
                typingDiv.innerText = '❌ Không kết nối được server.';
            });
            return;
        }

        // ── Trường hợp 2: Hỏi câu hỏi ──────────────────────────────────
        aiAppendMsg('user', rawInput);
        input.value = '';
        const typingDiv = aiAppendMsg('typing', '...');

        // Nếu đang có context SĐT → dùng /api/ai/phone
        if (aiPhoneContext && aiPhoneContext.tel) {
            fetch('/api/ai/phone', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({ tel: aiPhoneContext.tel, question: rawInput })
            })
            .then(async r => {
                const data = await r.json();
                typingDiv.className = 'ai-msg bot';
                typingDiv.innerText = data.answer || data.error || 'Không có phản hồi';
                document.getElementById('ai-chat-body').scrollTop = 99999;
            })
            .catch(() => {
                typingDiv.className = 'ai-msg bot';
                typingDiv.innerText = '❌ Không kết nối được server.';
            });
            return;
        }

        // Không có context SĐT → kiểm tra lead_id
        const leadId = getLeadId();
        if (leadId) {
            // Đang ở trang lead/edit → dùng /api/ai/lead
            fetch('/api/ai/lead', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({ lead_id: leadId, question: rawInput })
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
            return;
        }

        // Không có SĐT, không có lead_id → hỏi tự do /api/ai/ask
        fetch('/api/ai/ask', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify({ question: rawInput })
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
