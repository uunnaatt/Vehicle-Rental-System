document.addEventListener('DOMContentLoaded', () => {
    const launcher = document.getElementById('ai-chatbot-launcher');
    const panel = document.getElementById('ai-chatbot-panel');
    const closeBtn = document.getElementById('ai-chatbot-close');
    const form = document.getElementById('ai-chatbot-form');
    const input = document.getElementById('ai-chatbot-input');
    const messages = document.getElementById('ai-chatbot-messages');
    const suggestions = document.getElementById('ai-chatbot-suggestions');
    const status = document.getElementById('ai-chatbot-status');

    if (!launcher || !panel || !form || !input || !messages) {
        return;
    }

    const storageKey = 'sawari_ai_chat_history';
    let history = [];

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, (char) => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        }[char]));
    }

    function setStatus(text) {
        if (status) {
            status.textContent = text || '';
        }
    }

    function saveHistory() {
        localStorage.setItem(storageKey, JSON.stringify(history.slice(-14)));
    }

    function loadHistory() {
        try {
            const stored = JSON.parse(localStorage.getItem(storageKey) || '[]');
            if (Array.isArray(stored)) {
                history = stored.filter((item) => item && item.role && item.content);
            }
        } catch (e) {
            history = [];
        }
    }

    function createMessageBubble(role, content) {
        const wrap = document.createElement('div');
        wrap.className = `ai-chatbot-message ${role === 'user' ? 'user' : 'assistant'}`;
        wrap.innerHTML = `<div class="ai-chatbot-bubble">${escapeHtml(content)}</div>`;
        return wrap;
    }

    function renderHistory() {
        messages.innerHTML = '';
        if (!history.length) {
            messages.innerHTML = `
                <div class="ai-chatbot-message assistant">
                    <div class="ai-chatbot-bubble">
                        Hi, I’m your SAWARI assistant. Ask me about bookings, vehicles, pricing, locations, tracking, collateral, or support.
                    </div>
                </div>
            `;
            return;
        }

        history.forEach((item) => {
            messages.appendChild(createMessageBubble(item.role, item.content));
        });
        messages.scrollTop = messages.scrollHeight;
    }

    function appendMessage(role, content) {
        history.push({ role, content });
        messages.appendChild(createMessageBubble(role, content));
        messages.scrollTop = messages.scrollHeight;
        saveHistory();
    }

    function setOpen(isOpen) {
        panel.classList.toggle('open', isOpen);
        launcher.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        if (isOpen) {
            input.focus();
        }
    }

    async function askAssistant(question) {
        const trimmed = String(question || '').trim();
        if (!trimmed) return;

        appendMessage('user', trimmed);
        input.value = '';
        setStatus('Thinking...');

        try {
            const response = await fetch('../api/ai/chat.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    message: trimmed,
                    history: history.slice(-8)
                })
            });

            const data = await response.json();
            if (!response.ok) {
                throw new Error(data.message || 'Assistant unavailable');
            }

            appendMessage('assistant', data.message || 'I could not prepare a reply.');
            renderSuggestions(data.suggestions || []);
            if (data.engine === 'mistral-rag') {
                setStatus('Powered by Mistral + SAWARI retrieval');
            } else if (data.scope === 'out_of_scope') {
                setStatus('Out-of-scope guard active');
            } else {
                setStatus('Powered by SAWARI retrieval');
            }
        } catch (error) {
            appendMessage('assistant', 'I could not answer that right now. Please try another SAWARI-related question.');
            setStatus('Temporary problem');
        }
    }

    function renderSuggestions(items) {
        if (!suggestions) return;
        suggestions.innerHTML = '';
        items.slice(0, 4).forEach((item) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'ai-chatbot-chip';
            btn.textContent = item;
            btn.addEventListener('click', () => askAssistant(item));
            suggestions.appendChild(btn);
        });
    }

    launcher.addEventListener('click', () => {
        setOpen(!panel.classList.contains('open'));
    });

    if (closeBtn) {
        closeBtn.addEventListener('click', () => setOpen(false));
    }

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        askAssistant(input.value);
    });

    loadHistory();
    renderHistory();
    renderSuggestions([
        'How do I book a vehicle?',
        'What documents are accepted?',
        'Which locations have available SUVs?',
        'Can I track my active rental?'
    ]);
});
