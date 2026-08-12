@extends('principal.layouts.app')
@section('title', 'Classes, Sections & Subjects')
@section('breadcrumb', 'Classes & Subjects')

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
        margin-bottom: 16px;
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
        margin-top: 20px;
    }

    /* Accordion Style */
    .class-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 14px;
        margin-bottom: 16px;
        overflow: hidden;
        transition: border-color 0.2s ease;
    }
    .class-card.open {
        border-color: var(--accent);
    }
    .class-card-header {
        padding: 20px 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        cursor: pointer;
        user-select: none;
        background: var(--surface);
        transition: background 0.15s ease;
    }
    .class-card-header:hover {
        background: rgba(30, 41, 59, 0.5);
    }
    .class-card-body {
        padding: 0 24px 24px 24px;
        display: none;
        border-top: 1px solid var(--border);
        background: #0f1422;
    }
    .class-card.open .class-card-body {
        display: block;
    }

    .capacity-badge {
        font-size: 11px;
        font-weight: 700;
        padding: 3px 8px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .capacity-green { background: rgba(46,213,115,0.15); color: #2ed573; border: 1px solid rgba(46,213,115,0.3); }
    .capacity-red { background: rgba(255,71,87,0.15); color: #ff4757; border: 1px solid rgba(255,71,87,0.3); }
</style>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:16px">
    <div>
        <h1 style="font-family:'Space Grotesk',sans-serif;font-size:24px;font-weight:700">Classes, Sections & Subjects</h1>
        <p style="color:var(--text-muted);font-size:14px;margin-top:2px">
            Provision school classes with compulsory sections, assigned rooms, student capacity, and subjects.
        </p>
    </div>
    <div style="display:flex;gap:12px">
        <a href="{{ route('principal.rooms.index') }}" class="btn btn-ghost">
            🏢 Manage Rooms & Facilities
        </a>
        <button type="button" class="btn btn-primary" onclick="openAddClassModal()">
            ➕ Add New Class
        </button>
    </div>
</div>

<!-- Full Width Classes Accordion List -->
<div>
    @if($classes->count() > 0)
        @foreach($classes as $index => $class)
        <div class="class-card {{ $index === 0 ? 'open' : '' }}" id="class-card-{{ $class->id }}">
            <!-- Card Header -->
            <div class="class-card-header" onclick="toggleClassCard({{ $class->id }})">
                <div style="display:flex;align-items:center;gap:14px">
                    <span id="chevron-{{ $class->id }}" style="font-size:14px;color:var(--text-muted);transition:transform 0.2s;display:inline-block;{{ $index === 0 ? 'transform:rotate(90deg)' : '' }}">▶</span>
                    <div>
                        <div style="font-family:'Space Grotesk',sans-serif;font-size:18px;font-weight:700;color:#fff">
                            {{ $class->custom_name }}
                        </div>
                        <div style="font-size:12px;color:var(--text-muted);margin-top:2px">
                            {{ $class->sections->count() }} Section(s) • {{ $class->subjects->count() }} Subject(s)
                        </div>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:12px">
                    <span class="badge badge-purple">{{ $class->sections->count() }} Sections</span>
                    <span class="badge badge-purple">{{ $class->subjects->count() }} Subjects</span>
                    <button type="button" class="btn btn-danger btn-sm" onclick="event.stopPropagation(); triggerDeleteModal('class', '{{ route('principal.classes.destroy', $class) }}', '{{ addslashes($class->custom_name) }}')" style="padding:4px 10px;font-size:12px">
                        🗑️ Delete Class
                    </button>
                </div>
            </div>

            <!-- Card Body -->
            <div class="class-card-body">
                
                <!-- 1. SECTIONS & ROOM ALLOCATIONS SUB-PANEL -->
                <div style="margin-top:20px;margin-bottom:14px;display:flex;align-items:center;justify-content:space-between">
                    <span style="font-weight:600;font-size:14px;color:var(--accent2)">🏫 Sections, Assigned Rooms & Seating Capacity</span>
                    <button type="button" class="btn btn-ghost btn-sm" onclick="openAddSectionModal({{ $class->id }}, '{{ addslashes($class->custom_name) }}')">
                        ➕ Add Section
                    </button>
                </div>

                @if($class->sections->count() > 0)
                <table style="margin-bottom:24px;background:var(--surface);border-radius:10px;overflow:hidden;border:1px solid var(--border)">
                    <thead>
                        <tr>
                            <th>Section Name</th>
                            <th>Assigned Room / Lab</th>
                            <th>Enrolled / Room Capacity</th>
                            <th style="text-align:right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($class->sections as $sec)
                        @php 
                            $enrolled = $sec->enrolled_students ?? 0; 
                            $capacity = $sec->capacity ?? 40;
                            $isFull = $enrolled >= $capacity;
                            $roomName = $sec->room ? $sec->room->room_number : ($sec->room_number ?? 'Unassigned Room');
                        @endphp
                        <tr>
                            <td><strong style="color:#fff">{{ $sec->section_name }}</strong></td>
                            <td>
                                <span style="font-size:13px;color:var(--text)">
                                    📍 {{ $roomName }}
                                </span>
                            </td>
                            <td>
                                <span class="capacity-badge {{ $isFull ? 'capacity-red' : 'capacity-green' }}">
                                    👥 {{ $enrolled }} / {{ $capacity }} Seats
                                    @if($isFull)(FULL)@endif
                                </span>
                            </td>
                            <td style="text-align:right">
                                <button type="button" 
                                        class="btn btn-danger btn-sm"
                                        onclick="triggerDeleteModal('section', '{{ route('principal.sections.destroy', $sec) }}', '{{ addslashes($sec->section_name) }}')">
                                    🗑️ Delete Section
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <p style="color:var(--text-muted);font-size:13px;margin-bottom:24px;font-style:italic">No sections provisioned yet. Click "Add Section" above.</p>
                @endif

                <!-- 2. SUBJECTS SUB-PANEL -->
                <div style="margin-top:20px;margin-bottom:14px;display:flex;align-items:center;justify-content:space-between">
                    <div>
                        <span style="font-weight:600;font-size:14px;color:var(--accent2)">📖 Subjects Offered in {{ $class->custom_name }}</span>
                        <span style="font-size:11px;color:var(--text-muted);margin-left:8px">(Applies automatically to all sections of {{ $class->custom_name }})</span>
                    </div>
                    <button type="button" class="btn btn-ghost btn-sm" onclick="openAddSubjectModal({{ $class->id }}, '{{ addslashes($class->custom_name) }}')">
                        ➕ Add Subject
                    </button>
                </div>

                @if($class->subjects->count() > 0)
                <table style="margin-bottom:12px;background:var(--surface);border-radius:10px;overflow:hidden;border:1px solid var(--border)">
                    <thead>
                        <tr>
                            <th>Subject Name</th>
                            <th>Subject Code</th>
                            <th>Credit Hours</th>
                            <th style="text-align:right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($class->subjects as $subject)
                        <tr>
                            <td><strong style="color:#fff">{{ $subject->subject_name }}</strong></td>
                            <td><code style="color:var(--accent2);background:rgba(0,206,209,0.1);padding:2px 6px;border-radius:4px">{{ $subject->subject_code ?? 'N/A' }}</code></td>
                            <td>{{ $subject->credit_hours }} Hours</td>
                            <td style="text-align:right">
                                <button type="button" 
                                        class="btn btn-danger btn-sm"
                                        onclick="triggerDeleteModal('subject', '{{ route('principal.subjects.destroy', $subject) }}', '{{ addslashes($subject->subject_name) }}')">
                                    🗑️ Delete Subject
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <p style="color:var(--text-muted);font-size:13px;margin-bottom:12px;font-style:italic">No subjects added to this class yet. Click "Add Subject" above.</p>
                @endif

            </div>
        </div>
        @endforeach
    @else
    <div class="card" style="text-align:center;padding:48px 24px">
        <p style="color:var(--text-muted);font-size:16px;margin-bottom:16px">No classes added yet for this institute.</p>
        <button type="button" class="btn btn-primary" onclick="openAddClassModal()">
            ➕ Provision Your First Class
        </button>
    </div>
    @endif
</div>

<!-- MODAL 1: Add New Class -->
<div class="modal-backdrop" id="addClassModal">
    <div class="modal-box">
        <div class="modal-header">
            <div class="modal-title">➕ Provision New Class & Section</div>
            <button type="button" onclick="closeAddClassModal()" style="background:none;border:none;color:var(--text-muted);font-size:20px;cursor:pointer">&times;</button>
        </div>
        <form method="POST" action="{{ route('principal.classes.store') }}">
            @csrf

            <div class="form-group">
                <label for="custom_name">Class Name *</label>
                <input id="custom_name" type="text" name="custom_name" placeholder="e.g. Grade 10, 1st Year, ACCA FA1" required value="{{ old('custom_name') }}">
            </div>

            <div class="form-group">
                <label for="section_name">Section Name * (Compulsory)</label>
                <input id="section_name" type="text" name="section_name" placeholder="e.g. Section A, Section 1" required value="{{ old('section_name') }}">
            </div>

            <div class="form-group">
                <label for="room_id">Select Room / Lab</label>
                <select id="room_id" name="room_id" onchange="handleRoomSelect(this)">
                    <option value="" data-capacity="40">-- Select Created Room (Optional) --</option>
                    @foreach($rooms as $rm)
                        <option value="{{ $rm->id }}" data-capacity="{{ $rm->capacity }}" {{ old('room_id') == $rm->id ? 'selected' : '' }}>
                            📍 {{ $rm->room_number }} (Max: {{ $rm->capacity }} Seats)
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="capacity">Room Capacity (Max Students) *</label>
                <input id="capacity" type="number" name="capacity" class="section-capacity-input" placeholder="e.g. 40" min="1" max="1000" required value="{{ old('capacity', 40) }}">
            </div>

            <div class="form-group">
                <label for="enrolled_students">Current Enrolled Students</label>
                <input id="enrolled_students" type="number" name="enrolled_students" placeholder="e.g. 0 or 35" min="0" value="{{ old('enrolled_students', 0) }}">
            </div>

            <div class="modal-actions">
                <button type="button" class="btn btn-ghost" onclick="closeAddClassModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Provision Class</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 2: Add Additional Section -->
<div class="modal-backdrop" id="addSectionModal">
    <div class="modal-box">
        <div class="modal-header">
            <div class="modal-title" id="addSectionModalTitle">➕ Add Section</div>
            <button type="button" onclick="closeAddSectionModal()" style="background:none;border:none;color:var(--text-muted);font-size:20px;cursor:pointer">&times;</button>
        </div>
        <form method="POST" action="{{ route('principal.sections.store') }}">
            @csrf
            <input type="hidden" name="institute_class_id" id="sec_class_id">

            <div class="form-group">
                <label for="sec_section_name">Section Name *</label>
                <input id="sec_section_name" type="text" name="section_name" placeholder="e.g. Section B, Green" required>
            </div>

            <div class="form-group">
                <label for="sec_room_id">Select Room / Lab</label>
                <select id="sec_room_id" name="room_id" onchange="handleRoomSelect(this)">
                    <option value="" data-capacity="40">-- Select Room (Optional) --</option>
                    @foreach($rooms as $rm)
                        <option value="{{ $rm->id }}" data-capacity="{{ $rm->capacity }}">📍 {{ $rm->room_number }} (Max: {{ $rm->capacity }} Seats)</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="sec_capacity">Room Capacity (Max Students) *</label>
                <input id="sec_capacity" type="number" name="capacity" class="section-capacity-input" placeholder="e.g. 40" min="1" max="1000" value="40" required>
            </div>

            <div class="form-group">
                <label for="sec_enrolled">Current Enrolled Students</label>
                <input id="sec_enrolled" type="number" name="enrolled_students" value="0" min="0" required>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn btn-ghost" onclick="closeAddSectionModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Section</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 3: Add Subject -->
<div class="modal-backdrop" id="addSubjectModal">
    <div class="modal-box">
        <div class="modal-header">
            <div class="modal-title" id="addSubjectModalTitle">➕ Add Subject</div>
            <button type="button" onclick="closeAddSubjectModal()" style="background:none;border:none;color:var(--text-muted);font-size:20px;cursor:pointer">&times;</button>
        </div>
        <form method="POST" action="{{ route('principal.subjects.store') }}">
            @csrf
            <input type="hidden" name="institute_class_id" id="sub_class_id">

            <div class="form-group">
                <label for="sub_subject_name">Subject Name *</label>
                <input id="sub_subject_name" type="text" name="subject_name" placeholder="e.g. Physics, Mathematics" required>
            </div>

            <div class="form-group">
                <label for="sub_subject_code">Subject Code (Optional)</label>
                <input id="sub_subject_code" type="text" name="subject_code" placeholder="e.g. PHY-101">
            </div>

            <div class="form-group">
                <label for="sub_credit_hours">Credit Hours *</label>
                <input id="sub_credit_hours" type="number" name="credit_hours" value="3" min="1" max="10" required>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn btn-ghost" onclick="closeAddSubjectModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Subject</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal Popup -->
<div class="modal-backdrop" id="deleteConfirmationModal">
    <div class="modal-box">
        <div class="modal-header">
            <div class="modal-title" id="modalTitle">⚠️ Confirm Deletion</div>
            <button type="button" onclick="closeDeleteModal()" style="background:none;border:none;color:var(--text-muted);font-size:20px;cursor:pointer">&times;</button>
        </div>
        <div class="modal-body" id="modalMessage">
            Are you sure you want to delete this item? This action cannot be undone.
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

<script>
    function handleRoomSelect(selectElem) {
        const selectedOption = selectElem.options[selectElem.selectedIndex];
        const capacity = selectedOption.getAttribute('data-capacity');
        if (capacity) {
            const form = selectElem.closest('form');
            const capInput = form.querySelector('.section-capacity-input, #capacity, #sec_capacity');
            if (capInput) {
                capInput.value = capacity;
            }
        }
    }

    function toggleClassCard(classId) {
        const card = document.getElementById('class-card-' + classId);
        const chevron = document.getElementById('chevron-' + classId);
        if (card.classList.contains('open')) {
            card.classList.remove('open');
            chevron.style.transform = 'rotate(0deg)';
        } else {
            card.classList.add('open');
            chevron.style.transform = 'rotate(90deg)';
        }
    }

    // Modal Control Functions
    function openAddClassModal() {
        document.getElementById('addClassModal').classList.add('active');
    }
    function closeAddClassModal() {
        document.getElementById('addClassModal').classList.remove('active');
    }

    function openAddSectionModal(classId, className) {
        document.getElementById('sec_class_id').value = classId;
        document.getElementById('addSectionModalTitle').innerText = '➕ Add Section to ' + className;
        document.getElementById('addSectionModal').classList.add('active');
    }
    function closeAddSectionModal() {
        document.getElementById('addSectionModal').classList.remove('active');
    }

    function openAddSubjectModal(classId, className) {
        document.getElementById('sub_class_id').value = classId;
        document.getElementById('addSubjectModalTitle').innerText = '➕ Add Subject to ' + className;
        document.getElementById('addSubjectModal').classList.add('active');
    }
    function closeAddSubjectModal() {
        document.getElementById('addSubjectModal').classList.remove('active');
    }

    function triggerDeleteModal(type, targetUrl, itemName) {
        const modal = document.getElementById('deleteConfirmationModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalMessage = document.getElementById('modalMessage');
        const modalForm = document.getElementById('deleteModalForm');

        modalForm.action = targetUrl;

        if (type === 'subject') {
            modalTitle.innerText = '🗑️ Delete Subject';
            modalMessage.innerHTML = 'Are you sure you want to delete subject <strong style="color:#fff">' + itemName + '</strong>?<br>This will remove it from class subjects.';
        } else if (type === 'section') {
            modalTitle.innerText = '🗑️ Delete Section';
            modalMessage.innerHTML = 'Are you sure you want to delete section <strong style="color:#fff">' + itemName + '</strong>?<br>This will remove it from class sections.';
        } else if (type === 'class') {
            modalTitle.innerText = '🗑️ Delete Class';
            modalMessage.innerHTML = 'Are you sure you want to delete class <strong style="color:#fff">' + itemName + '</strong>?<br>This will also remove all associated subjects and sections.';
        }

        modal.classList.add('active');
    }

    function closeDeleteModal() {
        document.getElementById('deleteConfirmationModal').classList.remove('active');
    }

    // Close modals on escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeAddClassModal();
            closeAddSectionModal();
            closeAddSubjectModal();
            closeDeleteModal();
        }
    });
</script>
@endsection
