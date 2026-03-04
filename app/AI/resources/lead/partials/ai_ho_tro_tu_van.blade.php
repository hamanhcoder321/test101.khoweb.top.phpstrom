<style>
    .ai-chat-box {
        width: 360px;
        border: 1px solid #ddd;
        border-radius: 10px;
        font-family: Arial, sans-serif;
        background: #fff;
    }

    .ai-chat-header {
        padding: 10px;
        background: #0d6efd;
        color: #fff;
        font-weight: bold;
        border-radius: 10px 10px 0 0;
    }

    .ai-chat-body {
        height: 300px;
        overflow-y: auto;
        padding: 10px;
        background: #f7f7f7;
    }

    .msg {
        margin-bottom: 10px;
        padding: 8px 10px;
        border-radius: 6px;
        max-width: 85%;
        line-height: 1.4;
    }

    .msg.user {
        background: #d1e7dd;
        margin-left: auto;
        text-align: right;
    }

    .msg.ai {
        background: #fff;
        border: 1px solid #ddd;
    }

    .ai-chat-footer {
        display: flex;
        border-top: 1px solid #ddd;
    }

    .ai-chat-footer input {
        flex: 1;
        padding: 10px;
        border: none;
        outline: none;
    }

    .ai-chat-footer button {
        padding: 0 15px;
        background: #0d6efd;
        color: #fff;
        border: none;
        cursor: pointer;
    }

</style>
<input type="hidden" id="lead_id" value="41240">

<div class="ai-chat-box">
    <div class="ai-chat-header">
        🤖 Trợ lý AI
    </div>

    <div class="ai-chat-body" id="chatBody">
        <div class="msg ai">
            Xin chào 👋 Tôi là Trợ lý AI. Bạn cần hỗ trợ gì về khách hàng này?
        </div>
    </div>

    <div class="ai-chat-footer">
        <input type="text" id="question" placeholder="Nhập câu hỏi..." onkeydown="if(event.key==='Enter') askAI()">
        <button onclick="askAI()">Gửi</button>
    </div>
</div>

<script>
    // Tìm SĐT Việt Nam trong chuỗi bất kỳ
    function extractPhone(str) {
        const m = str.match(/0[3-9][0-9]{8}/);
        return m ? m[0] : null;
    }

    function extractQuestion(str, phone) {
        return str.replace(phone, '').replace(/^[\s\-,:]+|[\s\-,:]+$/g, '').trim();
    }

    function appendMessage(role, text) {
        const div = document.createElement('div');
        div.className = 'msg ' + role;
        div.innerText = text;
        document.getElementById('chatBody').appendChild(div);
        document.getElementById('chatBody').scrollTop = 99999;
        return div;
    }

    function askAI() {
        let question = document.getElementById('question').value.trim();
        if (!question) return;

        appendMessage('user', question);
        document.getElementById('question').value = '';

        const typingDiv = appendMessage('ai', '...');

        // Phát hiện SĐT trong câu nhập (hỗ trợ "0981... khách cần tư vấn gì?")
        const detectedPhone = extractPhone(question);

        if (detectedPhone) {
            const questionPart = extractQuestion(question, detectedPhone);
            const displayQuestion = questionPart || 'Tóm tắt thông tin khách hàng này';
            typingDiv.innerText = '🔍 Đang tra cứu SĐT ' + detectedPhone + '...';

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
                const text = await r.text();
                try {
                    const data = JSON.parse(text);
                    typingDiv.innerText = data.answer || data.error || 'Tôi chưa hiểu câu hỏi này, bạn có thể diễn đạt lại không?';
                } catch (e) {
                    typingDiv.innerText = '❌ Backend lỗi, xem console.';
                    console.error(text);
                }
                document.getElementById('chatBody').scrollTop = 99999;
            })
            .catch(() => { typingDiv.innerText = '❌ Không kết nối được server.'; });
            return;
        }

        // Không có SĐT → hỏi theo lead_id của trang này
        fetch('/api/ai/lead/edit', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify({
                lead_id: document.getElementById('lead_id').value,
                question: question
            })
        })
        .then(async r => {
            const text = await r.text();
            try {
                const data = JSON.parse(text);
                typingDiv.innerText = data.answer || data.error || 'Tôi chưa hiểu câu hỏi này, bạn có thể diễn đạt lại không?';
            } catch (e) {
                console.error('HTML RESPONSE:', text);
                typingDiv.innerText = '❌ Backend trả HTML, xem console';
            }
            document.getElementById('chatBody').scrollTop = 99999;
        })
        .catch(() => { typingDiv.innerText = '❌ Không kết nối được server.'; });
    }
</script>
