{{-- ── Student & Teacher Real-Time Notification Bell (Powered by Laravel Reverb & Echo) ── --}}
@php
    $authUser = auth()->user();
    $activeInstituteId = $authUser ? (method_exists($authUser, 'getActiveInstituteId') ? $authUser->getActiveInstituteId() : ($authUser->institute_id ?? 1)) : 1;
@endphp

@auth
<div id="portal-notification-root" style="position:relative;display:inline-block">
    {{-- Bell Trigger Button --}}
    <button type="button" 
            id="portalBellBtn" 
            onclick="event.stopPropagation(); togglePortalNotifications()" 
            style="position:relative;width:38px;height:38px;border-radius:11px;background:#F9F8F5;border:1.5px solid #E1DFD7;color:#1B1A17;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:all 0.2s cubic-bezier(0.16,1,0.3,1);box-shadow:0 1px 3px rgba(0,0,0,0.04)"
            title="Notifications &amp; Alerts">
        
        <svg id="portalBellIcon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="transition:transform 0.25s cubic-bezier(0.34,1.56,0.64,1)">
            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
        </svg>

        {{-- Unread Badge --}}
        <span id="portalBellBadge" style="display:none;position:absolute;top:-4px;right:-4px;min-width:18px;height:18px;padding:0 4px;border-radius:9999px;background:#D48A2E;color:#1A1200;font-size:10px;font-weight:900;line-height:18px;text-align:center;box-shadow:0 2px 6px rgba(212,138,46,0.45);border:1.5px solid #FFFFFF;animation:portalBadgePop 0.3s cubic-bezier(0.34,1.56,0.64,1)">
            0
        </span>

        {{-- Live Reverb Connection Dot --}}
        <span id="portalReverbStatusDot" style="position:absolute;bottom:2px;right:2px;width:7px;height:7px;border-radius:50%;background:#10b981;border:1.5px solid #ffffff;box-shadow:0 0 6px rgba(16,185,129,0.6)" title="Connected to Laravel Reverb"></span>
    </button>

    {{-- Notification Drawer / Popover --}}
    <div id="portalBellPopover" 
         style="display:none;position:absolute;top:calc(100% + 10px);right:0;width:390px;max-width:calc(100vw - 24px);background:#F9F8F5;border:1.5px solid #E1DFD7;border-radius:18px;box-shadow:0 20px 50px -10px rgba(0,0,0,0.18),0 0 0 1px rgba(255,255,255,0.7) inset;z-index:999999;overflow:hidden;animation:portalLiquidSpringPop 0.25s cubic-bezier(0.16,1,0.3,1) forwards">
        
        {{-- Popover Header --}}
        <div style="padding:14px 16px 10px;border-bottom:1px solid #EAE8E1;background:#F2EFEB;display:flex;align-items:center;justify-content:space-between">
            <div style="display:flex;align-items:center;gap:8px">
                <div style="width:26px;height:26px;border-radius:7px;background:rgba(212,138,46,0.15);color:#D48A2E;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800">
                    🔔
                </div>
                <div>
                    <div style="font-family:'Manrope',sans-serif;font-size:13.5px;font-weight:800;color:#1B1A17;line-height:1.1">Notifications &amp; Alerts</div>
                    <div style="font-size:10px;font-weight:600;color:#68665D;display:flex;align-items:center;gap:5px;margin-top:2px">
                        <span id="portalReverbConnLabel" style="color:#059669;font-weight:800;display:inline-flex;align-items:center;gap:3px">
                            <span style="width:5px;height:5px;border-radius:50%;background:#10b981;display:inline-block"></span> Live Reverb
                        </span>
                        <span>• Academic Feed</span>
                    </div>
                </div>
            </div>

            <div style="display:flex;align-items:center;gap:6px">
                <button type="button" onclick="togglePortalChimeMute()" id="portalChimeToggleBtn" style="padding:4px 7px;border-radius:7px;background:#F9F8F5;border:1px solid #E1DFD7;color:#68665D;font-size:11px;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:4px" title="Sound alerts">
                    <span id="portalChimeIcon">🔊</span>
                </button>
                <button type="button" onclick="markAllPortalNotificationsRead()" style="padding:4px 9px;border-radius:7px;background:#F9F8F5;border:1px solid #E1DFD7;color:#8A5A10;font-size:11px;font-weight:700;cursor:pointer;transition:all 0.15s">
                    Mark all read
                </button>
            </div>
        </div>

        {{-- Filter Tabs --}}
        <div style="display:flex;align-items:center;gap:4px;padding:8px 12px;background:#F9F8F5;border-bottom:1px solid #EAE8E1;overflow-x:auto;scrollbar-width:none">
            <button type="button" class="portal-feed-tab active" onclick="setPortalFeedCategory('all')" data-cat="all">All</button>
            <button type="button" class="portal-feed-tab" onclick="setPortalFeedCategory('academics')" data-cat="academics">📚 Diary</button>
            <button type="button" class="portal-feed-tab" onclick="setPortalFeedCategory('finance')" data-cat="finance">💰 Fees</button>
            <button type="button" class="portal-feed-tab" onclick="setPortalFeedCategory('attendance')" data-cat="attendance">📋 Attendance</button>
            <button type="button" class="portal-feed-tab" onclick="setPortalFeedCategory('exam')" data-cat="exam">📝 Exams</button>
        </div>

        {{-- Notification List Container --}}
        <div id="portalNotificationList" style="max-height:360px;overflow-y:auto;padding:8px;display:flex;flex-direction:column;gap:6px;box-sizing:border-box">
            <div style="padding:24px 12px;text-align:center;color:#8A877E">
                <div style="font-size:24px;margin-bottom:6px">⏳</div>
                <div style="font-size:12px;font-weight:600">Loading notifications...</div>
            </div>
        </div>

        {{-- Popover Footer --}}
        <div style="padding:8px 12px;background:#F2EFEB;border-top:1px solid #EAE8E1;display:flex;align-items:center;justify-content:space-between">
            <span style="font-size:10px;font-weight:700;color:#8A877E" id="portalFeedCountLabel">
                0 updates
            </span>
            <div style="display:flex;align-items:center;gap:6px">
                <button type="button" onclick="triggerTestPortalAlert()" style="padding:3px 8px;border-radius:6px;background:rgba(212,138,46,0.15);border:1px solid rgba(212,138,46,0.35);color:#8A5A10;font-size:10px;font-weight:800;cursor:pointer">
                    ⚡ Test Live Alert
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ── Floating Live Toast Alert Container ── --}}
<div id="portalLiveToastContainer" style="position:fixed;top:74px;right:24px;z-index:9999999;display:flex;flex-direction:column;gap:8px;pointer-events:none"></div>

<style>
    .portal-feed-tab {
        padding: 3px 9px;
        border-radius: 6px;
        background: #F2EFEB;
        border: 1px solid #E1DFD7;
        font-size: 11px;
        font-weight: 700;
        color: #68665D;
        cursor: pointer;
        transition: all 0.15s ease;
        white-space: nowrap;
    }
    .portal-feed-tab:hover {
        background: #EAE8E1;
        color: #1B1A17;
    }
    .portal-feed-tab.active {
        background: #D48A2E;
        color: #1A1200;
        border-color: #C07A22;
        box-shadow: 0 1px 4px rgba(212,138,46,0.3);
    }

    .portal-noti-card {
        padding: 10px 12px;
        border-radius: 12px;
        background: #FFFFFF;
        border: 1px solid #EAE8E1;
        display: flex;
        align-items: flex-start;
        gap: 10px;
        transition: all 0.18s ease;
        text-decoration: none;
        color: inherit;
        position: relative;
    }
    .portal-noti-card:hover {
        border-color: #D48A2E;
        background: #FDFBF7;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    .portal-noti-card.unread {
        background: #FEFDFB;
        border-left: 3.5px solid #D48A2E;
    }

    .portal-toast-alert {
        background: #F9F8F5;
        border: 1.5px solid #D48A2E;
        border-radius: 14px;
        padding: 12px 16px;
        box-shadow: 0 16px 36px -6px rgba(0,0,0,0.22);
        display: flex;
        align-items: center;
        gap: 12px;
        width: 340px;
        max-width: 90vw;
        pointer-events: auto;
        animation: portalToastSlideIn 0.3s cubic-bezier(0.16,1,0.3,1) forwards;
    }

    @keyframes portalBadgePop {
        0% { transform: scale(0.4); opacity: 0; }
        70% { transform: scale(1.2); }
        100% { transform: scale(1); opacity: 1; }
    }
    @keyframes portalLiquidSpringPop {
        from { opacity: 0; transform: translateY(-8px) scale(0.96); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }
    @keyframes portalToastSlideIn {
        from { opacity: 0; transform: translateX(20px) scale(0.95); }
        to { opacity: 1; transform: translateX(0) scale(1); }
    }
</style>

<script>
(function() {
    const CURRENT_USER_ID = {{ (int) ($authUser->id ?? 0) }};
    const FEED_URL = "{{ route('portal.notifications.feed') }}";
    const MARK_READ_URL = "{{ url('/portal-notifications') }}";
    const MARK_ALL_READ_URL = "{{ route('portal.notifications.mark-all-read') }}";
    const TEST_ALERT_URL = "{{ route('portal.notifications.test-broadcast') }}";

    let portalNotifications = [];
    let portalCurrentCategory = 'all';
    let portalChimeMuted = localStorage.getItem('uplyft_portal_chime_muted') === 'true';

    window.togglePortalNotifications = function() {
        const popover = document.getElementById('portalBellPopover');
        if (!popover) return;
        const isVisible = popover.style.display === 'block';
        popover.style.display = isVisible ? 'none' : 'block';
        if (!isVisible) {
            fetchPortalNotifications();
        }
    };

    document.addEventListener('click', function(e) {
        const root = document.getElementById('portal-notification-root');
        const popover = document.getElementById('portalBellPopover');
        if (root && !root.contains(e.target) && popover && popover.style.display === 'block') {
            popover.style.display = 'none';
        }
    });

    window.setPortalFeedCategory = function(cat) {
        portalCurrentCategory = cat;
        document.querySelectorAll('.portal-feed-tab').forEach(t => {
            t.classList.toggle('active', t.getAttribute('data-cat') === cat);
        });
        renderPortalNotifications();
    };

    window.togglePortalChimeMute = function() {
        portalChimeMuted = !portalChimeMuted;
        localStorage.setItem('uplyft_portal_chime_muted', portalChimeMuted);
        document.getElementById('portalChimeIcon').textContent = portalChimeMuted ? '🔇' : '🔊';
    };

    function playPortalChime() {
        if (portalChimeMuted) return;
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const now = ctx.currentTime;
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(587.33, now); // D5
            osc.frequency.exponentialRampToValueAtTime(880.00, now + 0.12); // A5
            gain.gain.setValueAtTime(0.2, now);
            gain.gain.exponentialRampToValueAtTime(0.001, now + 0.35);
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start(now);
            osc.stop(now + 0.36);
        } catch (e) {}
    }

    async function fetchPortalNotifications() {
        try {
            const res = await fetch(FEED_URL + (portalCurrentCategory !== 'all' ? `?category=${portalCurrentCategory}` : ''));
            const data = await res.json();
            if (data.success) {
                portalNotifications = data.notifications;
                updatePortalBadge(data.unread_count);
                renderPortalNotifications();
            }
        } catch (err) {
            console.error('Portal notification fetch error:', err);
        }
    }

    function updatePortalBadge(count) {
        const badge = document.getElementById('portalBellBadge');
        if (!badge) return;
        if (count > 0) {
            badge.textContent = count > 99 ? '99+' : count;
            badge.style.display = 'inline-block';
        } else {
            badge.style.display = 'none';
        }
    }

    function renderPortalNotifications() {
        const list = document.getElementById('portalNotificationList');
        const countLabel = document.getElementById('portalFeedCountLabel');
        if (!list) return;

        let filtered = portalNotifications;
        if (portalCurrentCategory !== 'all') {
            filtered = portalNotifications.filter(n => n.category === portalCurrentCategory);
        }

        if (countLabel) {
            countLabel.textContent = `${filtered.length} update${filtered.length === 1 ? '' : 's'}`;
        }

        if (filtered.length === 0) {
            list.innerHTML = `
                <div style="padding:32px 16px;text-align:center;color:#8A877E">
                    <div style="font-size:26px;margin-bottom:6px">✨</div>
                    <div style="font-size:12.5px;font-weight:700;color:#1B1A17">All caught up!</div>
                    <div style="font-size:11px;margin-top:2px">No updates in this category.</div>
                </div>
            `;
            return;
        }

        list.innerHTML = filtered.map(n => {
            const isUnread = !n.read_at;
            const iconSymbol = getCategoryIcon(n.category, n.icon);
            const actionUrl = n.action_url || '#';

            return `
                <div class="portal-noti-card ${isUnread ? 'unread' : ''}" onclick="handlePortalNotiClick('${n.id}', '${actionUrl}')" style="cursor:pointer">
                    <div style="width:34px;height:34px;border-radius:9px;background:${getColorBg(n.color)};color:${getColorText(n.color)};display:flex;align-items:center;justify-content:center;font-size:15px;flex-shrink:0">
                        ${iconSymbol}
                    </div>
                    <div style="flex:1;min-width:0">
                        <div style="display:flex;align-items:center;justify-content:space-between;gap:6px">
                            <div style="font-size:12.5px;font-weight:800;color:#1B1A17;line-height:1.2;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                                ${escapeHtml(n.title)}
                            </div>
                            <span style="font-size:10px;font-weight:600;color:#8A877E;flex-shrink:0">
                                ${n.created_at_human}
                            </span>
                        </div>
                        <div style="font-size:11.5px;color:#68665D;margin-top:3px;line-height:1.4">
                            ${escapeHtml(n.message)}
                        </div>
                        ${n.actor_name ? `
                            <div style="font-size:10px;font-weight:700;color:#D48A2E;margin-top:4px">
                                ${n.actor_role ? n.actor_role + ': ' : ''}${escapeHtml(n.actor_name)}
                            </div>
                        ` : ''}
                    </div>
                    <button type="button" onclick="event.stopPropagation(); deletePortalNotification('${n.id}')" style="background:none;border:none;color:#A19E92;font-size:12px;cursor:pointer;padding:2px;" title="Dismiss">✕</button>
                </div>
            `;
        }).join('');
    }

    function getCategoryIcon(category, icon) {
        if (category === 'academics') return '📚';
        if (category === 'finance') return '💰';
        if (category === 'attendance') return '📋';
        if (category === 'exam') return '📝';
        if (icon === 'book-open') return '📖';
        if (icon === 'file-invoice-dollar') return '💳';
        return '🔔';
    }

    function getColorBg(color) {
        if (color === 'emerald') return '#E3EFE2';
        if (color === 'rose') return '#F6E4E1';
        if (color === 'purple') return '#E7ECF6';
        if (color === 'blue') return '#E7ECF6';
        return '#F8E9D3';
    }

    function getColorText(color) {
        if (color === 'emerald') return '#2E6E42';
        if (color === 'rose') return '#A2412C';
        if (color === 'purple') return '#3A529C';
        if (color === 'blue') return '#3A529C';
        return '#8A5A10';
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/[&<>"']/g, function(m) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
        });
    }

    window.handlePortalNotiClick = async function(id, url) {
        try {
            await fetch(`${MARK_READ_URL}/${id}/mark-read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'Content-Type': 'application/json'
                }
            });
            const item = portalNotifications.find(n => n.id === id);
            if (item) item.read_at = new Date().toISOString();
            const unread = portalNotifications.filter(n => !n.read_at).length;
            updatePortalBadge(unread);
        } catch (e) {}

        if (url && url !== '#') {
            window.location.href = url;
        }
    };

    window.markAllPortalNotificationsRead = async function() {
        try {
            const res = await fetch(MARK_ALL_READ_URL, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'Content-Type': 'application/json'
                }
            });
            const data = await res.json();
            if (data.success) {
                portalNotifications.forEach(n => n.read_at = new Date().toISOString());
                updatePortalBadge(0);
                renderPortalNotifications();
            }
        } catch (e) {}
    };

    window.deletePortalNotification = async function(id) {
        try {
            await fetch(`${MARK_READ_URL}/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'Content-Type': 'application/json'
                }
            });
            portalNotifications = portalNotifications.filter(n => n.id !== id);
            const unread = portalNotifications.filter(n => !n.read_at).length;
            updatePortalBadge(unread);
            renderPortalNotifications();
        } catch (e) {}
    };

    window.triggerTestPortalAlert = async function() {
        try {
            await fetch(TEST_ALERT_URL, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'Content-Type': 'application/json'
                }
            });
        } catch (e) {}
    };

    function showPortalToastAlert(payload) {
        const container = document.getElementById('portalLiveToastContainer');
        if (!container) return;

        playPortalChime();

        const toast = document.createElement('div');
        toast.className = 'portal-toast-alert';
        toast.innerHTML = `
            <div style="width:36px;height:36px;border-radius:10px;background:#F8E9D3;color:#8A5A10;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0">
                ${getCategoryIcon(payload.category, payload.icon)}
            </div>
            <div style="flex:1;min-width:0">
                <div style="font-size:13px;font-weight:800;color:#1B1A17;line-height:1.2;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                    ${escapeHtml(payload.title)}
                </div>
                <div style="font-size:11.5px;color:#68665D;margin-top:2px;line-height:1.3;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                    ${escapeHtml(payload.message)}
                </div>
            </div>
            <button type="button" onclick="this.closest('.portal-toast-alert').remove()" style="background:none;border:none;color:#8A877E;font-size:14px;cursor:pointer;padding:4px">✕</button>
        `;

        container.appendChild(toast);
        setTimeout(() => {
            toast.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(20px)';
            setTimeout(() => toast.remove(), 350);
        }, 5000);
    }

    // Initialize Echo listener for Laravel Reverb
    document.addEventListener('DOMContentLoaded', function() {
        if (portalChimeMuted) {
            const icon = document.getElementById('portalChimeIcon');
            if (icon) icon.textContent = '🔇';
        }

        fetchPortalNotifications();

        // Listen on private user channel via Laravel Echo / Reverb
        function initPortalEchoListener() {
            if (window.Echo && CURRENT_USER_ID > 0) {
                try {
                    window.Echo.private(`user.${CURRENT_USER_ID}`)
                        .listen('.portal.notification', function(e) {
                            showPortalToastAlert(e);
                            portalNotifications.unshift({
                                id: e.id,
                                title: e.title,
                                message: e.message,
                                category: e.category,
                                icon: e.icon,
                                color: e.color,
                                action_url: e.action_url,
                                actor_name: e.actor_name,
                                actor_role: e.actor_role,
                                meta: e.meta || {},
                                read_at: null,
                                created_at: e.created_at,
                                created_at_human: 'Just now'
                            });
                            const unread = portalNotifications.filter(n => !n.read_at).length;
                            updatePortalBadge(unread);
                            renderPortalNotifications();
                        });

                    const connLabel = document.getElementById('portalReverbConnLabel');
                    if (connLabel) {
                        connLabel.innerHTML = `<span style="width:5px;height:5px;border-radius:50%;background:#10b981;display:inline-block"></span> Live Reverb`;
                    }
                } catch (err) {
                    console.info('Reverb private channel init deferred:', err);
                }
            } else {
                setTimeout(initPortalEchoListener, 1000);
            }
        }

        initPortalEchoListener();

        // Background polling fallback every 45s
        setInterval(fetchPortalNotifications, 45000);
    });
})();
</script>
@endauth
