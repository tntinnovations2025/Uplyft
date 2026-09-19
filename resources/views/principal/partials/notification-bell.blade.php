{{-- ── Principal Real-Time Operations Notification Hub (Powered by Laravel Reverb & Echo) ── --}}
@php
    $authUser = auth()->user();
    $activeInstituteId = $authUser ? (method_exists($authUser, 'getActiveInstituteId') ? $authUser->getActiveInstituteId() : $authUser->institute_id) : 1;
@endphp

<div id="principal-notification-root" style="position:relative;display:inline-block">
    {{-- Bell Trigger Button --}}
    <button type="button" 
            id="principalBellBtn" 
            onclick="event.stopPropagation(); togglePrincipalNotifications()" 
            style="position:relative;width:40px;height:40px;border-radius:12px;background:#F9F8F5;border:1.5px solid #E1DFD7;color:#1B1A17;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:all 0.2s cubic-bezier(0.16,1,0.3,1);box-shadow:0 1px 3px rgba(0,0,0,0.04)"
            title="Real-Time Operations &amp; Notifications">
        
        <svg id="principalBellIcon" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="transition:transform 0.25s cubic-bezier(0.34,1.56,0.64,1)">
            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
        </svg>

        {{-- Unread Badge --}}
        <span id="principalBellBadge" style="display:none;position:absolute;top:-4px;right:-4px;min-width:18px;height:18px;padding:0 4px;border-radius:9999px;background:#D48A2E;color:#1A1200;font-size:10px;font-weight:900;line-height:18px;text-align:center;box-shadow:0 2px 6px rgba(212,138,46,0.45);border:1.5px solid #FFFFFF;animation:bellBadgePop 0.3s cubic-bezier(0.34,1.56,0.64,1)">
            0
        </span>

        {{-- Live Reverb Connection Dot --}}
        <span id="principalReverbStatusDot" style="position:absolute;bottom:2px;right:2px;width:7px;height:7px;border-radius:50%;background:#10b981;border:1.5px solid #ffffff;box-shadow:0 0 6px rgba(16,185,129,0.6)" title="Live via Laravel Reverb"></span>
    </button>

    {{-- Notification Drawer / Popover --}}
    <div id="principalBellPopover" 
         style="display:none;position:absolute;top:calc(100% + 10px);right:0;width:400px;max-width:calc(100vw - 24px);background:#F9F8F5;border:1.5px solid #E1DFD7;border-radius:18px;box-shadow:0 20px 50px -10px rgba(0,0,0,0.18),0 0 0 1px rgba(255,255,255,0.7) inset;z-index:999999;overflow:hidden;animation:appleLiquidSpringPop 0.25s cubic-bezier(0.16,1,0.3,1) forwards">
        
        {{-- Popover Header --}}
        <div style="padding:14px 16px 10px;border-bottom:1px solid #EAE8E1;background:#F2EFEB;display:flex;align-items:center;justify-content:space-between">
            <div style="display:flex;align-items:center;gap:8px">
                <div style="width:26px;height:26px;border-radius:7px;background:rgba(212,138,46,0.15);color:#D48A2E;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                </div>
                <div>
                    <div style="font-family:'Outfit',sans-serif;font-size:13.5px;font-weight:800;color:#1B1A17;line-height:1.1">Operations &amp; Feed</div>
                    <div style="font-size:10px;font-weight:600;color:#68665D;display:flex;align-items:center;gap:5px;margin-top:2px">
                        <span id="reverbConnLabel" style="color:#059669;font-weight:800;display:inline-flex;align-items:center;gap:3px">
                            <span style="width:5px;height:5px;border-radius:50%;background:#10b981;display:inline-block"></span> Live Reverb
                        </span>
                        <span>• Campus Updates</span>
                    </div>
                </div>
            </div>

            <div style="display:flex;align-items:center;gap:6px">
                <button type="button" onclick="toggleChimeMute()" id="chimeToggleBtn" style="padding:4px 7px;border-radius:7px;background:#F9F8F5;border:1px solid #E1DFD7;color:#68665D;font-size:11px;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:4px" title="Sound alerts">
                    <span id="chimeIcon">🔊</span>
                </button>
                <button type="button" onclick="markAllNotificationsRead()" style="padding:4px 9px;border-radius:7px;background:#F9F8F5;border:1px solid #E1DFD7;color:#8A5A10;font-size:11px;font-weight:700;cursor:pointer;transition:all 0.15s">
                    Mark all read
                </button>
            </div>
        </div>

        {{-- Filter Tabs --}}
        <div style="display:flex;align-items:center;gap:4px;padding:8px 12px;background:#F9F8F5;border-bottom:1px solid #EAE8E1;overflow-x:auto;scrollbar-width:none">
            <button type="button" class="principal-feed-tab active" onclick="setFeedCategory('all')" data-cat="all">All</button>
            <button type="button" class="principal-feed-tab" onclick="setFeedCategory('finance')" data-cat="finance">💰 Finance</button>
            <button type="button" class="principal-feed-tab" onclick="setFeedCategory('attendance')" data-cat="attendance">📋 Attendance</button>
            <button type="button" class="principal-feed-tab" onclick="setFeedCategory('exam')" data-cat="exam">📝 Exams</button>
            <button type="button" class="principal-feed-tab" onclick="setFeedCategory('student')" data-cat="student">🎓 Students</button>
            <button type="button" class="principal-feed-tab" onclick="setFeedCategory('staff')" data-cat="staff">👔 Staff</button>
        </div>

        {{-- Notification List Container --}}
        <div id="principalNotificationList" style="max-height:360px;overflow-y:auto;padding:8px;display:flex;flex-direction:column;gap:6px;box-sizing:border-box">
            <div style="padding:24px 12px;text-align:center;color:#8A877E">
                <div style="font-size:24px;margin-bottom:6px">⏳</div>
                <div style="font-size:12px;font-weight:600">Loading live updates...</div>
            </div>
        </div>

        {{-- Popover Footer --}}
        <div style="padding:8px 12px;background:#F2EFEB;border-top:1px solid #EAE8E1;display:flex;align-items:center;justify-content:space-between">
            <span style="font-size:10px;font-weight:700;color:#8A877E" id="feedCountLabel">
                0 updates
            </span>
            <div style="display:flex;align-items:center;gap:6px">
                <button type="button" onclick="triggerTestLiveAlert()" style="padding:3px 8px;border-radius:6px;background:rgba(212,138,46,0.15);border:1px solid rgba(212,138,46,0.35);color:#8A5A10;font-size:10px;font-weight:800;cursor:pointer">
                    ⚡ Test Live Alert
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ── Floating Live Toast Alert Container ── --}}
<div id="principalLiveToastContainer" style="position:fixed;top:74px;right:24px;z-index:9999999;display:flex;flex-direction:column;gap:8px;pointer-events:none"></div>

<style>
    .principal-feed-tab {
        padding: 3px 9px;
        border-radius: 6px;
        background: #F2EFEB;
        border: 1px solid #E1DFD7;
        font-size: 11px;
        font-weight: 700;
        color: #68665D;
        cursor: pointer;
        white-space: nowrap;
        transition: all 0.15s ease;
    }
    .principal-feed-tab:hover {
        background: #EAE8E1;
        color: #1B1A17;
    }
    .principal-feed-tab.active {
        background: #D48A2E !important;
        color: #1A1200 !important;
        border-color: #C07A22 !important;
        font-weight: 800;
    }

    .feed-item-card {
        padding: 9px 11px;
        border-radius: 12px;
        background: #FFFFFF;
        border: 1px solid #E1DFD7;
        display: flex;
        align-items: flex-start;
        gap: 10px;
        cursor: pointer;
        transition: all 0.18s cubic-bezier(0.16,1,0.3,1);
        text-decoration: none;
        color: inherit;
        position: relative;
    }
    .feed-item-card:hover {
        border-color: #D48A2E;
        background: #FFFDF9;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(212,138,46,0.10);
    }
    .feed-item-card.unread {
        background: #FFFBF4;
        border-color: #F8E9D3;
    }
    .feed-item-card.unread::before {
        content: '';
        position: absolute;
        top: 10px;
        right: 10px;
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #D48A2E;
        box-shadow: 0 0 6px rgba(212,138,46,0.6);
    }

    .feed-icon-box {
        width: 32px;
        height: 32px;
        border-radius: 9px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        flex-shrink: 0;
    }
    .feed-icon-box.emerald { background: #E3EFE2; color: #2E6E42; border: 1px solid #C7DEC5; }
    .feed-icon-box.amber   { background: #F8E9D3; color: #8A5A10; border: 1px solid #E8CEAA; }
    .feed-icon-box.purple  { background: #F3E8FF; color: #7E22CE; border: 1px solid #E9D5FF; }
    .feed-icon-box.blue    { background: #E7ECF6; color: #1E40AF; border: 1px solid #CCD7ED; }
    .feed-icon-box.rose    { background: #F6E4E1; color: #A2412C; border: 1px solid #EAC8C1; }
    .feed-icon-box.cyan    { background: #E0F2FE; color: #0369A1; border: 1px solid #BAE6FD; }

    @keyframes bellRingKeyframe {
        0%, 100% { transform: rotate(0); }
        15% { transform: rotate(14deg); }
        30% { transform: rotate(-12deg); }
        45% { transform: rotate(8deg); }
        60% { transform: rotate(-6deg); }
        75% { transform: rotate(3deg); }
    }
    .bell-ring-active {
        animation: bellRingKeyframe 0.7s cubic-bezier(0.36,0.07,0.19,0.97) both !important;
        color: #D48A2E !important;
    }

    @keyframes bellBadgePop {
        0% { transform: scale(0.4); opacity: 0; }
        70% { transform: scale(1.2); }
        100% { transform: scale(1); opacity: 1; }
    }

    .live-toast-card {
        pointer-events: auto;
        width: 340px;
        background: rgba(249,248,245,0.95);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1.5px solid #E1DFD7;
        border-radius: 14px;
        box-shadow: 0 16px 36px -8px rgba(0,0,0,0.22);
        padding: 12px 14px;
        display: flex;
        align-items: flex-start;
        gap: 10px;
        animation: toastSlideIn 0.3s cubic-bezier(0.16,1,0.3,1) forwards;
        cursor: pointer;
        transition: all 0.2s;
    }
    .live-toast-card:hover {
        border-color: #D48A2E;
        transform: translateY(-2px);
    }
    @keyframes toastSlideIn {
        from { opacity: 0; transform: translateX(30px) scale(0.95); }
        to   { opacity: 1; transform: translateX(0) scale(1); }
    }
</style>

<script>
    (function() {
        var activeInstituteId = {{ (int) $activeInstituteId }};
        var activeCategory = 'all';
        var notificationsData = [];
        var unreadCount = 0;
        var chimeMuted = localStorage.getItem('uplyft_chime_muted') === 'true';

        // ── Synthetic Web Audio Chime (Zero External Asset Dependency) ──
        function playChime() {
            if (chimeMuted) return;
            try {
                var AudioContext = window.AudioContext || window.webkitAudioContext;
                if (!AudioContext) return;
                var ctx = new AudioContext();

                var now = ctx.currentTime;
                // Pleasant marimba / glass chord (E6 & B6)
                var osc1 = ctx.createOscillator();
                var osc2 = ctx.createOscillator();
                var gain1 = ctx.createGain();
                var gain2 = ctx.createGain();

                osc1.type = 'sine';
                osc1.frequency.setValueAtTime(1318.51, now); // E6
                gain1.gain.setValueAtTime(0.2, now);
                gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.5);

                osc2.type = 'triangle';
                osc2.frequency.setValueAtTime(1975.53, now + 0.06); // B6
                gain2.gain.setValueAtTime(0.25, now + 0.06);
                gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.65);

                osc1.connect(gain1);
                gain1.connect(ctx.destination);
                osc2.connect(gain2);
                gain2.connect(ctx.destination);

                osc1.start(now);
                osc1.stop(now + 0.55);
                osc2.start(now + 0.06);
                osc2.stop(now + 0.7);
            } catch (e) {
                // Audio context may require user gesture on first load
            }
        }

        window.toggleChimeMute = function() {
            chimeMuted = !chimeMuted;
            localStorage.setItem('uplyft_chime_muted', chimeMuted);
            var icon = document.getElementById('chimeIcon');
            if (icon) icon.textContent = chimeMuted ? '🔇' : '🔊';
        };

        // ── Toggle Popover ──
        window.togglePrincipalNotifications = function() {
            var pop = document.getElementById('principalBellPopover');
            if (!pop) return;
            var isHidden = pop.style.display === 'none' || !pop.style.display;
            pop.style.display = isHidden ? 'block' : 'none';
            if (isHidden) {
                fetchFeed();
            }
        };

        document.addEventListener('click', function(e) {
            var pop = document.getElementById('principalBellPopover');
            var btn = document.getElementById('principalBellBtn');
            if (pop && pop.style.display !== 'none' && !pop.contains(e.target) && !btn.contains(e.target)) {
                pop.style.display = 'none';
            }
        });

        // ── Filter Tab Change ──
        window.setFeedCategory = function(cat) {
            activeCategory = cat;
            document.querySelectorAll('.principal-feed-tab').forEach(function(btn) {
                if (btn.getAttribute('data-cat') === cat) {
                    btn.classList.add('active');
                } else {
                    btn.classList.remove('active');
                }
            });
            renderFeed();
        };

        // ── Render Feed Items ──
        function renderFeed() {
            var listEl = document.getElementById('principalNotificationList');
            var countLabel = document.getElementById('feedCountLabel');
            if (!listEl) return;

            var filtered = notificationsData.filter(function(item) {
                if (activeCategory === 'all') return true;
                return item.category === activeCategory;
            });

            if (countLabel) {
                countLabel.textContent = filtered.length + ' update' + (filtered.length === 1 ? '' : 's');
            }

            if (filtered.length === 0) {
                listEl.innerHTML = '<div style="padding:28px 14px;text-align:center;color:#8A877E">' +
                    '<div style="font-size:26px;margin-bottom:6px">✨</div>' +
                    '<div style="font-size:13px;font-weight:800;color:#1B1A17">All caught up!</div>' +
                    '<div style="font-size:11px;margin-top:2px">No operations recorded under this filter.</div>' +
                    '</div>';
                return;
            }

            var html = '';
            filtered.forEach(function(item) {
                var isUnread = !item.read_at;
                var colorClass = item.color || 'amber';
                var iconSymbol = getCategoryIconSvg(item.category, item.icon);

                html += '<a href="' + (item.action_url || 'javascript:void(0)') + '" onclick="handleNotificationClick(event, \'' + item.id + '\', \'' + (item.action_url || '') + '\')" class="feed-item-card ' + (isUnread ? 'unread' : '') + '">' +
                    '<div class="feed-icon-box ' + colorClass + '">' + iconSymbol + '</div>' +
                    '<div style="flex:1;min-width:0">' +
                        '<div style="display:flex;align-items:center;justify-content:space-between;gap:6px">' +
                            '<div style="font-size:12px;font-weight:800;color:#1B1A17;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">' + escapeHtml(item.title) + '</div>' +
                            '<span style="font-size:10px;font-weight:600;color:#8A877E;flex-shrink:0">' + escapeHtml(item.created_at_human || 'Just now') + '</span>' +
                        '</div>' +
                        '<div style="font-size:11.5px;color:#68665D;line-height:1.35;margin-top:2px">' + escapeHtml(item.message) + '</div>' +
                        (item.actor_name ? (
                            '<div style="margin-top:4px;display:flex;align-items:center;gap:5px">' +
                                '<span style="font-size:9.5px;font-weight:800;background:#F2EFEB;border:1px solid #E1DFD7;padding:1px 6px;border-radius:6px;color:#1B1A17">' +
                                    (item.actor_role ? escapeHtml(item.actor_role) + ': ' : '') + escapeHtml(item.actor_name) +
                                '</span>' +
                            '</div>'
                        ) : '') +
                    '</div>' +
                '</a>';
            });

            listEl.innerHTML = html;
        }

        function getCategoryIconSvg(cat, iconName) {
            switch(cat) {
                case 'finance':
                    return '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1-2-1Z"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/><path d="M12 6v12"/></svg>';
                case 'attendance':
                    return '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="8" height="4" x="8" y="2" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="m9 14 2 2 4-4"/></svg>';
                case 'exam':
                    return '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>';
                case 'student':
                    return '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="22" x2="16" y1="11" y2="11"/></svg>';
                case 'staff':
                    return '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>';
                default:
                    return '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>';
            }
        }

        function escapeHtml(str) {
            if (!str) return '';
            return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        // ── Fetch Feed from Server ──
        function fetchFeed() {
            var csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            fetch('{{ route("principal.notifications.feed") }}', {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf || ''
                }
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.success) {
                    notificationsData = data.notifications || [];
                    updateUnreadBadge(data.unread_count || 0);
                    renderFeed();
                }
            })
            .catch(function(err) {
                console.warn('Notifications fetch notice:', err);
            });
        }

        function updateUnreadBadge(count) {
            unreadCount = count;
            var badge = document.getElementById('principalBellBadge');
            if (badge) {
                if (count > 0) {
                    badge.style.display = 'block';
                    badge.textContent = count > 99 ? '99+' : count;
                } else {
                    badge.style.display = 'none';
                }
            }
        }

        // ── Mark Read Actions ──
        window.handleNotificationClick = function(e, id, url) {
            markSingleRead(id);
            if (!url || url === 'javascript:void(0)') {
                e.preventDefault();
            }
        };

        window.markSingleRead = function(id) {
            var item = notificationsData.find(function(n) { return n.id === id; });
            if (item && !item.read_at) {
                item.read_at = new Date().toISOString();
                updateUnreadBadge(Math.max(0, unreadCount - 1));
                renderFeed();

                var csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                fetch('{{ url("principal/notifications") }}/' + id + '/mark-read', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf || '' }
                });
            }
        };

        window.markAllNotificationsRead = function() {
            notificationsData.forEach(function(n) { n.read_at = new Date().toISOString(); });
            updateUnreadBadge(0);
            renderFeed();

            var csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            fetch('{{ route("principal.notifications.mark-all-read") }}', {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf || '' }
            });
        };

        // ── Trigger Live Toast & Bell Animation on Incoming Reverb Event ──
        function handleIncomingNotification(payload) {
            // Animate bell
            var icon = document.getElementById('principalBellIcon');
            if (icon) {
                icon.classList.remove('bell-ring-active');
                void icon.offsetWidth; // trigger reflow
                icon.classList.add('bell-ring-active');
                setTimeout(function() { icon.classList.remove('bell-ring-active'); }, 1000);
            }

            // Play Chime
            playChime();

            // Insert into data
            notificationsData.unshift(payload);
            updateUnreadBadge(unreadCount + 1);
            renderFeed();

            // Show floating popup toast
            showLiveToast(payload);
        }

        function showLiveToast(item) {
            var container = document.getElementById('principalLiveToastContainer');
            if (!container) return;

            var toast = document.createElement('div');
            toast.className = 'live-toast-card';
            var iconSvg = getCategoryIconSvg(item.category, item.icon);
            var colorClass = item.color || 'amber';

            toast.innerHTML = '<div class="feed-icon-box ' + colorClass + '">' + iconSvg + '</div>' +
                '<div style="flex:1;min-width:0">' +
                    '<div style="font-size:12.5px;font-weight:800;color:#1B1A17">' + escapeHtml(item.title) + '</div>' +
                    '<div style="font-size:11.5px;color:#68665D;line-height:1.3;margin-top:2px">' + escapeHtml(item.message) + '</div>' +
                    '<div style="margin-top:3px;font-size:9.5px;color:#8A877E;font-weight:700">⚡ Live Reverb Alert • Just now</div>' +
                '</div>';

            toast.addEventListener('click', function() {
                if (item.action_url) {
                    window.location.href = item.action_url;
                }
                toast.remove();
            });

            container.appendChild(toast);

            setTimeout(function() {
                toast.style.transition = 'all 0.3s cubic-bezier(0.16,1,0.3,1)';
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(-10px) scale(0.95)';
                setTimeout(function() { toast.remove(); }, 300);
            }, 5500);
        }

        // ── Test Alert Trigger ──
        window.triggerTestLiveAlert = function(type) {
            type = type || 'fee';
            var csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            fetch('{{ route("principal.notifications.test-broadcast") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf || ''
                },
                body: JSON.stringify({ type: type })
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                // Notice logged
            });
        };

        // ── Connect to Laravel Echo / Reverb WebSocket ──
        function initReverbListener() {
            var dot = document.getElementById('principalReverbStatusDot');
            var connLabel = document.getElementById('reverbConnLabel');

            if (window.Echo) {
                var channelName = 'institute.' + activeInstituteId + '.principal';
                try {
                    window.Echo.private(channelName)
                        .listen('.operation.performed', function(e) {
                            handleIncomingNotification(e);
                        })
                        .listen('PrincipalOperationNotification', function(e) {
                            handleIncomingNotification(e);
                        });

                    if (dot) dot.style.background = '#10b981';
                    if (connLabel) connLabel.innerHTML = '<span style="width:5px;height:5px;border-radius:50%;background:#10b981;display:inline-block"></span> Live Reverb';
                } catch (err) {
                    console.warn('Reverb private channel connect attempt:', err);
                }
            } else {
                if (dot) dot.style.background = '#f59e0b';
                if (connLabel) connLabel.innerHTML = '<span style="width:5px;height:5px;border-radius:50%;background:#f59e0b;display:inline-block"></span> Polling Mode';
            }
        }

        // Initialize on DOM load
        document.addEventListener('DOMContentLoaded', function() {
            var icon = document.getElementById('chimeIcon');
            if (icon) icon.textContent = chimeMuted ? '🔇' : '🔊';

            fetchFeed();
            initReverbListener();

            // Fallback sync polling every 30 seconds
            setInterval(fetchFeed, 30000);
        });
    })();
</script>
