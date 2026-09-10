<?php $__env->startSection('title', 'LMS & Assignments'); ?>
<?php $__env->startSection('page-header', 'Module 6: Learning Management'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">

    <!-- LMS TABS -->
    <div class="flex gap-4 border-b border-slate-200 pb-2 mb-6">
        <button class="px-4 py-2 text-sm font-extrabold text-pink-600 border-b-2 border-pink-600" id="tabCreateBtn" onclick="switchTab('create')">
            <i class="fa-solid fa-plus mr-2"></i>Create Assignment
        </button>
        <button class="px-4 py-2 text-sm font-bold text-slate-500 hover:text-slate-800 transition" id="tabGradeBtn" onclick="switchTab('grade')">
            <i class="fa-solid fa-marker mr-2"></i>Manual Grading
        </button>
    </div>

    <!-- CREATE ASSIGNMENT TAB -->
    <div id="tabCreate" class="liquid-glass-card p-6 md:p-8 border border-slate-200/90 shadow-2xs relative glass-specular-top">
        <div class="border-b border-slate-200 pb-4 mb-6">
            <h2 class="text-xl md:text-2xl font-extrabold text-slate-900 font-display">New Assignment / Quiz</h2>
            <p class="text-xs text-slate-500 font-medium mt-0.5">Distribute course material or assessments to your enrolled class.</p>
        </div>

        <form method="POST" action="#" enctype="multipart/form-data" class="space-y-5 max-w-2xl">
            <?php echo csrf_field(); ?>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="type" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Assessment Type <span class="text-rose-500">*</span></label>
                    <select name="type" id="type" class="w-full p-3 rounded-xl text-sm bg-white text-slate-900 border border-slate-300 shadow-2xs font-medium outline-none focus:border-pink-500" required>
                        <option value="assignment">Assignment</option>
                        <option value="quiz">Quiz</option>
                        <option value="exam">Exam</option>
                    </select>
                </div>
                <div>
                    <label for="deadline" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Submission Deadline</label>
                    <input type="datetime-local" name="deadline" id="deadline" class="w-full p-3 rounded-xl text-sm bg-white text-slate-900 border border-slate-300 shadow-2xs font-medium outline-none focus:border-pink-500">
                </div>
            </div>

            <div>
                <label for="title" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Title <span class="text-rose-500">*</span></label>
                <input type="text" name="title" id="title" placeholder="e.g. Chapter 1: Introduction to Physics" class="w-full p-3 rounded-xl text-sm bg-white text-slate-900 border border-slate-300 shadow-2xs font-medium outline-none focus:border-pink-500" required>
            </div>

            <div>
                <label for="description_message" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Description / Instructions</label>
                <textarea name="description_message" id="description_message" rows="4" class="w-full p-3 rounded-xl text-sm bg-white text-slate-900 border border-slate-300 shadow-2xs font-medium outline-none focus:border-pink-500" placeholder="Provide instructions for the students..."></textarea>
            </div>

            <!-- File Upload -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Attachment (PDF/Docs)</label>
                <input type="file" name="file_attachment" class="w-full p-2.5 rounded-xl text-xs bg-slate-50 border border-slate-300 shadow-2xs text-slate-700">
            </div>

            <!-- AI GRADING TOGGLE -->
            <div class="mt-6 p-4 rounded-xl border border-pink-200 bg-pink-50/50 flex items-center justify-between">
                <div>
                    <h4 class="text-sm font-extrabold text-slate-900 flex items-center gap-2 font-display">
                        <i class="fa-solid fa-robot text-pink-600"></i> Use UPLYFT AI Chatbot for Auto-Grading
                    </h4>
                    <p class="text-xs text-slate-600 font-medium mt-0.5">Enable experimental automated grading based on rubric.</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="is_ai_graded" value="1" class="sr-only peer">
                    <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-pink-600"></div>
                </label>
            </div>

            <button type="button" class="w-full mt-4 btn-primary py-3.5 px-4 text-sm font-bold shadow-md flex items-center justify-center gap-2" onclick="alert('Assignment Published!')">
                <i class="fa-solid fa-paper-plane"></i>
                <span>Publish Assignment</span>
            </button>
        </form>
    </div>

    <!-- MANUAL GRADING TAB (STATIC UI) -->
    <div id="tabGrade" class="liquid-glass-card p-6 md:p-8 border border-slate-200/90 shadow-2xs hidden">
        <div class="border-b border-slate-200 pb-4 mb-6">
            <h2 class="text-xl md:text-2xl font-extrabold text-slate-900 font-display">Manual Exam Grading</h2>
            <p class="text-xs text-slate-500 font-medium mt-0.5">Input marks manually for paper-based exams.</p>
        </div>

        <div class="overflow-x-auto rounded-xl border border-slate-200/80">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs text-slate-700 font-extrabold uppercase tracking-wider">
                        <th class="py-3.5 px-5">Student Name</th>
                        <th class="py-3.5 px-5">Assignment/Exam</th>
                        <th class="py-3.5 px-5">Marks Obtained</th>
                        <th class="py-3.5 px-5">Total Marks</th>
                        <th class="py-3.5 px-5 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="text-sm text-slate-700 divide-y divide-slate-100 bg-white">
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-3 px-5 text-slate-900 font-bold">Sarah Connor</td>
                        <td class="py-3 px-5 text-slate-600 font-medium">Midterm Exam</td>
                        <td class="py-3 px-5"><input type="number" class="w-20 p-2 rounded-lg text-xs font-bold text-center bg-white border border-slate-300 text-slate-900" value="85"></td>
                        <td class="py-3 px-5"><input type="number" class="w-20 p-2 rounded-lg text-xs font-bold text-center bg-white border border-slate-300 text-slate-900" value="100"></td>
                        <td class="py-3 px-5 text-center"><button class="btn-primary px-3 py-1.5 text-xs font-bold shadow-2xs">Save</button></td>
                    </tr>
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-3 px-5 text-slate-900 font-bold">John Doe</td>
                        <td class="py-3 px-5 text-slate-600 font-medium">Midterm Exam</td>
                        <td class="py-3 px-5"><input type="number" class="w-20 p-2 rounded-lg text-xs font-bold text-center bg-white border border-slate-300 text-slate-900" value="92"></td>
                        <td class="py-3 px-5"><input type="number" class="w-20 p-2 rounded-lg text-xs font-bold text-center bg-white border border-slate-300 text-slate-900" value="100"></td>
                        <td class="py-3 px-5 text-center"><button class="btn-primary px-3 py-1.5 text-xs font-bold shadow-2xs">Save</button></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
    function switchTab(tab) {
        const createTab = document.getElementById('tabCreate');
        const gradeTab = document.getElementById('tabGrade');
        const createBtn = document.getElementById('tabCreateBtn');
        const gradeBtn = document.getElementById('tabGradeBtn');

        if (tab === 'create') {
            createTab.classList.remove('hidden');
            gradeTab.classList.add('hidden');
            createBtn.className = "px-4 py-2 text-sm font-extrabold text-pink-600 border-b-2 border-pink-600";
            gradeBtn.className = "px-4 py-2 text-sm font-bold text-slate-500 hover:text-slate-800 transition";
        } else {
            createTab.classList.add('hidden');
            gradeTab.classList.remove('hidden');
            gradeBtn.className = "px-4 py-2 text-sm font-extrabold text-pink-600 border-b-2 border-pink-600";
            createBtn.className = "px-4 py-2 text-sm font-bold text-slate-500 hover:text-slate-800 transition";
        }
    }
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\UPLYFT\uplifyt\resources\views\teacher\lms.blade.php ENDPATH**/ ?>