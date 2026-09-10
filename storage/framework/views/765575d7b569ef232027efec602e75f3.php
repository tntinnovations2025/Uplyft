<?php
    $routePrefix = 'principal.scholarships.';
    if (auth()->check() && !auth()->user()->isPrincipal() && !auth()->user()->isGlobalAdmin()) {
        $routePrefix = auth()->user()->getStaffUrlPrefix() . '.scholarships.';
    }
?>

<?php $__env->startSection('title', 'Scholarships & Concessions'); ?>
<?php $__env->startSection('breadcrumb', 'Scholarships'); ?>

<?php $__env->startSection('content'); ?>
<div class="content-header" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:16px">
    <div>
        <h1 style="font-family:'Outfit',sans-serif;font-size:24px;font-weight:800;color:#0f172a;margin:0 0 4px;letter-spacing:-0.5px">
            🎓 Scholarships &amp; Concessions
        </h1>
        <p style="color:#64748b;font-size:13px;margin:0;font-weight:500">
            Define institutional discount categories (e.g., Kinship, Merit, Need-Based), set maximum discount percentages, and establish verification criteria.
        </p>
    </div>
    <?php if(auth()->user()->hasPermission('scholarships', 'edit')): ?>
        <button onclick="openCreateModal()" class="btn btn-primary">
            <span>➕ Add Scholarship Policy</span>
        </button>
    <?php else: ?>
        <div style="font-size:12px;color:#0284c7;font-weight:700;padding:8px 14px;background:#f0f9ff;border:1px solid #bae6fd;border-radius:8px;display:flex;align-items:center;gap:6px">
            <span>👁️</span> <span>View Only Access (Read-Only)</span>
        </div>
    <?php endif; ?>
</div>

<?php if(session('success')): ?>
    <div class="alert alert-success" style="margin-bottom:24px">
        <span>✅</span> <span><?php echo e(session('success')); ?></span>
    </div>
<?php endif; ?>

<!-- SCHOLARSHIP POLICIES GRID -->
<div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(340px, 1fr));gap:20px;margin-bottom:30px">
    <?php $__empty_1 = true; $__currentLoopData = $scholarships; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $scholarship): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="card" style="background:#ffffff;border:1px solid rgba(226,232,240,0.85);box-shadow:0 4px 20px -2px rgba(0,0,0,0.04);border-radius:16px;padding:24px;display:flex;flex-direction:column;justify-content:space-between;transition:transform 0.2s ease">
            <div>
                <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:14px">
                    <div>
                        <span class="badge <?php echo e($scholarship->is_active ? 'badge-green' : 'badge-yellow'); ?>" style="margin-bottom:8px">
                            <?php echo e($scholarship->is_active ? 'Active Policy' : 'Disabled'); ?>

                        </span>
                        <h3 style="font-size:18px;font-weight:800;color:#0f172a;margin:4px 0 2px"><?php echo e($scholarship->title); ?></h3>
                        <p style="font-size:12px;color:#64748b;margin:0;font-weight:500"><?php echo e($scholarship->description ?? 'No description provided.'); ?></p>
                    </div>
                    <div style="background:#ecfdf5;border:1px solid #a7f3d0;padding:8px 14px;border-radius:12px;text-align:center;flex-shrink:0">
                        <div style="font-size:18px;font-weight:900;color:#059669"><?php echo e(number_format($scholarship->discount_percentage, 0)); ?>%</div>
                        <div style="font-size:10px;text-transform:uppercase;color:#059669;font-weight:800">Discount</div>
                    </div>
                </div>

                <div style="background:#fdf4ff;border:1px solid #f5d0fe;border-radius:12px;padding:14px;margin:14px 0">
                    <div style="font-size:11px;font-weight:800;text-transform:uppercase;color:#9333ea;letter-spacing:0.5px;margin-bottom:6px">
                        📋 Required Verification Questions (<?php echo e(is_array($scholarship->questions) ? count($scholarship->questions) : 0); ?>)
                    </div>
                    <?php if(!empty($scholarship->questions) && is_array($scholarship->questions)): ?>
                        <ul style="margin:0;padding-left:18px;font-size:12px;color:#334155">
                            <?php $__currentLoopData = $scholarship->questions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $q): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li style="margin-bottom:3px">
                                    <strong style="color:#0f172a"><?php echo e($q['label']); ?></strong>
                                    <span style="font-size:11px;color:#64748b">(<?php echo e($q['type']); ?><?php echo e(!empty($q['required']) ? ', Required' : ''); ?>)</span>
                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    <?php else: ?>
                        <span style="font-size:12px;color:#64748b;font-style:italic">No verification questions configured. Admission staff can grant without details.</span>
                    <?php endif; ?>
                </div>
            </div>

            <div style="display:flex;justify-content:space-between;align-items:center;margin-top:14px;padding-top:14px;border-top:1px solid #e2e8f0">
                <div style="font-size:12px;color:#64748b;font-weight:600">
                    <span>👨‍🎓 Beneficiaries: <strong style="color:#0f172a"><?php echo e($scholarship->students_count); ?> students</strong></span>
                </div>
                <?php if(auth()->user()->hasPermission('scholarships', 'edit')): ?>
                    <div style="display:flex;gap:6px">
                        <button onclick='openEditModal(<?php echo json_encode($scholarship, 15, 512) ?>)' class="btn btn-ghost btn-sm" style="color:#0284c7;border-color:#bae6fd;background:#f0f9ff">
                            ✏️ Edit Rules
                        </button>
                        <form method="POST" action="<?php echo e(route($routePrefix . 'destroy', $scholarship)); ?>" onsubmit="return confirm('Are you sure you want to delete this scholarship policy?')" style="display:inline">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="btn btn-liquid-red btn-sm" style="padding:5px 12px;font-size:11.5px;">
                                Delete
                            </button>
                        </form>
                    </div>
                <?php else: ?>
                    <span style="font-size:11px;color:#0284c7;font-weight:700">👁️ Read-Only</span>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div style="grid-column:1 / -1;background:#ffffff;border:1px dashed #cbd5e1;border-radius:16px;padding:40px;text-align:center;box-shadow:0 4px 20px -2px rgba(0,0,0,0.04)">
            <div style="font-size:40px;margin-bottom:10px">🎓</div>
            <h3 style="color:#0f172a;font-weight:800;margin:0 0 6px">No Scholarship Policies Configured Yet</h3>
            <p style="color:#64748b;font-size:13px;max-width:450px;margin:0 auto 16px">
                Create custom scholarship percentage rules (such as Kinship Scholarship, Merit Award, Staff Child Discount) and require specific verification questions from admission officers.
            </p>
            <?php if(auth()->user()->hasPermission('scholarships', 'edit')): ?>
                <button onclick="openCreateModal()" class="btn btn-primary">
                    ➕ Create Kinship / Merit Scholarship Policy
                </button>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<!-- CREATE / EDIT SCHOLARSHIP POLICY MODAL -->
<div id="scholarshipModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(15,23,42,0.5);backdrop-filter:blur(8px);z-index:9999;align-items:center;justify-content:center;padding:20px;overflow-y:auto">
    <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:20px;width:100%;max-width:650px;padding:28px;box-shadow:0 25px 60px rgba(0,0,0,0.15);margin:auto;color:#0f172a">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;padding-bottom:12px;border-bottom:1px solid #e2e8f0">
            <h2 id="modalTitle" style="font-family:'Outfit',sans-serif;font-size:20px;font-weight:800;color:#0f172a;margin:0">
                🎓 Configure Scholarship Policy
            </h2>
            <button onclick="closeModal()" style="background:none;border:none;color:#64748b;font-size:24px;cursor:pointer">&times;</button>
        </div>

        <form id="scholarshipForm" method="POST" action="<?php echo e(route($routePrefix . 'store')); ?>">
            <?php echo csrf_field(); ?>
            <input type="hidden" id="method_field" name="_method" value="POST">

            <div class="form-group">
                <label>Scholarship Name / Title <span style="color:#ef4444">*</span></label>
                <input type="text" id="policy_title" name="title" required placeholder="e.g. Kinship Scholarship, Academic Merit Award, Teacher Child" class="form-control">
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px">
                <div class="form-group" style="margin:0">
                    <label>Discount Percentage (%) <span style="color:#ef4444">*</span></label>
                    <div style="position:relative">
                        <input type="number" id="policy_discount" name="discount_percentage" step="0.5" min="0" max="100" required placeholder="20" class="form-control" style="padding-right:35px">
                        <span style="position:absolute;right:14px;top:50%;transform:translateY(-50%);color:#64748b;font-weight:800">%</span>
                    </div>
                    <span style="font-size:11px;color:#64748b">Granted discount applied to base admission fee</span>
                </div>
                <div class="form-group" style="margin:0">
                    <label>Policy Status</label>
                    <select id="policy_is_active" name="is_active" class="form-control">
                        <option value="1">Active (Selectable by Admission Staff)</option>
                        <option value="0">Disabled / Inactive</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Description / Criteria &amp; Rules</label>
                <textarea id="policy_description" name="description" rows="2" placeholder="Briefly describe who is eligible for this discount..." class="form-control"></textarea>
            </div>

            <!-- CUSTOM VERIFICATION QUESTIONS BUILDER -->
            <div style="background:#fdf4ff;border:1px solid #f5d0fe;border-radius:14px;padding:16px;margin-bottom:24px">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
                    <div>
                        <span style="font-size:13px;font-weight:800;color:#9333ea;text-transform:uppercase;letter-spacing:0.5px">
                            ❓ Verification Questions (Required Answers)
                        </span>
                        <p style="font-size:11px;color:#64748b;margin:2px 0 0;font-weight:500">
                            Admission staff must fill these details (e.g. Sibling Roll No, Father Name, Session) before applying this discount.
                        </p>
                    </div>
                    <button type="button" onclick="addQuestionRow()" class="btn btn-ghost btn-sm" style="border-color:#f5d0fe;color:#9333ea;background:#ffffff">
                        ➕ Add Question
                    </button>
                </div>

                <div id="questionsContainer" style="display:flex;flex-direction:column;gap:10px">
                    <!-- Dynamic Question Rows injected via JS -->
                </div>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:12px;border-top:1px solid #e2e8f0;padding-top:16px">
                <button type="button" onclick="closeModal()" class="btn btn-ghost">Cancel</button>
                <button type="submit" class="btn btn-primary">💾 Save Policy &amp; Questions</button>
            </div>
        </form>
    </div>
</div>

<script>
let questionCount = 0;

function openCreateModal() {
    document.getElementById('modalTitle').innerText = '🎓 Create New Scholarship Policy';
    document.getElementById('scholarshipForm').action = "<?php echo e(route($routePrefix . 'store')); ?>";
    document.getElementById('method_field').value = 'POST';
    document.getElementById('policy_title').value = '';
    document.getElementById('policy_discount').value = '';
    document.getElementById('policy_description').value = '';
    document.getElementById('policy_is_active').value = '1';
    
    document.getElementById('questionsContainer').innerHTML = '';
    questionCount = 0;
    
    // Add default template for Kinship Scholarship if creating
    addQuestionRow('Kin Sibling Name', 'text', true);
    addQuestionRow('Sibling Father Name', 'text', true);
    addQuestionRow('Sibling Roll Number / Student ID', 'text', true);
    addQuestionRow('Sibling Class & Section', 'text', false);
    addQuestionRow('Sibling Session (e.g. 2024-2026 or Enrolled)', 'text', false);

    document.getElementById('scholarshipModal').style.display = 'flex';
}

function openEditModal(scholarship) {
    document.getElementById('modalTitle').innerText = '✏️ Edit Scholarship Policy: ' + scholarship.title;
    let updateUrlTemplate = "<?php echo e(route($routePrefix . 'update', 9999)); ?>";
    document.getElementById('scholarshipForm').action = updateUrlTemplate.replace('9999', scholarship.id);
    document.getElementById('method_field').value = 'PUT';
    document.getElementById('policy_title').value = scholarship.title;
    document.getElementById('policy_discount').value = scholarship.discount_percentage;
    document.getElementById('policy_description').value = scholarship.description || '';
    document.getElementById('policy_is_active').value = scholarship.is_active ? '1' : '0';
    
    document.getElementById('questionsContainer').innerHTML = '';
    questionCount = 0;

    if (scholarship.questions && Array.isArray(scholarship.questions) && scholarship.questions.length > 0) {
        scholarship.questions.forEach(q => {
            addQuestionRow(q.label, q.type || 'text', q.required || false, q.id || '');
        });
    } else {
        addQuestionRow('', 'text', true);
    }

    document.getElementById('scholarshipModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('scholarshipModal').style.display = 'none';
}

function addQuestionRow(label = '', type = 'text', required = true, id = '') {
    const container = document.getElementById('questionsContainer');
    const rowId = 'q_row_' + questionCount;
    
    const div = document.createElement('div');
    div.id = rowId;
    div.style.cssText = "display:flex;gap:8px;align-items:center;background:#ffffff;padding:8px 12px;border-radius:10px;border:1px solid #e2e8f0";
    
    div.innerHTML = `
        <input type="hidden" name="questions[${questionCount}][id]" value="${id}">
        <div style="flex:1">
            <input type="text" name="questions[${questionCount}][label]" value="${escapeHtml(label)}" placeholder="Question Label (e.g. Sibling Roll No)" class="form-control" style="padding:8px 12px;font-size:12px;background:#ffffff;border:1px solid #cbd5e1;border-radius:8px;color:#0f172a" required>
        </div>
        <div style="width:110px">
            <select name="questions[${questionCount}][type]" class="form-control" style="padding:8px 12px;font-size:12px;background:#ffffff;border:1px solid #cbd5e1;border-radius:8px;color:#0f172a">
                <option value="text" ${type === 'text' ? 'selected' : ''}>Text</option>
                <option value="number" ${type === 'number' ? 'selected' : ''}>Number</option>
                <option value="date" ${type === 'date' ? 'selected' : ''}>Date</option>
            </select>
        </div>
        <label style="display:flex;align-items:center;gap:4px;font-size:11px;color:#64748b;cursor:pointer;white-space:nowrap;margin:0;font-weight:700">
            <input type="checkbox" name="questions[${questionCount}][required]" value="1" ${required ? 'checked' : ''}> Required
        </label>
        <button type="button" onclick="document.getElementById('${rowId}').remove()" style="background:none;border:none;color:#ef4444;cursor:pointer;font-size:14px;padding:4px">🗑️</button>
    `;
    
    container.appendChild(div);
    questionCount++;
}

function escapeHtml(text) {
    return text ? String(text).replace(/"/g, '&quot;') : '';
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make(auth()->check() && !auth()->user()->isPrincipal() && !auth()->user()->isGlobalAdmin() ? 'layouts.app' : 'principal.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\UPLYFT\uplifyt\resources\views\principal\scholarships\index.blade.php ENDPATH**/ ?>