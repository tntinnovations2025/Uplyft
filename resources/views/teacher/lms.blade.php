@extends('layouts.app')

@section('title', 'LMS & Assignments')
@section('page-header', 'Module 6: Learning Management')

@section('content')
<div class="space-y-6">

    <!-- LMS TABS -->
    <div class="flex gap-4 border-b border-white/10 pb-2 mb-6">
        <button class="px-4 py-2 text-sm font-bold text-cyan-400 border-b-2 border-cyan-400" id="tabCreateBtn" onclick="switchTab('create')">
            <i class="fa-solid fa-plus mr-2"></i>Create Assignment
        </button>
        <button class="px-4 py-2 text-sm font-bold text-slate-400 hover:text-white transition" id="tabGradeBtn" onclick="switchTab('grade')">
            <i class="fa-solid fa-marker mr-2"></i>Manual Grading
        </button>
    </div>

    <!-- CREATE ASSIGNMENT TAB -->
    <div id="tabCreate" class="glass-panel p-6">
        <div class="border-b border-white/10 pb-4 mb-6">
            <h2 class="text-xl font-bold text-white">New Assignment / Quiz</h2>
            <p class="text-xs text-slate-400">Distribute course material or assessments to your enrolled class.</p>
        </div>

        <form method="POST" action="#" enctype="multipart/form-data" class="space-y-5 max-w-2xl">
            @csrf
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="type" class="block text-xs font-medium text-slate-300 mb-1">Assessment Type</label>
                    <select name="type" id="type" class="w-full glass-input p-3 rounded-xl text-sm" required>
                        <option value="assignment">Assignment</option>
                        <option value="quiz">Quiz</option>
                        <option value="exam">Exam</option>
                    </select>
                </div>
                <div>
                    <label for="deadline" class="block text-xs font-medium text-slate-300 mb-1">Submission Deadline</label>
                    <input type="datetime-local" name="deadline" id="deadline" class="w-full glass-input p-3 rounded-xl text-sm">
                </div>
            </div>

            <div>
                <label for="title" class="block text-xs font-medium text-slate-300 mb-1">Title</label>
                <input type="text" name="title" id="title" placeholder="e.g. Chapter 1: Introduction to Physics" class="w-full glass-input p-3 rounded-xl text-sm" required>
            </div>

            <div>
                <label for="description_message" class="block text-xs font-medium text-slate-300 mb-1">Description / Instructions</label>
                <textarea name="description_message" id="description_message" rows="4" class="w-full glass-input p-3 rounded-xl text-sm" placeholder="Provide instructions for the students..."></textarea>
            </div>

            <!-- File Upload -->
            <div>
                <label class="block text-xs font-medium text-slate-300 mb-1">Attachment (PDF/Docs)</label>
                <input type="file" name="file_attachment" class="w-full glass-input p-2 rounded-xl text-sm bg-slate-900/40">
            </div>

            <!-- AI GRADING TOGGLE -->
            <div class="mt-6 p-4 rounded-xl border border-indigo-500/40 bg-indigo-500/10 flex items-center justify-between">
                <div>
                    <h4 class="text-sm font-bold text-indigo-300 flex items-center gap-2">
                        <i class="fa-solid fa-robot"></i> Use UPLYFT AI Chatbot for Auto-Grading
                    </h4>
                    <p class="text-[11px] text-slate-400 mt-1">Enable experimental automated grading based on rubric.</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="is_ai_graded" value="1" class="sr-only peer">
                    <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-500"></div>
                </label>
            </div>

            <button type="button" class="w-full mt-4 py-3.5 px-4 rounded-xl bg-gradient-to-r from-cyan-600 to-cyan-700 hover:from-cyan-500 hover:to-cyan-600 text-white font-semibold text-sm shadow-lg shadow-cyan-600/30 flex items-center justify-center gap-2 transition" onclick="alert('Assignment Published!')">
                <i class="fa-solid fa-paper-plane"></i>
                <span>Publish Assignment</span>
            </button>
        </form>
    </div>

    <!-- MANUAL GRADING TAB (STATIC UI) -->
    <div id="tabGrade" class="glass-panel p-6 hidden">
        <div class="border-b border-white/10 pb-4 mb-6">
            <h2 class="text-xl font-bold text-white">Manual Exam Grading</h2>
            <p class="text-xs text-slate-400">Input marks manually for paper-based exams.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-900/50 border-b border-white/10 text-xs text-slate-400 uppercase tracking-wider">
                        <th class="py-4 px-6">Student Name</th>
                        <th class="py-4 px-6">Assignment/Exam</th>
                        <th class="py-4 px-6">Marks Obtained</th>
                        <th class="py-4 px-6">Total Marks</th>
                        <th class="py-4 px-6 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="text-sm text-slate-300">
                    <tr class="border-b border-white/5 hover:bg-white/5 transition">
                        <td class="py-3 px-6 text-white font-semibold">Sarah Connor</td>
                        <td class="py-3 px-6 text-slate-400">Midterm Exam</td>
                        <td class="py-3 px-6"><input type="number" class="glass-input p-2 rounded-lg text-xs w-20 text-center" value="85"></td>
                        <td class="py-3 px-6"><input type="number" class="glass-input p-2 rounded-lg text-xs w-20 text-center" value="100"></td>
                        <td class="py-3 px-6 text-center"><button class="px-3 py-1.5 rounded-lg bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-semibold">Save</button></td>
                    </tr>
                    <tr class="border-b border-white/5 hover:bg-white/5 transition">
                        <td class="py-3 px-6 text-white font-semibold">John Doe</td>
                        <td class="py-3 px-6 text-slate-400">Midterm Exam</td>
                        <td class="py-3 px-6"><input type="number" class="glass-input p-2 rounded-lg text-xs w-20 text-center" value="92"></td>
                        <td class="py-3 px-6"><input type="number" class="glass-input p-2 rounded-lg text-xs w-20 text-center" value="100"></td>
                        <td class="py-3 px-6 text-center"><button class="px-3 py-1.5 rounded-lg bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-semibold">Save</button></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function switchTab(tab) {
        const createTab = document.getElementById('tabCreate');
        const gradeTab = document.getElementById('tabGrade');
        const createBtn = document.getElementById('tabCreateBtn');
        const gradeBtn = document.getElementById('tabGradeBtn');

        if (tab === 'create') {
            createTab.classList.remove('hidden');
            gradeTab.classList.add('hidden');
            createBtn.className = "px-4 py-2 text-sm font-bold text-cyan-400 border-b-2 border-cyan-400";
            gradeBtn.className = "px-4 py-2 text-sm font-bold text-slate-400 hover:text-white transition";
        } else {
            createTab.classList.add('hidden');
            gradeTab.classList.remove('hidden');
            gradeBtn.className = "px-4 py-2 text-sm font-bold text-cyan-400 border-b-2 border-cyan-400";
            createBtn.className = "px-4 py-2 text-sm font-bold text-slate-400 hover:text-white transition";
        }
    }
</script>
@endsection
