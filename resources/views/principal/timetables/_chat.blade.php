<div class="card" id="timetable-chat" style="margin-top:24px">
    <div class="card-header">
        <div>
            <div class="card-title">💬 Schedule Assistant</div>
            <div style="font-size:13px;color:var(--text-muted);margin-top:2px">
                Tell me what you want changed in plain words — I'll adjust the schedule with conflict checking.
                E.g. <em>"Class 10A first lecture is at 10–11am and the next is at 3pm, close the gap."</em>
            </div>
        </div>
        <button type="button" class="btn btn-ghost btn-sm" onclick="ttChat.clear()">🧹 Clear</button>
    </div>

    <div id="tt-chat-messages" style="max-height:340px;overflow-y:auto;display:flex;flex-direction:column;gap:10px;padding:4px;margin-bottom:16px">
        <!-- messages injected by JS -->
    </div>

    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:14px">
        @php
            $chipSections = $sections->take(4);
            $sectionLetter = function ($s) {
                return trim(preg_replace('/^\d+[-\s]*/i', '', (string) $s->section_name));
            };
        @endphp
        @foreach($chipSections as $chipSec)
            <button type="button" class="btn btn-ghost btn-sm" onclick="ttChat.send('Optimize {{ $chipSec->instituteClass->custom_name }} {{ $sectionLetter($chipSec) }} timetable')">
                📐 Optimize {{ $chipSec->instituteClass->custom_name }} {{ $sectionLetter($chipSec) }}
            </button>
        @endforeach
        @if($chipSections->isNotEmpty())
            <button type="button" class="btn btn-ghost btn-sm" onclick="ttChat.send('Show {{ $chipSections->first()->instituteClass->custom_name }} {{ $sectionLetter($chipSections->first()) }} schedule')">
                🗓️ Show schedule
            </button>
        @endif
        <button type="button" class="btn btn-ghost btn-sm" onclick="ttChat.send('help')">❓ Help</button>
    </div>

    <form id="tt-chat-form" onsubmit="return ttChat.submit(event)">
        <div style="display:flex;gap:12px;align-items:flex-end">
            <div style="flex:1">
                <textarea id="tt-chat-input" rows="2" required
                    placeholder='Type a request… e.g. "Move Maths for Class 10A to Tuesday 9:00-10:00" or "Optimize Class 10A timetable"'
                    style="width:100%;padding:12px 14px;background:var(--surface2);border:1px solid var(--border);border-radius:8px;color:#fff;font-size:14px;resize:vertical;outline:none"></textarea>
            </div>
            <button type="submit" class="btn btn-primary" style="white-space:nowrap;background:linear-gradient(135deg, #00ced1, #6c63ff)">
                Send ➤
            </button>
        </div>
    </form>
</div>

<style>
    .tt-bubble { max-width: 82%; padding: 12px 16px; border-radius: 14px; font-size: 14px; line-height: 1.55; word-break: break-word; }
    .tt-user { align-self: flex-end; background: linear-gradient(135deg, rgba(108,99,255,0.35), rgba(0,206,209,0.25)); border: 1px solid rgba(108,99,255,0.4); color: #fff; border-bottom-right-radius: 4px; }
    .tt-bot { align-self: flex-start; background: var(--surface2); border: 1px solid var(--border); color: var(--text); border-bottom-left-radius: 4px; }
    .tt-bot strong { color: var(--accent2); }
    .tt-typing { display: inline-flex; gap: 4px; padding: 4px 2px; }
    .tt-typing span { width: 8px; height: 8px; border-radius: 50%; background: var(--accent2); animation: tt-blink 1.2s infinite; }
    .tt-typing span:nth-child(2) { animation-delay: .2s; }
    .tt-typing span:nth-child(3) { animation-delay: .4s; }
    @keyframes tt-blink { 0%,80%,100% { opacity: .25; } 40% { opacity: 1; } }
</style>

<script>
    window.ttChat = (function () {
        const STORAGE_KEY = 'uptimetable-chat';
        const messagesEl = () => document.getElementById('tt-chat-messages');
        const inputEl = () => document.getElementById('tt-chat-input');

        function render(role, html) {
            const el = messagesEl();
            const bubble = document.createElement('div');
            bubble.className = 'tt-bubble ' + (role === 'user' ? 'tt-user' : 'tt-bot');
            bubble.innerHTML = (role === 'bot' ? '🤖&nbsp;' : '🙋&nbsp;') + html;
            el.appendChild(bubble);
            el.scrollTop = el.scrollHeight;
        }

        function typing() {
            const el = messagesEl();
            const bubble = document.createElement('div');
            bubble.className = 'tt-bubble tt-bot';
            bubble.id = 'tt-typing';
            bubble.innerHTML = '<span class="tt-typing"><span></span><span></span><span></span></span>';
            el.appendChild(bubble);
            el.scrollTop = el.scrollHeight;
        }

        function removeTyping() {
            const t = document.getElementById('tt-typing');
            if (t) t.remove();
        }

        function load() {
            let history = [];
            try { history = JSON.parse(sessionStorage.getItem(STORAGE_KEY) || '[]'); } catch (e) {}
            if (history.length === 0 || (history.length === 1 && history[0].role === 'bot' && (history[0].html.includes('Hi!') || history[0].html.includes('schedule assistant')))) {
                history = [{ role: 'bot', html: 'I am here to help you organize, I can:' }];
                sessionStorage.setItem(STORAGE_KEY, JSON.stringify(history));
            }
            history.forEach(function (m) { render(m.role, m.html); });
        }

        function save(role, html) {
            let history = [];
            try { history = JSON.parse(sessionStorage.getItem(STORAGE_KEY) || '[]'); } catch (e) {}
            history.push({ role: role, html: html });
            if (history.length > 80) history = history.slice(-80);
            sessionStorage.setItem(STORAGE_KEY, JSON.stringify(history));
        }

        function send(message) {
            if (!message || !message.trim()) return;

            render('user', escapeHtml(message.trim()));
            save('user', escapeHtml(message.trim()));
            inputEl().value = '';
            typing();

            fetch('{{ route('principal.timetables.chat') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                        || '{{ csrf_token() }}'
                },
                body: JSON.stringify({ message: message.trim() })
            })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    removeTyping();
                    const html = data.message || (data.success ? 'Done.' : 'Something went wrong. Please try again.');
                    render('bot', html);
                    save('bot', html);
                    if (data.changed) {
                        setTimeout(function () { window.location.reload(); }, 900);
                    }
                })
                .catch(function () {
                    removeTyping();
                    render('bot', '⚠️ Sorry, I couldn\'t reach the server. Please try again.');
                });
        }

        function submit(e) {
            e.preventDefault();
            send(inputEl().value);
            return false;
        }

        function clear() {
            sessionStorage.removeItem(STORAGE_KEY);
            messagesEl().innerHTML = '';
            load();
        }

        function escapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }

        return { submit: submit, send: send, clear: clear, load: load };
    })();

    document.addEventListener('DOMContentLoaded', function () {
        ttChat.load();
    });
</script>
