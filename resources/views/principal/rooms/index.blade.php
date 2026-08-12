@extends('principal.layouts.app')
@section('title', 'Rooms & Facilities Management')
@section('breadcrumb', 'Rooms & Facilities')

@section('content')
<style>
    /* Modal Backdrop & Dialog */
    .modal-backdrop {
        position: fixed;
        top: 0; left: 0; width: 100vw; height: 100vh;
        background: rgba(11, 15, 25, 0.85);
        backdrop-filter: blur(6px);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }
    .modal-backdrop.active { display: flex; }
    
    .modal-box {
        background: #121827;
        border: 1px solid #2e3d56;
        border-radius: 16px;
        width: 100%;
        max-width: 480px;
        padding: 24px;
        box-shadow: 0 20px 50px rgba(0,0,0,0.6);
        animation: modalSlide 0.2s ease-out;
    }
    @keyframes modalSlide {
        from { transform: translateY(-20px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
    .modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
    }
    .modal-title {
        font-family: 'Space Grotesk', sans-serif;
        font-size: 18px;
        font-weight: 700;
        color: #fff;
    }
    .modal-body {
        color: #94a3b8;
        font-size: 14px;
        line-height: 1.5;
        margin-bottom: 24px;
    }
    .modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
    }

    /* Room Card */
    .room-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 14px;
        margin-bottom: 20px;
        overflow: hidden;
        transition: border-color 0.3s ease;
    }
    .room-card:hover {
        border-color: rgba(99, 102, 241, 0.4);
    }
    .room-card-header {
        padding: 18px 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        cursor: pointer;
        user-select: none;
        background: var(--surface);
        transition: background 0.2s ease;
    }
    .room-card-header:hover { background: rgba(30, 41, 59, 0.5); }
    .room-card-body {
        display: none;
        padding: 0 24px 24px 24px;
        border-top: 1px solid var(--border);
        background: #0f1422;
    }
    .room-card.open .room-card-body { display: block; }

    /* Schedule Slot */
    .schedule-slot {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 12px 16px;
        background: rgba(30, 41, 59, 0.5);
        border: 1px solid var(--border);
        border-radius: 10px;
        margin-bottom: 8px;
        transition: background 0.2s ease;
    }
    .schedule-slot:hover { background: rgba(30, 41, 59, 0.8); }
    .schedule-slot.busy {
        border-left: 3px solid #ff4757;
    }
    .schedule-slot.free {
        border-left: 3px solid #2ed573;
    }
    .time-badge {
        font-family: 'Space Grotesk', sans-serif;
        font-size: 13px;
        font-weight: 700;
        color: #fff;
        background: rgba(99, 102, 241, 0.2);
        border: 1px solid rgba(99, 102, 241, 0.3);
        padding: 4px 10px;
        border-radius: 8px;
        white-space: nowrap;
        min-width: 110px;
        text-align: center;
    }
    .slot-info { flex: 1; }
    .slot-subject {
        font-size: 14px;
        font-weight: 600;
        color: #fff;
        margin-bottom: 2px;
    }
    .slot-details {
        font-size: 12px;
        color: var(--text-muted);
    }
    .status-tag {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        padding: 3px 8px;
        border-radius: 6px;
        letter-spacing: 0.5px;
    }
    .status-busy { background: rgba(255,71,87,0.15); color: #ff4757; border: 1px solid rgba(255,71,87,0.3); }
    .status-free { background: rgba(46,213,115,0.15); color: #2ed573; border: 1px solid rgba(46,213,115,0.3); }

    /* Day Filter */
    .day-filter {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
        margin-bottom: 20px;
    }
    .day-btn {
        padding: 6px 14px;
        font-size: 12px;
        font-weight: 600;
        border-radius: 8px;
        border: 1px solid var(--border);
        background: var(--surface);
        color: var(--text-muted);
        cursor: pointer;
        transition: all 0.15s ease;
        text-decoration: none;
    }
    .day-btn:hover { border-color: var(--accent); color: #fff; }
    .day-btn.active {
        background: var(--accent);
        border-color: var(--accent);
        color: #fff;
    }
</style>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px">
    <div>
        <h1 style="font-family:'Space Grotesk',sans-serif;font-size:24px;font-weight:700">🏢 Rooms & Facilities Management</h1>
        <p style="color:var(--text-muted);font-size:14px;margin-top:2px">
            Provision physical rooms, track live schedules per room, and manage room allocation. Principals can freely choose which room to assign to any class section.
        </p>
    </div>
</div>

<!-- Day Filter for Schedule View -->
<div style="margin-bottom:6px;font-size:13px;font-weight:600;color:var(--text)">📅 Viewing Schedule For:</div>
<div class="day-filter">
    @foreach($days as $day)
        <a href="{{ route('principal.rooms.index', ['day' => $day]) }}" 
           class="day-btn {{ $selectedDay === $day ? 'active' : '' }}">
            {{ ucfirst($day) }}
            @if($selectedDay === $day && strtolower(now()->format('l')) === $day)
                (Today)
            @endif
        </a>
    @endforeach
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:24px">
    <!-- Left Side: Room Cards with Live Schedule -->
    <div>
        @if($rooms->count() > 0)
            @foreach($rooms as $index => $room)
            @php
                $schedule = $roomSchedules->get($room->id, collect());
                $busyCount = $schedule->count();
            @endphp
            <div class="room-card {{ $index === 0 ? 'open' : '' }}" id="room-card-{{ $room->id }}">
                <!-- Room Card Header -->
                <div class="room-card-header" onclick="toggleRoomCard({{ $room->id }})">
                    <div style="display:flex;align-items:center;gap:14px">
                        <span id="chevron-r-{{ $room->id }}" style="font-size:14px;color:var(--text-muted);transition:transform 0.2s;display:inline-block;{{ $index === 0 ? 'transform:rotate(90deg)' : '' }}">▶</span>
                        <div>
                            <div style="font-family:'Space Grotesk',sans-serif;font-size:18px;font-weight:700;color:#fff">
                                📍 {{ $room->room_number }}
                            </div>
                            <div style="font-size:12px;color:var(--text-muted);margin-top:2px">
                                {{ $room->building_block ?? 'Main Block' }} • 🪑 {{ $room->capacity }} Seats • {{ $room->class_sections_count }} Section(s) Assigned
                            </div>
                        </div>
                    </div>
                    <div style="display:flex;align-items:center;gap:10px">
                        @if($busyCount > 0)
                            <span class="status-tag status-busy">{{ $busyCount }} Slot(s) Busy</span>
                        @else
                            <span class="status-tag status-free">Free All Day</span>
                        @endif
                        <button type="button" class="btn btn-ghost btn-sm" onclick="event.stopPropagation(); openEditRoomModal({{ $room->id }}, '{{ addslashes($room->room_number) }}', '{{ addslashes($room->building_block ?? '') }}', {{ $room->capacity }})" style="padding:4px 10px;font-size:12px">
                            ✏️ Edit
                        </button>
                        <button type="button" class="btn btn-danger btn-sm" onclick="event.stopPropagation(); triggerDeleteModal('{{ route('principal.rooms.destroy', $room) }}', '{{ addslashes($room->room_number) }}')" style="padding:4px 10px;font-size:12px">
                            🗑️ Delete
                        </button>
                    </div>
                </div>

                <!-- Room Card Body: Schedule -->
                <div class="room-card-body">
                    <div style="margin-top:16px;margin-bottom:12px;font-size:14px;font-weight:600;color:var(--accent2)">
                        📋 {{ ucfirst($selectedDay) }}'s Schedule for {{ $room->room_number }}
                        @if(strtolower(now()->format('l')) === $selectedDay)
                            <span style="color:var(--accent);font-size:12px;margin-left:6px">(Today)</span>
                        @endif
                    </div>

                    @if($schedule->count() > 0)
                        @foreach($schedule as $slot)
                        <div class="schedule-slot busy">
                            <div class="time-badge">
                                🕐 {{ \Carbon\Carbon::parse($slot->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($slot->end_time)->format('g:i A') }}
                            </div>
                            <div class="slot-info">
                                <div class="slot-subject">
                                    {{ $slot->subject->subject_name ?? 'Subject' }}
                                    ({{ $slot->section->instituteClass->custom_name ?? 'Class' }} {{ $slot->section->section_name ?? '' }})
                                </div>
                                <div class="slot-details">
                                    👤 Teacher: <strong style="color:#fff">{{ $slot->teacher->name ?? 'TBA' }}</strong>
                                </div>
                            </div>
                            <span class="status-tag status-busy">BUSY</span>
                        </div>
                        @endforeach
                    @else
                        <div class="schedule-slot free">
                            <div class="time-badge" style="background:rgba(46,213,115,0.2);border-color:rgba(46,213,115,0.3)">
                                All Day
                            </div>
                            <div class="slot-info">
                                <div class="slot-subject" style="color:#2ed573">
                                    ✅ No classes scheduled
                                </div>
                                <div class="slot-details">
                                    This room is free for the entire {{ ucfirst($selectedDay) }}.
                                </div>
                            </div>
                            <span class="status-tag status-free">FREE</span>
                        </div>
                    @endif

                    <div style="margin-top:16px;text-align:right">
                        <a href="{{ route('principal.timetables.index') }}" class="btn btn-ghost btn-sm" style="font-size:12px">
                            🗓️ Edit Schedule in Timetable Matrix →
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        @else
        <div class="card">
            <p style="color:var(--text-muted);font-size:14px;padding:12px 0">No rooms provisioned yet. Use the panel on the right to add your first classroom.</p>
        </div>
        @endif
    </div>

    <!-- Right Side: Add Room Form -->
    <div>
        <div class="card" style="position:sticky;top:84px">
            <div class="card-header">
                <div class="card-title">➕ Add New Room / Lab</div>
            </div>

            <form method="POST" action="{{ route('principal.rooms.store') }}">
                @csrf

                <!-- Room Number / Name -->
                <div class="form-group">
                    <label for="room_number">Room / Lab Name *</label>
                    <input id="room_number" type="text" name="room_number" placeholder="e.g. Room 101, Lab 2, Hall A" required value="{{ old('room_number') }}">
                    @error('room_number')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <!-- Building / Block -->
                <div class="form-group">
                    <label for="building_block">Building / Block (Optional)</label>
                    <input id="building_block" type="text" name="building_block" placeholder="e.g. Science Wing, Main Building" value="{{ old('building_block') }}">
                    @error('building_block')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <!-- Room Capacity -->
                <div class="form-group">
                    <label for="capacity">Seating Capacity (Max Students) *</label>
                    <input id="capacity" type="number" name="capacity" placeholder="e.g. 40" min="1" max="1000" required value="{{ old('capacity', 40) }}">
                    @error('capacity')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%;margin-top:8px">➕ Add Room / Lab</button>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal-backdrop" id="deleteConfirmationModal">
    <div class="modal-box">
        <div class="modal-header">
            <div class="modal-title">🗑️ Delete Room</div>
            <button type="button" onclick="closeDeleteModal()" style="background:none;border:none;color:var(--text-muted);font-size:20px;cursor:pointer">&times;</button>
        </div>
        <div class="modal-body" id="deleteModalMessage">
            Are you sure you want to delete this room?
        </div>
        <div class="modal-actions">
            <button type="button" class="btn btn-ghost" onclick="closeDeleteModal()">Cancel</button>
            <form id="deleteModalForm" method="POST" action="">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">Confirm Delete</button>
            </form>
        </div>
    </div>
</div>

<!-- Edit Room Modal -->
<div class="modal-backdrop" id="editRoomModal">
    <div class="modal-box">
        <div class="modal-header">
            <div class="modal-title">✏️ Edit Room Details</div>
            <button type="button" onclick="closeEditRoomModal()" style="background:none;border:none;color:var(--text-muted);font-size:20px;cursor:pointer">&times;</button>
        </div>
        <form id="editRoomForm" method="POST" action="">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label>Room / Lab Name *</label>
                <input type="text" name="room_number" id="edit_room_number" required>
            </div>
            <div class="form-group">
                <label>Building / Block (Optional)</label>
                <input type="text" name="building_block" id="edit_building_block">
            </div>
            <div class="form-group">
                <label>Seating Capacity *</label>
                <input type="number" name="capacity" id="edit_capacity" min="1" max="1000" required>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-ghost" onclick="closeEditRoomModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleRoomCard(roomId) {
        const card = document.getElementById('room-card-' + roomId);
        const chevron = document.getElementById('chevron-r-' + roomId);
        if (card.classList.contains('open')) {
            card.classList.remove('open');
            chevron.style.transform = 'rotate(0deg)';
        } else {
            card.classList.add('open');
            chevron.style.transform = 'rotate(90deg)';
        }
    }

    function triggerDeleteModal(targetUrl, roomName) {
        const modal = document.getElementById('deleteConfirmationModal');
        const form = document.getElementById('deleteModalForm');
        const msg = document.getElementById('deleteModalMessage');
        form.action = targetUrl;
        msg.innerHTML = 'Are you sure you want to delete room <strong style="color:#fff">' + roomName + '</strong>?<br>Sections assigned to this room will become unassigned. Timetable slots for this room will lose their room assignment.';
        modal.classList.add('active');
    }

    function closeDeleteModal() {
        document.getElementById('deleteConfirmationModal').classList.remove('active');
    }

    function openEditRoomModal(roomId, roomNumber, buildingBlock, capacity) {
        const modal = document.getElementById('editRoomModal');
        const form = document.getElementById('editRoomForm');
        form.action = '/principal/rooms/' + roomId;
        document.getElementById('edit_room_number').value = roomNumber;
        document.getElementById('edit_building_block').value = buildingBlock;
        document.getElementById('edit_capacity').value = capacity;
        modal.classList.add('active');
    }

    function closeEditRoomModal() {
        document.getElementById('editRoomModal').classList.remove('active');
    }

    // Close modals on escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeDeleteModal();
            closeEditRoomModal();
        }
    });
</script>
@endsection
