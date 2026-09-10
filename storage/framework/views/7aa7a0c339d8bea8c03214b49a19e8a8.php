<?php $__env->startSection('title', 'Classrooms & Campus Facilities'); ?>
<?php $__env->startSection('breadcrumb', 'Classrooms & Facilities'); ?>

<?php $__env->startSection('content'); ?>
<style>
    /* Modal Backdrop & Dialog */
    .modal-backdrop {
        position: fixed;
        top: 0; left: 0; width: 100vw; height: 100vh;
        background: rgba(15, 23, 42, 0.5);
        backdrop-filter: blur(8px);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }
    .modal-backdrop.active { display: flex; }
    
    .modal-box {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        width: 100%;
        max-width: 480px;
        padding: 24px;
        box-shadow: 0 25px 60px rgba(0,0,0,0.15);
        animation: modalSlide 0.2s ease-out;
        color: #0f172a;
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
        font-family: 'Outfit', sans-serif;
        font-size: 18px;
        font-weight: 700;
        color: #0f172a;
    }
    .modal-close {
        background: none;
        border: none;
        font-size: 20px;
        color: #64748b;
        cursor: pointer;
        padding: 4px 8px;
    }
    .modal-close:hover { color: #0f172a; }

    /* Forms */
    .form-group { margin-bottom: 16px; }
    .form-group label {
        display: block;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #475569;
        margin-bottom: 6px;
    }
    .form-group input, .form-group select {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        background: #ffffff;
        color: #0f172a;
        font-size: 14px;
        outline: none;
        transition: border-color 0.15s ease;
    }
    .form-group input:focus, .form-group select:focus {
        border-color: #4f46e5;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
    }
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }

    /* Cards */
    .card {
        background: #ffffff;
        border: 1px solid rgba(226, 232, 240, 0.85);
        border-radius: 16px;
        box-shadow: 0 4px 20px -2px rgba(0,0,0,0.04);
        padding: 24px;
    }

    .room-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 20px;
        margin-bottom: 16px;
        transition: border-color 0.15s ease;
    }
    .room-card:hover { border-color: #cbd5e1; }
    .room-card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 14px;
    }
    .room-card-title {
        font-family: 'Outfit', sans-serif;
        font-size: 18px;
        font-weight: 800;
        color: #0f172a;
    }
    .room-badge {
        font-size: 11px;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 8px;
    }

    /* Timeline Slot */
    .slot-row {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 14px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        margin-bottom: 8px;
    }
    .slot-time {
        font-family: 'JetBrains Mono', monospace;
        font-size: 12px;
        font-weight: 700;
        color: #4f46e5;
        background: #eef2ff;
        padding: 4px 10px;
        border-radius: 8px;
        white-space: nowrap;
        min-width: 110px;
        text-align: center;
    }
    .slot-info { flex: 1; }
    .slot-subject { font-size: 14px; font-weight: 700; color: #0f172a; }
    .slot-details { font-size: 12px; color: #64748b; }
    
    .status-tag {
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        padding: 4px 10px;
        border-radius: 6px;
        letter-spacing: 0.5px;
    }
    .status-busy { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
    .status-free { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }

    /* Day Filter */
    .day-filter {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-bottom: 20px;
    }
    .day-btn {
        padding: 8px 16px;
        font-size: 12px;
        font-weight: 700;
        border-radius: 10px;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        color: #475569;
        cursor: pointer;
        transition: all 0.15s ease;
        text-decoration: none;
    }
    .day-btn:hover { border-color: #4f46e5; color: #4338ca; }
    .day-btn.active {
        background: linear-gradient(135deg, #4f46e5, #4338ca);
        border-color: #4f46e5;
        color: #ffffff;
        box-shadow: 0 4px 14px rgba(79, 70, 229, 0.25);
    }
</style>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px">
    <div>
        <h1 style="font-family:'Outfit',sans-serif;font-size:24px;font-weight:800;color:#0f172a;letter-spacing:-0.5px">🏢 Classrooms &amp; Campus Facilities</h1>
        <p style="color:#64748b;font-size:13px;margin-top:4px;font-weight:500">
            Manage physical classrooms, computer/science labs, room seat capacities, and daily schedule allocations.
        </p>
    </div>
</div>

<!-- Day Filter for Schedule View -->
<div style="margin-bottom:8px;font-size:13px;font-weight:700;color:#0f172a">📅 Viewing Schedule For:</div>
<div class="day-filter">
    <?php $__currentLoopData = $days; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <a href="<?php echo e(route('principal.rooms.index', ['day' => $day])); ?>" 
           class="day-btn <?php echo e($selectedDay === $day ? 'active' : ''); ?>">
            <?php echo e(ucfirst($day)); ?>

            <?php if($selectedDay === $day && strtolower(now()->format('l')) === $day): ?>
                (Today)
            <?php endif; ?>
        </a>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:24px">
    <!-- Left Side: Room Cards with Live Schedule -->
    <div>
        <?php if($rooms->count() > 0): ?>
            <?php $__currentLoopData = $rooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $room): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $schedule = $roomSchedules->get($room->id, collect());
                $busyCount = $schedule->count();
            ?>
            <div class="room-card <?php echo e($index === 0 ? 'open' : ''); ?>" id="room-card-<?php echo e($room->id); ?>">
                <!-- Room Card Header -->
                <div class="room-card-header" onclick="toggleRoomCard(<?php echo e($room->id); ?>)">
                    <div style="display:flex;align-items:center;gap:14px">
                        <span id="chevron-r-<?php echo e($room->id); ?>" style="font-size:14px;color:#94a3b8;transition:transform 0.2s;display:inline-block;<?php echo e($index === 0 ? 'transform:rotate(90deg)' : ''); ?>">▶</span>
                        <div>
                            <div style="font-family:'Outfit',sans-serif;font-size:18px;font-weight:800;color:#0f172a">
                                📍 <?php echo e($room->room_number); ?>

                            </div>
                            <div style="font-size:12px;color:#64748b;margin-top:2px;font-weight:500">
                                <?php echo e($room->building_block ?? 'Main Block'); ?> • 🪑 <?php echo e($room->capacity); ?> Seats • <?php echo e($room->class_sections_count); ?> Section(s) Assigned
                            </div>
                        </div>
                    </div>
                    <div style="display:flex;align-items:center;gap:10px">
                        <?php if($busyCount > 0): ?>
                            <span class="status-tag status-busy"><?php echo e($busyCount); ?> Slot(s) Busy</span>
                        <?php else: ?>
                            <span class="status-tag status-free">Free All Day</span>
                        <?php endif; ?>
                        <button type="button" class="btn btn-ghost btn-sm" onclick="event.stopPropagation(); openEditRoomModal(<?php echo e($room->id); ?>, '<?php echo e(addslashes($room->room_number)); ?>', '<?php echo e(addslashes($room->building_block ?? '')); ?>', <?php echo e($room->capacity); ?>)" style="padding:6px 12px;font-size:12px;color:#0284c7;border-color:#bae6fd;background:#f0f9ff">
                            ✏️ Edit
                        </button>
                        <button type="button" class="btn btn-danger btn-sm" onclick="event.stopPropagation(); triggerDeleteModal('<?php echo e(route('principal.rooms.destroy', $room)); ?>', '<?php echo e(addslashes($room->room_number)); ?>')" style="padding:6px 12px;font-size:12px">
                            🗑️ Delete
                        </button>
                    </div>
                </div>

                <!-- Room Card Body: Schedule -->
                <div class="room-card-body">
                    <div style="margin-top:16px;margin-bottom:12px;font-size:14px;font-weight:700;color:#0f172a">
                        📋 <?php echo e(ucfirst($selectedDay)); ?>'s Schedule for <?php echo e($room->room_number); ?>

                        <?php if(strtolower(now()->format('l')) === $selectedDay): ?>
                            <span style="color:#e11d48;font-size:12px;margin-left:6px">(Today)</span>
                        <?php endif; ?>
                    </div>

                    <?php if($schedule->count() > 0): ?>
                        <?php $__currentLoopData = $schedule; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="schedule-slot busy">
                            <div class="time-badge">
                                🕐 <?php echo e(\Carbon\Carbon::parse($slot->start_time)->format('g:i A')); ?> - <?php echo e(\Carbon\Carbon::parse($slot->end_time)->format('g:i A')); ?>

                            </div>
                            <div class="slot-info">
                                <div class="slot-subject">
                                    <?php echo e($slot->subject->subject_name ?? 'Subject'); ?>

                                    (<?php echo e($slot->section->instituteClass->custom_name ?? 'Class'); ?> <?php echo e($slot->section->section_name ?? ''); ?>)
                                </div>
                                <div class="slot-details">
                                    👤 Teacher: <strong style="color:#0f172a"><?php echo e($slot->teacher->name ?? 'TBA'); ?></strong>
                                </div>
                            </div>
                            <span class="status-tag status-busy">BUSY</span>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php else: ?>
                        <div class="schedule-slot free">
                            <div class="time-badge" style="background:#ecfdf5;border-color:#a7f3d0;color:#059669">
                                All Day
                            </div>
                            <div class="slot-info">
                                <div class="slot-subject" style="color:#059669">
                                    ✅ No classes scheduled
                                </div>
                                <div class="slot-details">
                                    This room is free for the entire <?php echo e(ucfirst($selectedDay)); ?>.
                                </div>
                            </div>
                            <span class="status-tag status-free">FREE</span>
                        </div>
                    <?php endif; ?>

                    <div style="margin-top:16px;text-align:right">
                        <a href="<?php echo e(route('principal.timetables.index')); ?>" class="btn btn-ghost btn-sm" style="font-size:12px;color:#0284c7;border-color:#bae6fd;background:#f0f9ff">
                            🗓️ Edit Schedule in Timetable Matrix →
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php else: ?>
        <div class="card">
            <p style="color:#64748b;font-size:14px;padding:12px 0">No rooms provisioned yet. Use the panel on the right to add your first classroom.</p>
        </div>
        <?php endif; ?>
    </div>

    <!-- Right Side: Add Room Form -->
    <div>
        <div class="card" style="position:sticky;top:84px">
            <div class="card-header">
                <div class="card-title">➕ Add New Room / Lab</div>
            </div>

            <form method="POST" action="<?php echo e(route('principal.rooms.store')); ?>">
                <?php echo csrf_field(); ?>

                <!-- Room Number / Name -->
                <div class="form-group">
                    <label for="room_number">Room / Lab Name *</label>
                    <input id="room_number" type="text" name="room_number" placeholder="e.g. Room 101, Lab 2, Hall A" required value="<?php echo e(old('room_number')); ?>">
                    <?php $__errorArgs = ['room_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="form-error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <!-- Building / Block -->
                <div class="form-group">
                    <label for="building_block">Building / Block (Optional)</label>
                    <input id="building_block" type="text" name="building_block" placeholder="e.g. Science Wing, Main Building" value="<?php echo e(old('building_block')); ?>">
                    <?php $__errorArgs = ['building_block'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="form-error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <!-- Room Capacity -->
                <div class="form-group">
                    <label for="capacity">Seating Capacity (Max Students) *</label>
                    <input id="capacity" type="number" name="capacity" placeholder="e.g. 40" min="1" max="1000" required value="<?php echo e(old('capacity', 40)); ?>">
                    <?php $__errorArgs = ['capacity'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="form-error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
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
            <button type="button" onclick="closeDeleteModal()" style="background:none;border:none;color:#64748b;font-size:20px;cursor:pointer">&times;</button>
        </div>
        <div class="modal-body" id="deleteModalMessage">
            Are you sure you want to delete this room?
        </div>
        <div class="modal-actions">
            <button type="button" class="btn btn-ghost" onclick="closeDeleteModal()">Cancel</button>
            <form id="deleteModalForm" method="POST" action="">
                <?php echo csrf_field(); ?>
                <?php echo method_field('DELETE'); ?>
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
            <button type="button" onclick="closeEditRoomModal()" style="background:none;border:none;color:#64748b;font-size:20px;cursor:pointer">&times;</button>
        </div>
        <form id="editRoomForm" method="POST" action="">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>
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
        msg.innerHTML = 'Are you sure you want to delete room <strong style="color:#0f172a">' + roomName + '</strong>?<br>Sections assigned to this room will become unassigned. Timetable slots for this room will lose their room assignment.';
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
<?php $__env->stopSection(); ?>

<?php echo $__env->make(auth()->check() && auth()->user()->isTeacher() ? 'layouts.app' : 'principal.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\UPLYFT\uplifyt\resources\views\principal\rooms\index.blade.php ENDPATH**/ ?>