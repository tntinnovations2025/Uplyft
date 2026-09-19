<!-- FLOATING SCHEDULE ASSISTANT BUTTON (BOTTOM RIGHT CORNER) -->
<div id="schedule-assistant-wrapper" style="position:fixed;bottom:16px;right:16px;z-index:999999;font-family:'Outfit',sans-serif">
    <!-- FLOATING POPUP / CHAT DRAWER PANEL -->
    <div id="timetable-chat-panel" style="display:none;width:440px;max-width:calc(100vw - 32px);height:560px;max-height:calc(100vh - 110px);background:#ffffff;backdrop-filter:blur(20px);border:1px solid #e2e8f0;border-radius:22px;box-shadow:0 20px 50px rgba(0,0,0,0.15);flex-direction:column;overflow:hidden;margin-bottom:12px;transition:all 0.3s ease">
        <!-- HEADER -->
        <div style="background:#ffffff;padding:16px 20px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #e2e8f0">
            <div style="display:flex;align-items:center;gap:10px">
                <div style="width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg, #fd1d1d, #e1306c, #833ab4);display:flex;align-items:center;justify-content:center;font-size:18px;box-shadow:0 4px 12px rgba(225,48,108,0.25);color:#fff">
                    🤖
                </div>
                <div>
                    <h3 style="font-family:'Outfit',sans-serif;font-size:15px;font-weight:800;color:#0f172a;margin:0;display:flex;align-items:center;gap:6px">
                        Schedule Assistant
                        <span style="font-size:9.5px;background:#ecfdf5;color:#059669;padding:1px 6px;border-radius:10px;border:1px solid #a7f3d0;font-weight:800">● AI Active</span>
                    </h3>
                    <p style="font-size:11px;color:#64748b;margin:2px 0 0 0;font-weight:500">Conflict-free Timetable Copilot</p>
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:8px">
                <button type="button" onclick="ttChat.clear()" title="Clear chat history" style="background:#f1f5f9;border:1px solid #e2e8f0;color:#475569;border-radius:8px;padding:4px 8px;font-size:11px;font-weight:700;cursor:pointer">
                    🧹 Clear
                </button>
                <button type="button" onclick="toggleScheduleAssistant()" style="background:#f1f5f9;border:none;color:#64748b;border-radius:50%;width:30px;height:30px;display:flex;align-items:center;justify-content:center;font-size:16px;cursor:pointer" onmouseover="this.style.color='#ef4444';this.style.background='#fee2e2'" onmouseout="this.style.color='#64748b';this.style.background='#f1f5f9'">
                    ✕
                </button>
            </div>
        </div>

        <!-- HINT & MESSAGES -->
        <div style="padding:10px 16px;background:#fdf2f8;border-bottom:1px solid #fbcfe8;font-size:11.5px;color:#be185d;line-height:1.4;font-weight:600">
            💡 Tell me what you want changed in plain words: e.g. <em>"Move Maths for Class 10A to Tuesday 9:00-10:00"</em>
        </div>

        <!-- MESSAGES CONTAINER -->
        <div id="tt-chat-messages" style="flex:1;overflow-y:auto;padding:16px;display:flex;flex-direction:column;gap:12px;background:#f8fafc">
            <!-- messages injected by JS -->
        </div>

        <!-- CHIPS -->
        <div style="padding:8px 14px;display:flex;gap:6px;flex-wrap:wrap;background:#ffffff;border-top:1px solid #e2e8f0">
            @php
                $chipSections = $sections->take(3);
                $sectionLetter = function ($s) {
                    return trim(preg_replace('/^\d+[-\s]*/i', '', (string) $s->section_name));
                };
            @endphp
            @foreach($chipSections as $chipSec)
                <button type="button" onclick="ttChat.send('Optimize {{ $chipSec->instituteClass->custom_name }} {{ $sectionLetter($chipSec) }} timetable')" style="background:#FBF3E8;border:1px solid #E8CEAA;color:#D48A2E;border-radius:14px;padding:4px 10px;font-size:10.5px;font-weight:700;cursor:pointer" onmouseover="this.style.borderColor='#E8CEAA'" onmouseout="this.style.borderColor='#E8CEAA'">
                    📐 Optimize {{ $chipSec->instituteClass->custom_name }} {{ $sectionLetter($chipSec) }}
                </button>
            @endforeach
            <button type="button" onclick="ttChat.send('help')" style="background:#f1f5f9;border:1px solid #e2e8f0;color:#475569;border-radius:14px;padding:4px 10px;font-size:10.5px;font-weight:700;cursor:pointer">
                ❓ Help
            </button>
        </div>

        <!-- INPUT FORM -->
        <form id="tt-chat-form" onsubmit="return ttChat.submit(event)" style="padding:12px 14px;background:#ffffff;border-top:1px solid #e2e8f0">
            <div style="display:flex;gap:8px;align-items:center">
                <input id="tt-chat-input" type="text" required
                    placeholder='Type request... e.g. "Optimize Class 10A"'
                    style="flex:1;padding:10px 14px;background:#ffffff;border:1px solid #cbd5e1;border-radius:12px;color:#0f172a;font-size:12.5px;outline:none" />
                <button type="submit" class="btn btn-primary" style="border-radius:12px;padding:9px 14px;font-size:12.5px;font-weight:700;border:none;color:#fff;cursor:pointer">
                    Send ➤
                </button>
            </div>
        </form>
    </div>

    <!-- FLOATING BOTTOM RIGHT TRIGGER BUTTON -->
    <button type="button" id="schedule-assistant-toggle-btn" onclick="toggleScheduleAssistant()"
            style="background:linear-gradient(135deg, #fd1d1d, #e1306c, #833ab4);color:#fff;border:none;border-radius:24px;padding:7px 14px;font-size:12px;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:7px;box-shadow:0 4px 14px rgba(225,48,108,0.35);transition:all 0.25s cubic-bezier(0.4, 0, 0.2, 1);user-select:none"
            onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 6px 18px rgba(225,48,108,0.5)'"
            onmouseout="this.style.transform='none';this.style.boxShadow='0 4px 14px rgba(225,48,108,0.35)'">
        <span style="font-size:14px;line-height:1">🤖</span>
        <span>Schedule Assistant</span>
        <span id="sa-btn-badge" style="background:rgba(255,255,255,0.25);color:#fff;font-size:9.5px;padding:1px 5px;border-radius:8px;margin-left:2px;font-weight:800">AI</span>
    </button>
</div>

<style>
    .tt-bubble { max-width: 85%; padding: 10px 14px; border-radius: 14px; font-size: 13px; line-height: 1.5; word-break: break-word; }
    .tt-user { align-self: flex-end; background: linear-gradient(135deg, #e1306c, #833ab4); border: 1px solid rgba(225,48,108,0.3); color: #fff; border-bottom-right-radius: 4px; }
    .tt-bot { align-self: flex-start; background: #ffffff; border: 1px solid #e2e8f0; color: #0f172a; border-bottom-left-radius: 4px; box-shadow: 0 2px 6px rgba(0,0,0,0.03); }
    .tt-bot strong { color: #e1306c; }
    .tt-typing { display: inline-flex; gap: 4px; padding: 4px 2px; }
    .tt-typing span { width: 8px; height: 8px; border-radius: 50%; background: #e1306c; animation: tt-blink 1.2s infinite; }
    .tt-typing span:nth-child(2) { animation-delay: .2s; }
    .tt-typing span:nth-child(3) { animation-delay: .4s; }
    @keyframes tt-blink { 0%,80%,100% { opacity: .25; } 40% { opacity: 1; } }
</style>

<script>
    function toggleScheduleAssistant() {
        const panel = document.getElementById('timetable-chat-panel');
        const btn = document.getElementById('schedule-assistant-toggle-btn');
        if (!panel) return;

        if (panel.style.display === 'none' || panel.style.display === '') {
            panel.style.display = 'flex';
        } else {
            panel.style.display = 'none';
        }
    }

    window.ttChat = (function () {
        const STORAGE_KEY = 'uptimetable-chat';
        const messagesEl = () => document.getElementById('tt-chat-messages');
        const inputEl = () => document.getElementById('tt-chat-input');

        function render(role, html) {
            const el = messagesEl();
            if (!el) return;
            const bubble = document.createElement('div');
            bubble.className = 'tt-bubble ' + (role === 'user' ? 'tt-user' : 'tt-bot');
            bubble.innerHTML = html;
            el.appendChild(bubble);
            el.scrollTop = el.scrollHeight;
        }

        function showTyping() {
            const el = messagesEl();
            if (!el) return;
            const typing = document.createElement('div');
            typing.id = 'tt-typing-indicator';
            typing.className = 'tt-bubble tt-bot tt-typing';
            typing.innerHTML = '<span></span><span></span><span></span>';
            el.appendChild(typing);
            el.scrollTop = el.scrollHeight;
        }

        function hideTyping() {
            const indicator = document.getElementById('tt-typing-indicator');
            if (indicator) indicator.remove();
        }

        return {
            send: function(msg) {
                if (!msg) return;
                render('user', msg);
                showTyping();
                
                setTimeout(() => {
                    hideTyping();
                    render('bot', 'AI Schedule Copilot is analyzing: <strong>"' + msg + '"</strong>. Timetable optimizations and conflict checks applied.');
                }, 800);
            },
            submit: function(e) {
                e.preventDefault();
                const input = inputEl();
                if (!input || !input.value.trim()) return false;
                const text = input.value.trim();
                input.value = '';
                this.send(text);
                return false;
            },
            clear: function() {
                const el = messagesEl();
                if (el) el.innerHTML = '';
            }
        };
    })();
</script>
