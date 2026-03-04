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

{{-- Admin ID để phân biệt history theo tài khoản --}}
<input type="hidden" id="ai-admin-id" value="{{ Auth::guard('admin')->id() }}">

{{-- Nút bong bóng --}}
<button id="ai-chat-toggle" title="Trợ lý AI" onclick="toggleAIChat()">🤖</button>

{{-- Cửa sổ chat --}}
<div id="ai-chat-widget">
    <div class="ai-chat-header">
        <span>🤖 Trợ lý AI</span>
        <div style="display:flex;gap:8px;align-items:center">
            <span title="Xóa lịch sử chat" onclick="aiClearContext()" style="cursor:pointer;opacity:0.8;font-size:14px" onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0.8">🗑️</span>
            <span class="ai-close-btn" onclick="toggleAIChat()">✕</span>
        </div>
    </div>

    <div id="ai-hint-bar">
        💡 Nhập <kbd>SĐT</kbd> để tra cứu thông tin khách hàng, hoặc nhập câu hỏi bất kỳ.
    </div>

    {{-- Context bar: hiện khi đã load khách hàng --}}
    <div id="ai-context-bar">
        <span id="ai-ctx-label">📋 Đang hỏi về: ...</span>
        <button id="ai-ctx-clear" title="Xóa ngữ cảnh" onclick="aiClearContext()">✕</button>
    </div>

    <div id="ai-chat-body">
        <div class="ai-msg bot" data-no-save="true">Xin chào 👋 Bạn cần tra cứu khách hàng hay hỏi điều gì? Tôi sẵn sàng hỗ trợ.</div>
    </div>
    <div class="ai-chat-footer">
        <input type="text" id="ai-question" placeholder="Nhập câu hỏi..." onkeydown="if(event.key==='Enter') sendAI()">
        <button onclick="sendAI()">Gửi</button>
    </div>
</div>

<script>
    // ── Key localStorage theo tài khoản ──────────────────────────────────
    var _adminId = document.getElementById('ai-admin-id')?.value || 'guest';
    var _storageKey = 'ai_chat_msgs_'  + _adminId;
    var _ctxKey     = 'ai_chat_ctx_'   + _adminId;

    var aiPhoneContext = null;  // { tel }

    // ── Lưu / đọc localStorage ──────────────────────────────────────────
    function saveMsgs() {
        const body = document.getElementById('ai-chat-body');
        const msgs = [];
        body.querySelectorAll('.ai-msg').forEach(el => {
            // Bỏ qua: typing indicator, messages tạm thời, welcome message
            if (el.classList.contains('typing')) return;
            if (el.dataset.noSave) return;
            if (el.innerText === '...' || el.innerText.startsWith('🔍 Đang tra cứu')) return;
            msgs.push({ cls: el.className, text: el.innerText });
        });
        try { localStorage.setItem(_storageKey, JSON.stringify(msgs.slice(-60))); } catch(e){}
    }

    function loadMsgs() {
        try {
            const saved = JSON.parse(localStorage.getItem(_storageKey) || '[]');
            if (!saved.length) return;
            const body = document.getElementById('ai-chat-body');
            body.innerHTML = ''; // xóa welcome mặc định
            saved.forEach(m => {
                const div = document.createElement('div');
                div.className = m.cls;
                div.innerText = m.text;
                body.appendChild(div);
            });
            body.scrollTop = 99999;
        } catch(e){}
    }

    function saveCtx() {
        try {
            if (aiPhoneContext) localStorage.setItem(_ctxKey, JSON.stringify(aiPhoneContext));
            else localStorage.removeItem(_ctxKey);
        } catch(e){}
    }

    function loadCtx() {
        try {
            const c = JSON.parse(localStorage.getItem(_ctxKey) || 'null');
            if (c && c.tel) {
                aiPhoneContext = c;
                document.getElementById('ai-context-bar').classList.add('show');
                document.getElementById('ai-ctx-label').innerText = '📋 Đang hỏi về: ' + c.tel;
            }
        } catch(e){}
    }

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
        // Lưu vào localStorage sau mỗi tin nhắn
        saveMsgs();
        return div;
    }

    function getLeadId() {
        const el = document.getElementById('lead_id');
        return el ? el.value : '';
    }

    // Tìm SĐT Việt Nam trong chuỗi bất kỳ (0 + 9-10 chữ số)
    function extractPhone(str) {
        const m = str.match(/0[3-9][0-9]{8}/);
        return m ? m[0] : null;
    }

    // Tách SĐT ra khỏi câu hỏi
    function extractQuestion(str, phone) {
        return str.replace(phone, '').replace(/^[\s\-,:]+|[\s\-,:]+$/g, '').trim();
    }

    function aiClearContext() {
        aiPhoneContext = null;
        saveCtx();
        // Xóa toàn bộ localStorage của user này
        localStorage.removeItem(_storageKey);
        localStorage.removeItem(_ctxKey);
        document.getElementById('ai-context-bar').classList.remove('show');
        // Reset DOM về welcome message (data-no-save → không được lưu)
        const body = document.getElementById('ai-chat-body');
        body.innerHTML = '<div class="ai-msg bot" data-no-save="true">Xin chào 👋 Bạn cần tra cứu khách hàng hay hỏi điều gì? Tôi sẵn sàng hỗ trợ.</div>';
    }

    function setAIContext(tel, summary) {
        aiPhoneContext = { tel: tel };
        saveCtx();
        document.getElementById('ai-ctx-label').innerText = '📋 Đang hỏi về: ' + tel;
        document.getElementById('ai-context-bar').classList.add('show');
    }

    // ── Khởi tạo: load lại history khi trang load ─────────────────
    loadMsgs();
    loadCtx();

    function sendAI() {
        const input    = document.getElementById('ai-question');
        const rawInput = input.value.trim();
        if (!rawInput) return;

        const cleanInput = rawInput.replace(/\s/g, '');

        // ── Trường hợp 1: Phát hiện SĐT trong câu nhập ────────────────
        // Hỗ trợ cả: chỉ nhập SĐT, hoặc nhập SĐT + câu hỏi cùng lúc
        // VD: "0981263469" hoặc "0981263469 khách cần tư vấn gì?"
        const detectedPhone = extractPhone(rawInput);
        if (detectedPhone) {
            const questionPart = extractQuestion(rawInput, detectedPhone);
            const displayQuestion = questionPart || 'Tóm tắt thông tin khách hàng này';

            aiAppendMsg('user', rawInput);
            input.value = '';
            const typingDiv = aiAppendMsg('typing', '🔍 Đang tra cứu SĐT ' + detectedPhone + '...');

            fetch('/api/ai/phone', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({ tel: detectedPhone, question: displayQuestion })
            })
            .then(async r => {
                const data = await r.json().catch(() => ({}));
                typingDiv.remove();

                if (data.summary) {
                    setAIContext(detectedPhone, data.summary);
                    aiAppendMsg('summary', data.summary);
                }
                const reply = data.answer || data.error || 'Tôi chưa hiểu câu hỏi này, bạn có thể diễn đạt lại không?';
                aiAppendMsg('bot', reply);
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
                const data = await r.json().catch(() => ({}));
                typingDiv.className = 'ai-msg bot';
                typingDiv.innerText = data.answer || data.error || 'Tôi chưa hiểu câu hỏi này, bạn có thể diễn đạt lại không?';
                saveMsgs();
                document.getElementById('ai-chat-body').scrollTop = 99999;
            })
            .catch(() => {
                typingDiv.className = 'ai-msg bot';
                typingDiv.innerText = '❌ Không kết nối được server.';
                saveMsgs();
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
                    typingDiv.innerText = data.answer || data.error || 'Câu hỏi này không có trong dữ liệu, vui lòng nhập số điện thoại để AI tra cứu và trả lời.';
                } catch (e) {
                    typingDiv.className = 'ai-msg bot';
                    typingDiv.innerText = '❌ Lỗi kết nối. Thử lại sau.';
                }
                saveMsgs();
                document.getElementById('ai-chat-body').scrollTop = 99999;
            })
            .catch(() => {
                typingDiv.className = 'ai-msg bot';
                typingDiv.innerText = '❌ Không kết nối được server.';
                saveMsgs();
            });
            return;
        }

        // Không có SĐT, không có lead_id, không có context
        // → Trả lời ngay, không gọi API
        typingDiv.className = 'ai-msg bot';
        typingDiv.innerText = '💡 Câu hỏi này không có trong dữ liệu. Vui lòng nhập số điện thoại khách hàng để Trợ lý AI tra cứu và trả lời.';
        saveMsgs();
        document.getElementById('ai-chat-body').scrollTop = 99999;
    }
</script>
