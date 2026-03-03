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
        🤖 Trợ lý Sales
    </div>

    <div class="ai-chat-body" id="chatBody">
        <div class="msg ai">
            Xin chào 👋 Tôi có thể hỗ trợ tư vấn khách hàng này.
        </div>
    </div>

    <div class="ai-chat-footer">
        <input type="text" id="question" placeholder="Hỏi trợ lý sales..." onkeydown="if(event.key==='Enter') askAI()">
        <button onclick="askAI()">Gửi</button>
    </div>
</div>

<script>
    function appendMessage(role, text) {
        const div = document.createElement('div');
        div.className = 'msg ' + role;
        div.innerText = text;
        document.getElementById('chatBody').appendChild(div);
        document.getElementById('chatBody').scrollTop = 99999;
    }

    function askAI() {
        let question = document.getElementById('question').value.trim();
        if (!question) return;

        appendMessage('user', question);
        document.getElementById('question').value = '';

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
                    appendMessage('ai', data.answer || data.error || 'Không có phản hồi');
                } catch (e) {
                    console.error('HTML RESPONSE:', text);
                    appendMessage('ai', '❌ Backend trả HTML, xem console');
                }
            });

    }
</script>
