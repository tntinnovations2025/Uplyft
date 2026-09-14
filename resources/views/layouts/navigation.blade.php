<aside class="app-sidebar flex flex-col justify-between custom-scrollbar" style="background:#0E0E11;border-right:1px solid #2A2C30;box-shadow:none;">
    <!-- Top Dynamic Multi-Tenant Branding Header -->
    <div>
        @include('partials.brand-header')

        @php
            $roleSubGroup = null;
            $u = auth()->user();
            if ($u) {
                if ($u->role === 'teacher') {
                    if (request()->is('*/attendance*') || request()->is('*/students*') || request()->is('*/directory*') || request()->is('*/scholarships*')) {
                        $roleSubGroup = 'students';
                    } elseif (request()->is('*/schedule*') || request()->is('*/classes-subjects*') || request()->is('*/timetables*') || request()->is('*/rooms*') || request()->routeIs('lms.subjects.*') || request()->routeIs('lms.materials.*') || request()->is('lms/subjects*') || request()->is('lms/materials*')) {
                        $roleSubGroup = 'academics';
                    } elseif (request()->routeIs('lms.assessments.*') || request()->routeIs('lms.mocks.*') || request()->routeIs('lms.test-results.*') || request()->routeIs('lms.datesheet.*') || request()->routeIs('lms.exam-report.*') || request()->routeIs('lms.grades.*') || request()->is('lms/assessments*') || request()->is('lms/mocks*') || request()->is('lms/test-results*') || request()->is('lms/datesheet*') || request()->is('lms/exam-report*') || request()->is('lms/grades*')) {
                        $roleSubGroup = 'exams';
                    } elseif (request()->routeIs('lms.chatbot.*') || request()->routeIs('lms.practice-test.*') || request()->is('lms/chatbot*') || request()->is('lms/practice-test*')) {
                        $roleSubGroup = 'ai';
                    } elseif (request()->is('*/accounts*') || request()->is('*/invoices*')) {
                        $roleSubGroup = 'finance';
                    } elseif (request()->is('*/password-resets*') || request()->is('*/staff*')) {
                        $roleSubGroup = 'security';
                    } elseif (request()->routeIs('profile.*')) {
                        $roleSubGroup = 'settings';
                    }
                } elseif ($u->role === 'student') {
                    if (request()->routeIs('student.courses') || request()->routeIs('student.timetable') || request()->routeIs('student.schedule') || request()->routeIs('student.attendance') || request()->is('student/courses*') || request()->is('student/timetable*') || request()->is('student/attendance*')) {
                        $roleSubGroup = 'academics';
                    } elseif (request()->routeIs('student.lms') || request()->routeIs('student.datesheet') || request()->routeIs('student.examReport') || request()->is('student/lms*') || request()->is('student/datesheet*') || request()->is('student/exam-report*')) {
                        $roleSubGroup = 'exams';
                    } elseif (request()->routeIs('lms.chatbot.*') || request()->routeIs('lms.practice-test.*') || request()->is('lms/chatbot*') || request()->is('lms/practice-test*')) {
                        $roleSubGroup = 'ai';
                    } elseif (request()->routeIs('student.invoices') || request()->routeIs('student.fees') || request()->routeIs('student.profile') || request()->is('student/invoices*') || request()->is('student/fees*')) {
                        $roleSubGroup = 'finance';
                    } elseif (request()->routeIs('profile.*')) {
                        $roleSubGroup = 'settings';
                    }
                } elseif ($u->isPrincipal()) {
                    if (request()->routeIs('principal.students.*') || request()->routeIs('principal.scholarships.*') || request()->is('principal/students*') || request()->is('principal/scholarships*') || request()->routeIs('principal.attendance*') || request()->is('principal/attendance*') || request()->routeIs('principal.directory.*') || request()->is('principal/directory*')) {
                        $roleSubGroup = 'students';
                    } elseif (request()->routeIs('principal.staff.*') || request()->routeIs('principal.teachers.availability.*') || request()->routeIs('*.password-resets.*') || request()->routeIs('principal.rooms.*') || request()->is('principal/staff*') || request()->is('principal/teachers/availability*') || request()->is('*/password-resets*') || request()->is('principal/rooms*')) {
                        $roleSubGroup = 'staff';
                    } elseif (request()->routeIs('*.invoices.*') || request()->routeIs('*.accounts.*') || request()->is('principal/invoices*') || request()->is('principal/accounts*') || (request()->routeIs('principal.settings.*') && (request('tab') === 'financial' || request('tab') === 'bank'))) {
                        $roleSubGroup = 'finance';
                    } elseif (request()->routeIs('principal.academic-terms.*') || request()->routeIs('principal.classes-subjects.*') || request()->routeIs('principal.timetables.*') || request()->is('principal/academic-terms*') || request()->is('principal/classes-subjects*') || request()->is('principal/timetables*')) {
                        $roleSubGroup = 'academic';
                    } elseif (request()->routeIs('lms.chatbot.*') || request()->routeIs('lms.practice-test.*') || request()->routeIs('lms.subjects.*') || request()->routeIs('lms.materials.*') || request()->is('lms/chatbot*') || request()->is('lms/practice-test*') || request()->is('lms/subjects*') || request()->is('lms/materials*')) {
                        $roleSubGroup = 'ai';
                    } elseif (request()->routeIs('lms.assessments.*') || request()->routeIs('lms.mocks.*') || request()->routeIs('lms.test-results.*') || request()->routeIs('lms.datesheet.*') || request()->routeIs('lms.exam-report.*') || request()->routeIs('lms.grades.*') || request()->is('lms/assessments*') || request()->is('lms/mocks*') || request()->is('lms/test-results*') || request()->is('lms/datesheet*') || request()->is('lms/exam-report*') || request()->is('lms/grades*')) {
                        $roleSubGroup = 'exams';
                    } elseif (request()->routeIs('principal.settings.*') || request()->routeIs('principal.organization.campuses.*') || request()->routeIs('principal.attendance-settings.*') || request()->routeIs('principal.security.*') || request()->routeIs('profile.*') || request()->is('principal/settings*') || request()->is('principal/organization/campuses*') || request()->is('principal/attendance-settings*') || request()->is('principal/security*')) {
                        $roleSubGroup = 'settings';
                    }
                }
            }
        @endphp

        <!-- Navigation Links Grouped by Role & Drill-Down Submenus -->
        <nav class="p-3 space-y-2">
            @auth
                <!-- TEACHER / FACULTY / STAFF PORTAL -->
                @if(auth()->user()->role === 'teacher')
                    {{-- Teacher Main Menu --}}
                    <div id="role-main-nav" class="space-y-1.5" style="display: {{ $roleSubGroup ? 'none' : 'block' }};">
                        <a href="{{ auth()->user()->staffUrl('dashboard') }}" class="glossy-nav-item {{ request()->is('*/dashboard') ? 'active' : '' }}" style="margin-bottom: 8px;">
                            <span class="icon"><x-icon name="chart-pie" /></span>
                            <span>Dashboard</span>
                        </a>

                        <div style="padding: 4px 6px 2px; display: flex; align-items: center; justify-content: space-between;">
                            <span style="font-size: 10px; font-weight: 800; color: #8A877E; text-transform: uppercase; letter-spacing: 0.8px;">
                                {{ auth()->user()->staff_role ? strtoupper(auth()->user()->staff_role) . ' HUBS' : 'FACULTY HUBS' }}
                            </span>
                        </div>

                        <!-- 1. Student Operations Hub -->
                        @if(auth()->user()->hasPermission('attendance') || auth()->user()->hasPermission('students') || auth()->user()->hasPermission('students_view'))
                            <button type="button" onclick="showRoleSubNav('students')" class="sidebar-hub-card-btn {{ $roleSubGroup === 'students' ? 'active' : '' }}">
                                <div class="hub-btn-left">
                                    <span class="hub-btn-icon" style="background:rgba(59,130,246,0.15);color:#60a5fa;"><x-icon name="users" /></span>
                                    <div class="hub-btn-text">
                                        <span class="hub-btn-title">Student Operations</span>
                                        <span class="hub-btn-desc">Attendance, Student Roster</span>
                                    </div>
                                </div>
                                <span class="hub-btn-arrow"><x-icon name="chevron-right" class="w-3.5 h-3.5" /></span>
                            </button>
                        @endif

                        <!-- 2. Classroom & Schedule Hub -->
                        <button type="button" onclick="showRoleSubNav('academics')" class="sidebar-hub-card-btn {{ $roleSubGroup === 'academics' ? 'active' : '' }}">
                            <div class="hub-btn-left">
                                <span class="hub-btn-icon" style="background:rgba(168,85,247,0.15);color:#c084fc;"><x-icon name="calendar-days" /></span>
                                <div class="hub-btn-text">
                                    <span class="hub-btn-title">Classes &amp; Schedule</span>
                                    <span class="hub-btn-desc">Schedule, Classes, Notes</span>
                                </div>
                            </div>
                            <span class="hub-btn-arrow"><x-icon name="chevron-right" class="w-3.5 h-3.5" /></span>
                        </button>

                        <!-- 3. Assessments & Exams Hub -->
                        @if(auth()->user()->hasPermission('assessment_engine') || auth()->user()->hasPermission('grading_normalizer'))
                            <button type="button" onclick="showRoleSubNav('exams')" class="sidebar-hub-card-btn {{ $roleSubGroup === 'exams' ? 'active' : '' }}">
                                <div class="hub-btn-left">
                                    <span class="hub-btn-icon" style="background:rgba(20,184,166,0.15);color:#2dd4bf;"><x-icon name="file-signature" /></span>
                                    <div class="hub-btn-text">
                                        <span class="hub-btn-title">Assessments &amp; Exams</span>
                                        <span class="hub-btn-desc">Creator, Tests, Grading</span>
                                    </div>
                                </div>
                                <span class="hub-btn-arrow"><x-icon name="chevron-right" class="w-3.5 h-3.5" /></span>
                            </button>
                        @endif

                        <!-- 4. AI & Study Suite Hub -->
                        @if(auth()->user()->hasPermission('ai_bot'))
                            <button type="button" onclick="showRoleSubNav('ai')" class="sidebar-hub-card-btn {{ $roleSubGroup === 'ai' ? 'active' : '' }}">
                                <div class="hub-btn-left">
                                    <span class="hub-btn-icon" style="background:rgba(236,72,153,0.15);color:#f472b6;"><x-icon name="brain" /></span>
                                    <div class="hub-btn-text">
                                        <span class="hub-btn-title">AI Study Suite</span>
                                        <span class="hub-btn-desc">Assistant, Practice Tests</span>
                                    </div>
                                </div>
                                <span class="hub-btn-arrow"><x-icon name="chevron-right" class="w-3.5 h-3.5" /></span>
                            </button>
                        @endif

                        <!-- 5. Financial Operations Hub (if permitted) -->
                        @if(auth()->user()->hasPermission('accounts') || auth()->user()->hasPermission('invoices') || auth()->user()->hasPermission('staff_salaries'))
                            <button type="button" onclick="showRoleSubNav('finance')" class="sidebar-hub-card-btn {{ $roleSubGroup === 'finance' ? 'active' : '' }}">
                                <div class="hub-btn-left">
                                    <span class="hub-btn-icon" style="background:rgba(34,197,94,0.15);color:#4ade80;"><x-icon name="receipt" /></span>
                                    <div class="hub-btn-text">
                                        <span class="hub-btn-title">Financial Operations</span>
                                        <span class="hub-btn-desc">Ledger, Invoices, Billing</span>
                                    </div>
                                </div>
                                <span class="hub-btn-arrow"><x-icon name="chevron-right" class="w-3.5 h-3.5" /></span>
                            </button>
                        @endif

                        <!-- 6. Staff Directory & Security Hub -->
                        @if(auth()->user()->hasPermission('staff') || auth()->user()->hasPermission('security'))
                            <button type="button" onclick="showRoleSubNav('security')" class="sidebar-hub-card-btn {{ $roleSubGroup === 'security' ? 'active' : '' }}">
                                <div class="hub-btn-left">
                                    <span class="hub-btn-icon" style="background:rgba(148,163,184,0.15);color:#cbd5e1;"><x-icon name="shield-halved" /></span>
                                    <div class="hub-btn-text">
                                        <span class="hub-btn-title">Security &amp; Staff</span>
                                        <span class="hub-btn-desc">Directory, Password Resets</span>
                                    </div>
                                </div>
                                <span class="hub-btn-arrow"><x-icon name="chevron-right" class="w-3.5 h-3.5" /></span>
                            </button>
                        @endif

                        <!-- 7. Settings Hub -->
                        <button type="button" onclick="showRoleSubNav('settings')" class="sidebar-hub-card-btn {{ $roleSubGroup === 'settings' ? 'active' : '' }}">
                            <div class="hub-btn-left">
                                <span class="hub-btn-icon" style="background:rgba(148,163,184,0.15);color:#cbd5e1;"><x-icon name="user-gear" /></span>
                                <div class="hub-btn-text">
                                    <span class="hub-btn-title">Settings</span>
                                    <span class="hub-btn-desc">Profile, Security, Password</span>
                                </div>
                            </div>
                            <span class="hub-btn-arrow"><x-icon name="chevron-right" class="w-3.5 h-3.5" /></span>
                        </button>
                    </div>

                    {{-- Submenu: Students --}}
                    <div id="subnav-students" class="sidebar-subnav-panel space-y-1" style="display: {{ $roleSubGroup === 'students' ? 'block' : 'none' }};">
                        <div class="subnav-header">
                            <button type="button" onclick="backToDashboard()" class="subnav-back-btn">
                                <span class="subnav-back-icon"><x-icon name="arrow-left" class="w-4 h-4" /></span>
                                <span>Back</span>
                            </button>
                            <div class="subnav-badge" style="color:#60a5fa;">
                                <x-icon name="users" class="w-3.5 h-3.5 text-[#60a5fa]" />
                                <span>Student Operations</span>
                            </div>
                        </div>

                        @if(auth()->user()->hasPermission('attendance'))
                            <a href="{{ auth()->user()->staffUrl('attendance') }}" class="glossy-nav-item {{ request()->is('*/attendance*') ? 'active' : '' }}">
                                <span class="icon"><x-icon name="clipboard-check" /></span>
                                <span>Mark Student Attendance</span>
                            </a>
                        @endif
                        @if(auth()->user()->hasPermission('students') || auth()->user()->hasPermission('students_view'))
                            <a href="{{ auth()->user()->staffUrl('students') }}" class="glossy-nav-item {{ (request()->is('*/students') || request()->is('*/students/*')) && !request()->is('*/students/create') ? 'active' : '' }}">
                                <span class="icon"><x-icon name="users" /></span>
                                <span>Student Directory</span>
                            </a>
                        @endif
                        @if(auth()->user()->hasPermission('student_registration'))
                            <a href="{{ auth()->user()->staffUrl('students/create') }}" class="glossy-nav-item {{ request()->is('*/students/create') ? 'active' : '' }}">
                                <span class="icon"><x-icon name="user-plus" /></span>
                                <span>New Student Admission</span>
                            </a>
                        @endif
                        @if(auth()->user()->hasPermission('directory') || auth()->user()->hasPermission('staff_directory'))
                            <a href="{{ auth()->user()->staffUrl('directory') }}" class="glossy-nav-item {{ request()->is('*/directory*') ? 'active' : '' }}">
                                <span class="icon"><x-icon name="building-columns" /></span>
                                <span>Search Profile</span>
                            </a>
                        @endif
                        @if(auth()->user()->hasPermission('scholarships'))
                            <a href="{{ auth()->user()->staffUrl('scholarships') }}" class="glossy-nav-item {{ request()->is('*/scholarships*') ? 'active' : '' }}">
                                <span class="icon"><x-icon name="award" /></span>
                                <span>Scholarship Programs</span>
                            </a>
                        @endif
                    </div>

                    {{-- Submenu: Academics --}}
                    <div id="subnav-academics" class="sidebar-subnav-panel space-y-1" style="display: {{ $roleSubGroup === 'academics' ? 'block' : 'none' }};">
                        <div class="subnav-header">
                            <button type="button" onclick="backToDashboard()" class="subnav-back-btn">
                                <span class="subnav-back-icon"><x-icon name="arrow-left" class="w-4 h-4" /></span>
                                <span>Back</span>
                            </button>
                            <div class="subnav-badge" style="color:#c084fc;">
                                <x-icon name="calendar-days" class="w-3.5 h-3.5 text-[#c084fc]" />
                                <span>Classes &amp; Schedule</span>
                            </div>
                        </div>

                        @if((auth()->user()->isTeacher() && (empty(auth()->user()->staff_role) || strtolower(auth()->user()->staff_role) === 'teacher')) || auth()->user()->hasPermission('timetables'))
                            <a href="{{ auth()->user()->staffUrl('schedule') }}" class="glossy-nav-item {{ request()->is('*/schedule') ? 'active' : '' }}">
                                <span class="icon"><x-icon name="calendar-days" /></span>
                                <span>My Teaching Schedule</span>
                            </a>
                        @endif
                        @if(auth()->user()->hasPermission('classes') || auth()->user()->hasPermission('subjects') || auth()->user()->hasPermission('academics'))
                            <a href="{{ auth()->user()->staffUrl('classes-subjects') }}" class="glossy-nav-item {{ request()->is('*/classes-subjects*') ? 'active' : '' }}">
                                <span class="icon"><x-icon name="layer-group" /></span>
                                <span>Classes &amp; Subjects</span>
                            </a>
                        @endif
                        @if(auth()->user()->hasPermission('lms_content'))
                            <a href="{{ route('lms.subjects.list') }}" class="glossy-nav-item {{ request()->routeIs('lms.subjects.*') || request()->routeIs('lms.materials.*') ? 'active' : '' }}">
                                <span class="icon"><x-icon name="book-bookmark" /></span>
                                <span>Course Materials &amp; Notes</span>
                            </a>
                        @endif
                        @if(auth()->user()->hasPermission('timetables'))
                            <a href="{{ auth()->user()->staffUrl('timetables') }}" class="glossy-nav-item {{ request()->is('*/timetables*') ? 'active' : '' }}">
                                <span class="icon"><x-icon name="calendar-days" /></span>
                                <span>Timetable Matrix</span>
                            </a>
                        @endif
                        @if(auth()->user()->hasPermission('rooms'))
                            <a href="{{ auth()->user()->staffUrl('rooms') }}" class="glossy-nav-item {{ request()->is('*/rooms*') ? 'active' : '' }}">
                                <span class="icon"><x-icon name="door-open" /></span>
                                <span>Campus Rooms</span>
                            </a>
                        @endif
                    </div>

                    {{-- Submenu: Exams --}}
                    <div id="subnav-exams" class="sidebar-subnav-panel space-y-1" style="display: {{ $roleSubGroup === 'exams' ? 'block' : 'none' }};">
                        <div class="subnav-header">
                            <button type="button" onclick="backToDashboard()" class="subnav-back-btn">
                                <span class="subnav-back-icon"><x-icon name="arrow-left" class="w-4 h-4" /></span>
                                <span>Back</span>
                            </button>
                            <div class="subnav-badge" style="color:#2dd4bf;">
                                <x-icon name="file-signature" class="w-3.5 h-3.5 text-[#2dd4bf]" />
                                <span>Assessments &amp; Exams</span>
                            </div>
                        </div>

                        @if(auth()->user()->hasPermission('assessment_engine'))
                            <a href="{{ route('lms.assessments.index') }}" class="glossy-nav-item {{ request()->routeIs('lms.assessments.*') ? 'active' : '' }}">
                                <span class="icon"><x-icon name="file-pen" /></span>
                                <span>Assessment Creator</span>
                            </a>
                            <a href="{{ route('lms.mocks.create') }}" class="glossy-nav-item {{ (request()->routeIs('lms.mocks.create') || request()->routeIs('teacher.mocks.create')) ? 'active' : '' }}">
                                <span class="icon"><x-icon name="bullseye" /></span>
                                <span>🎯 Generate Mocks</span>
                            </a>
                            <a href="{{ route('lms.mocks.index') }}" class="glossy-nav-item {{ (request()->routeIs('lms.mocks.index') || request()->routeIs('teacher.mocks.index') || request()->routeIs('lms.mocks.show') || request()->routeIs('teacher.mocks.show')) ? 'active' : '' }}">
                                <span class="icon"><x-icon name="list-check" /></span>
                                <span>📋 View Mocks</span>
                            </a>
                            <a href="{{ route('lms.test-results.index') }}" class="glossy-nav-item {{ request()->routeIs('lms.test-results.*') ? 'active' : '' }}">
                                <span class="icon"><x-icon name="square-poll-vertical" /></span>
                                <span>Test Results &amp; Grading</span>
                            </a>
                            <a href="{{ route('lms.datesheet.index') }}" class="glossy-nav-item {{ request()->routeIs('lms.datesheet.*') ? 'active' : '' }}">
                                <span class="icon"><x-icon name="calendar-week" /></span>
                                <span>Exam Datesheets</span>
                            </a>
                            <a href="{{ route('lms.exam-report.index') }}" class="glossy-nav-item {{ request()->routeIs('lms.exam-report.*') ? 'active' : '' }}">
                                <span class="icon"><x-icon name="file-lines" /></span>
                                <span>Exam Performance Reports</span>
                            </a>
                        @endif
                        @if(auth()->user()->hasPermission('grading_normalizer'))
                            <a href="{{ route('lms.grades.weightages.page') }}" class="glossy-nav-item {{ request()->routeIs('lms.grades.*') ? 'active' : '' }}">
                                <span class="icon"><x-icon name="scale-balanced" /></span>
                                <span>Grading Weights &amp; Criteria</span>
                            </a>
                        @endif
                    </div>

                    {{-- Submenu: AI --}}
                    <div id="subnav-ai" class="sidebar-subnav-panel space-y-1" style="display: {{ $roleSubGroup === 'ai' ? 'block' : 'none' }};">
                        <div class="subnav-header">
                            <button type="button" onclick="backToDashboard()" class="subnav-back-btn">
                                <span class="subnav-back-icon"><x-icon name="arrow-left" class="w-4 h-4" /></span>
                                <span>Back</span>
                            </button>
                            <div class="subnav-badge" style="color:#f472b6;">
                                <x-icon name="brain" class="w-3.5 h-3.5 text-[#f472b6]" />
                                <span>AI Study Suite</span>
                            </div>
                        </div>

                        @if(Route::has('lms.chatbot.index'))
                            <a href="{{ route('lms.chatbot.index') }}" class="glossy-nav-item {{ request()->routeIs('lms.chatbot.*') ? 'active' : '' }}">
                                <span class="icon"><x-icon name="robot" /></span>
                                <span>AI Study Assistant</span>
                            </a>
                        @endif
                        @if(Route::has('lms.practice-test.index'))
                            <a href="{{ route('lms.practice-test.index') }}" class="glossy-nav-item {{ request()->routeIs('lms.practice-test.*') ? 'active' : '' }}">
                                <span class="icon"><x-icon name="bolt" /></span>
                                <span>AI Practice Tests</span>
                            </a>
                        @endif
                    </div>

                    {{-- Submenu: Finance --}}
                    <div id="subnav-finance" class="sidebar-subnav-panel space-y-1" style="display: {{ $roleSubGroup === 'finance' ? 'block' : 'none' }};">
                        <div class="subnav-header">
                            <button type="button" onclick="backToDashboard()" class="subnav-back-btn">
                                <span class="subnav-back-icon"><x-icon name="arrow-left" class="w-4 h-4" /></span>
                                <span>Back</span>
                            </button>
                            <div class="subnav-badge" style="color:#4ade80;">
                                <x-icon name="receipt" class="w-3.5 h-3.5 text-[#4ade80]" />
                                <span>Financial Operations</span>
                            </div>
                        </div>

                        @if(auth()->user()->hasPermission('accounts') || auth()->user()->hasPermission('staff_salaries'))
                            <a href="{{ auth()->user()->staffUrl('accounts') }}" class="glossy-nav-item {{ request()->is('*/accounts*') ? 'active' : '' }}">
                                <span class="icon"><x-icon name="receipt" /></span>
                                <span>Financial Ledger &amp; Expenses</span>
                            </a>
                        @endif
                        @if(auth()->user()->hasPermission('invoices'))
                            <a href="{{ auth()->user()->staffUrl('invoices') }}" class="glossy-nav-item {{ request()->is('*/invoices*') ? 'active' : '' }}">
                                <span class="icon"><x-icon name="file-invoice-dollar" /></span>
                                <span>Fee Invoices &amp; Billing</span>
                            </a>
                        @endif
                    </div>

                    {{-- Submenu: Security --}}
                    <div id="subnav-security" class="sidebar-subnav-panel space-y-1" style="display: {{ $roleSubGroup === 'security' ? 'block' : 'none' }};">
                        <div class="subnav-header">
                            <button type="button" onclick="backToDashboard()" class="subnav-back-btn">
                                <span class="subnav-back-icon"><x-icon name="arrow-left" class="w-4 h-4" /></span>
                                <span>Back</span>
                            </button>
                            <div class="subnav-badge" style="color:#cbd5e1;">
                                <x-icon name="shield-halved" class="w-3.5 h-3.5 text-[#cbd5e1]" />
                                <span>Security &amp; Staff</span>
                            </div>
                        </div>

                        @if(auth()->user()->hasPermission('staff') || auth()->user()->hasPermission('staff_view'))
                            <a href="{{ auth()->user()->staffUrl('staff') }}" class="glossy-nav-item {{ request()->is('*/staff*') ? 'active' : '' }}">
                                <span class="icon"><x-icon name="chalkboard-user" /></span>
                                <span>Faculty &amp; Staff Directory</span>
                            </a>
                        @endif
                        @if(auth()->user()->hasPermission('security'))
                            <a href="{{ auth()->user()->staffUrl('password-resets') }}" class="glossy-nav-item {{ request()->is('*/password-resets*') ? 'active' : '' }}">
                                <span class="icon"><x-icon name="key" /></span>
                                <span>Password Resets</span>
                            </a>
                        @endif
                    </div>

                    {{-- Submenu: Settings (Personal) --}}
                    <div id="subnav-settings" class="sidebar-subnav-panel space-y-1" style="display: {{ $roleSubGroup === 'settings' ? 'block' : 'none' }};">
                        <div class="subnav-header">
                            <button type="button" onclick="backToDashboard()" class="subnav-back-btn">
                                <span class="subnav-back-icon"><x-icon name="arrow-left" class="w-4 h-4" /></span>
                                <span>Back</span>
                            </button>
                            <div class="subnav-badge" style="color:#cbd5e1;">
                                <x-icon name="user-gear" class="w-3.5 h-3.5 text-[#cbd5e1]" />
                                <span>My Settings</span>
                            </div>
                        </div>

                        <a href="{{ route('profile.edit') }}" class="glossy-nav-item {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                            <span class="icon"><x-icon name="user-gear" /></span>
                            <span>Personal Settings</span>
                        </a>
                    </div>

                <!-- STUDENT PORTAL -->
                @elseif(auth()->user()->role === 'student')
                    {{-- Student Main Menu --}}
                    <div id="role-main-nav" class="space-y-1.5" style="display: {{ $roleSubGroup ? 'none' : 'block' }};">
                        <a href="{{ route('student.dashboard') }}" class="glossy-nav-item {{ request()->routeIs('student.dashboard') ? 'active' : '' }}" style="margin-bottom: 8px;">
                            <span class="icon"><x-icon name="chart-pie" /></span>
                            <span>Dashboard</span>
                        </a>

                        <div style="padding: 4px 6px 2px; display: flex; align-items: center; justify-content: space-between;">
                            <span style="font-size: 10px; font-weight: 800; color: #8A877E; text-transform: uppercase; letter-spacing: 0.8px;">
                                STUDENT WORKSPACE HUBS
                            </span>
                        </div>

                        <!-- 1. Academics & Schedule Hub -->
                        <button type="button" onclick="showRoleSubNav('academics')" class="sidebar-hub-card-btn {{ $roleSubGroup === 'academics' ? 'active' : '' }}">
                            <div class="hub-btn-left">
                                <span class="hub-btn-icon" style="background:rgba(168,85,247,0.15);color:#c084fc;"><x-icon name="calendar-days" /></span>
                                <div class="hub-btn-text">
                                    <span class="hub-btn-title">Academics &amp; Schedule</span>
                                    <span class="hub-btn-desc">Courses, Timetable, Records</span>
                                </div>
                            </div>
                            <span class="hub-btn-arrow"><x-icon name="chevron-right" class="w-3.5 h-3.5" /></span>
                        </button>

                        <!-- 2. Exams & Gradebook Hub -->
                        <button type="button" onclick="showRoleSubNav('exams')" class="sidebar-hub-card-btn {{ $roleSubGroup === 'exams' ? 'active' : '' }}">
                            <div class="hub-btn-left">
                                <span class="hub-btn-icon" style="background:rgba(20,184,166,0.15);color:#2dd4bf;"><x-icon name="square-poll-vertical" /></span>
                                <div class="hub-btn-text">
                                    <span class="hub-btn-title">Exams &amp; Reports</span>
                                    <span class="hub-btn-desc">Datesheets, Gradebook, LMS</span>
                                </div>
                            </div>
                            <span class="hub-btn-arrow"><x-icon name="chevron-right" class="w-3.5 h-3.5" /></span>
                        </button>

                        <!-- 3. AI Study Desk Hub -->
                        @if(Route::has('lms.chatbot.index') || Route::has('lms.practice-test.index'))
                            <button type="button" onclick="showRoleSubNav('ai')" class="sidebar-hub-card-btn {{ $roleSubGroup === 'ai' ? 'active' : '' }}">
                                <div class="hub-btn-left">
                                    <span class="hub-btn-icon" style="background:rgba(236,72,153,0.15);color:#f472b6;"><x-icon name="brain" /></span>
                                    <div class="hub-btn-text">
                                        <span class="hub-btn-title">AI Study Desk</span>
                                        <span class="hub-btn-desc">AI Tutor, Practice Tests</span>
                                    </div>
                                </div>
                                <span class="hub-btn-arrow"><x-icon name="chevron-right" class="w-3.5 h-3.5" /></span>
                            </button>
                        @endif

                        <!-- 4. Fees & Billing Hub -->
                        <button type="button" onclick="showRoleSubNav('finance')" class="sidebar-hub-card-btn {{ $roleSubGroup === 'finance' ? 'active' : '' }}">
                            <div class="hub-btn-left">
                                <span class="hub-btn-icon" style="background:rgba(34,197,94,0.15);color:#4ade80;"><x-icon name="file-invoice-dollar" /></span>
                                <div class="hub-btn-text">
                                    <span class="hub-btn-title">Fee Billing &amp; Dues</span>
                                    <span class="hub-btn-desc">Invoices, Vouchers, History</span>
                                </div>
                            </div>
                            <span class="hub-btn-arrow"><x-icon name="chevron-right" class="w-3.5 h-3.5" /></span>
                        </button>

                        <!-- 5. Settings Hub -->
                        <button type="button" onclick="showRoleSubNav('settings')" class="sidebar-hub-card-btn {{ $roleSubGroup === 'settings' ? 'active' : '' }}">
                            <div class="hub-btn-left">
                                <span class="hub-btn-icon" style="background:rgba(148,163,184,0.15);color:#cbd5e1;"><x-icon name="user-gear" /></span>
                                <div class="hub-btn-text">
                                    <span class="hub-btn-title">Settings</span>
                                    <span class="hub-btn-desc">Profile, Security, Password</span>
                                </div>
                            </div>
                            <span class="hub-btn-arrow"><x-icon name="chevron-right" class="w-3.5 h-3.5" /></span>
                        </button>
                    </div>

                    {{-- Submenu: Academics --}}
                    <div id="subnav-academics" class="sidebar-subnav-panel space-y-1" style="display: {{ $roleSubGroup === 'academics' ? 'block' : 'none' }};">
                        <div class="subnav-header">
                            <button type="button" onclick="backToDashboard()" class="subnav-back-btn">
                                <span class="subnav-back-icon"><x-icon name="arrow-left" class="w-4 h-4" /></span>
                                <span>Back</span>
                            </button>
                            <div class="subnav-badge" style="color:#c084fc;">
                                <x-icon name="calendar-days" class="w-3.5 h-3.5 text-[#c084fc]" />
                                <span>Academics &amp; Schedule</span>
                            </div>
                        </div>

                        <a href="{{ route('student.courses') }}" class="glossy-nav-item {{ request()->routeIs('student.courses') ? 'active' : '' }}">
                            <span class="icon"><x-icon name="book-bookmark" /></span>
                            <span>Course Catalog</span>
                        </a>
                        <a href="{{ route('student.timetable') }}" class="glossy-nav-item {{ request()->routeIs('student.timetable') || request()->routeIs('student.schedule') ? 'active' : '' }}">
                            <span class="icon"><x-icon name="calendar-days" /></span>
                            <span>Class Timetable</span>
                        </a>
                        <a href="{{ route('student.attendance') }}" class="glossy-nav-item {{ request()->routeIs('student.attendance') ? 'active' : '' }}">
                            <span class="icon"><x-icon name="clipboard-check" /></span>
                            <span>Attendance Records</span>
                        </a>
                    </div>

                    {{-- Submenu: Exams --}}
                    <div id="subnav-exams" class="sidebar-subnav-panel space-y-1" style="display: {{ $roleSubGroup === 'exams' ? 'block' : 'none' }};">
                        <div class="subnav-header">
                            <button type="button" onclick="backToDashboard()" class="subnav-back-btn">
                                <span class="subnav-back-icon"><x-icon name="arrow-left" class="w-4 h-4" /></span>
                                <span>Back</span>
                            </button>
                            <div class="subnav-badge" style="color:#2dd4bf;">
                                <x-icon name="square-poll-vertical" class="w-3.5 h-3.5 text-[#2dd4bf]" />
                                <span>Exams &amp; Reports</span>
                            </div>
                        </div>

                        <a href="{{ route('student.mocks.index') }}" class="glossy-nav-item {{ request()->routeIs('student.mocks.*') ? 'active' : '' }}">
                            <span class="icon" style="font-size: 14px;">🎯</span>
                            <span>Mocks</span>
                        </a>
                        <a href="{{ route('student.lms') }}" class="glossy-nav-item {{ request()->routeIs('student.lms') ? 'active' : '' }}">
                            <span class="icon"><x-icon name="file-pen" /></span>
                            <span>Assignments &amp; Tests</span>
                        </a>
                        <a href="{{ route('student.datesheet') }}" class="glossy-nav-item {{ request()->routeIs('student.datesheet') ? 'active' : '' }}">
                            <span class="icon"><x-icon name="calendar-week" /></span>
                            <span>Exam Datesheets</span>
                        </a>
                        <a href="{{ route('student.examReport') }}" class="glossy-nav-item {{ request()->routeIs('student.examReport') ? 'active' : '' }}">
                            <span class="icon"><x-icon name="square-poll-vertical" /></span>
                            <span>Academic Gradebook</span>
                        </a>
                    </div>

                    {{-- Submenu: AI --}}
                    <div id="subnav-ai" class="sidebar-subnav-panel space-y-1" style="display: {{ $roleSubGroup === 'ai' ? 'block' : 'none' }};">
                        <div class="subnav-header">
                            <button type="button" onclick="backToDashboard()" class="subnav-back-btn">
                                <span class="subnav-back-icon"><x-icon name="arrow-left" class="w-4 h-4" /></span>
                                <span>Back</span>
                            </button>
                            <div class="subnav-badge" style="color:#f472b6;">
                                <x-icon name="brain" class="w-3.5 h-3.5 text-[#f472b6]" />
                                <span>AI Study Desk</span>
                            </div>
                        </div>

                        @if(Route::has('lms.chatbot.index'))
                            <a href="{{ route('lms.chatbot.index') }}" class="glossy-nav-item {{ request()->routeIs('lms.chatbot.*') ? 'active' : '' }}">
                                <span class="icon"><x-icon name="robot" /></span>
                                <span>AI Study Assistant</span>
                            </a>
                        @endif
                        @if(Route::has('lms.practice-test.index'))
                            <a href="{{ route('lms.practice-test.index') }}" class="glossy-nav-item {{ request()->routeIs('lms.practice-test.*') ? 'active' : '' }}">
                                <span class="icon"><x-icon name="bolt" /></span>
                                <span>AI Practice Tests</span>
                            </a>
                        @endif
                    </div>

                    {{-- Submenu: Finance --}}
                    <div id="subnav-finance" class="sidebar-subnav-panel space-y-1" style="display: {{ $roleSubGroup === 'finance' ? 'block' : 'none' }};">
                        <div class="subnav-header">
                            <button type="button" onclick="backToDashboard()" class="subnav-back-btn">
                                <span class="subnav-back-icon"><x-icon name="arrow-left" class="w-4 h-4" /></span>
                                <span>Back</span>
                            </button>
                            <div class="subnav-badge" style="color:#4ade80;">
                                <x-icon name="file-invoice-dollar" class="w-3.5 h-3.5 text-[#4ade80]" />
                                <span>Fee Billing &amp; Dues</span>
                            </div>
                        </div>

                        <a href="{{ route('student.invoices') }}" class="glossy-nav-item {{ request()->routeIs('student.invoices') || request()->routeIs('student.fees') || request()->routeIs('student.profile') ? 'active' : '' }}">
                            <span class="icon"><x-icon name="file-invoice-dollar" /></span>
                            <span>Fee Invoices &amp; Dues</span>
                        </a>
                    </div>

                    {{-- Submenu: Settings (Personal) --}}
                    <div id="subnav-settings" class="sidebar-subnav-panel space-y-1" style="display: {{ $roleSubGroup === 'settings' ? 'block' : 'none' }};">
                        <div class="subnav-header">
                            <button type="button" onclick="backToDashboard()" class="subnav-back-btn">
                                <span class="subnav-back-icon"><x-icon name="arrow-left" class="w-4 h-4" /></span>
                                <span>Back</span>
                            </button>
                            <div class="subnav-badge" style="color:#cbd5e1;">
                                <x-icon name="user-gear" class="w-3.5 h-3.5 text-[#cbd5e1]" />
                                <span>My Settings</span>
                            </div>
                        </div>

                        <a href="{{ route('profile.edit') }}" class="glossy-nav-item {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                            <span class="icon"><x-icon name="user-gear" /></span>
                            <span>Personal Settings</span>
                        </a>
                    </div>

                @elseif(auth()->user()->isPrincipal())
                    {{-- Principal Navigation (if rendered via layouts.navigation) --}}
                    <div id="role-main-nav" class="space-y-1.5" style="display: {{ $roleSubGroup ? 'none' : 'block' }};">
                        <a href="{{ route('principal.dashboard') }}" class="glossy-nav-item {{ request()->routeIs('principal.dashboard') ? 'active' : '' }}" style="margin-bottom: 8px;">
                            <span class="icon"><x-icon name="chart-pie" /></span>
                            <span>Dashboard</span>
                        </a>

                        <div style="padding: 4px 6px 2px; display: flex; align-items: center; justify-content: space-between;">
                            <span style="font-size: 10px; font-weight: 800; color: #8A877E; text-transform: uppercase; letter-spacing: 0.8px;">
                                CAMPUS HUBS &amp; OPERATIONS
                            </span>
                        </div>

                        <button type="button" onclick="showRoleSubNav('students')" class="sidebar-hub-card-btn {{ $roleSubGroup === 'students' ? 'active' : '' }}">
                            <div class="hub-btn-left">
                                <span class="hub-btn-icon" style="background:rgba(59,130,246,0.15);color:#60a5fa;"><x-icon name="users" /></span>
                                <div class="hub-btn-text">
                                    <span class="hub-btn-title">Student Operations</span>
                                    <span class="hub-btn-desc">Directory, Attendance, Rosters</span>
                                </div>
                            </div>
                            <span class="hub-btn-arrow"><x-icon name="chevron-right" class="w-3.5 h-3.5" /></span>
                        </button>

                        <button type="button" onclick="showRoleSubNav('staff')" class="sidebar-hub-card-btn {{ $roleSubGroup === 'staff' ? 'active' : '' }}">
                            <div class="hub-btn-left">
                                <span class="hub-btn-icon" style="background:rgba(234,179,8,0.15);color:#eab308;"><x-icon name="chalkboard-user" /></span>
                                <div class="hub-btn-text">
                                    <span class="hub-btn-title">Faculty &amp; Staff</span>
                                    <span class="hub-btn-desc">Teachers, Rooms, Authorities</span>
                                </div>
                            </div>
                            <span class="hub-btn-arrow"><x-icon name="chevron-right" class="w-3.5 h-3.5" /></span>
                        </button>

                        <button type="button" onclick="showRoleSubNav('finance')" class="sidebar-hub-card-btn {{ $roleSubGroup === 'finance' ? 'active' : '' }}">
                            <div class="hub-btn-left">
                                <span class="hub-btn-icon" style="background:rgba(34,197,94,0.15);color:#4ade80;"><x-icon name="receipt" /></span>
                                <div class="hub-btn-text">
                                    <span class="hub-btn-title">Financial Operations</span>
                                    <span class="hub-btn-desc">Billing, Invoices, Ledger</span>
                                </div>
                            </div>
                            <span class="hub-btn-arrow"><x-icon name="chevron-right" class="w-3.5 h-3.5" /></span>
                        </button>

                        <button type="button" onclick="showRoleSubNav('academic')" class="sidebar-hub-card-btn {{ $roleSubGroup === 'academic' ? 'active' : '' }}">
                            <div class="hub-btn-left">
                                <span class="hub-btn-icon" style="background:rgba(168,85,247,0.15);color:#c084fc;"><x-icon name="calendar-days" /></span>
                                <div class="hub-btn-text">
                                    <span class="hub-btn-title">Academic &amp; Timetable</span>
                                    <span class="hub-btn-desc">Terms, Classes, Schedule</span>
                                </div>
                            </div>
                            <span class="hub-btn-arrow"><x-icon name="chevron-right" class="w-3.5 h-3.5" /></span>
                        </button>

                        <button type="button" onclick="showRoleSubNav('ai')" class="sidebar-hub-card-btn {{ $roleSubGroup === 'ai' ? 'active' : '' }}">
                            <div class="hub-btn-left">
                                <span class="hub-btn-icon" style="background:rgba(236,72,153,0.15);color:#f472b6;"><x-icon name="brain" /></span>
                                <div class="hub-btn-text">
                                    <span class="hub-btn-title">AI &amp; Learning Suite</span>
                                    <span class="hub-btn-desc">AI Assistant, Notes, Study</span>
                                </div>
                            </div>
                            <span class="hub-btn-arrow"><x-icon name="chevron-right" class="w-3.5 h-3.5" /></span>
                        </button>

                        <button type="button" onclick="showRoleSubNav('exams')" class="sidebar-hub-card-btn {{ $roleSubGroup === 'exams' ? 'active' : '' }}">
                            <div class="hub-btn-left">
                                <span class="hub-btn-icon" style="background:rgba(20,184,166,0.15);color:#2dd4bf;"><x-icon name="file-signature" /></span>
                                <div class="hub-btn-text">
                                    <span class="hub-btn-title">Assessments &amp; Grading</span>
                                    <span class="hub-btn-desc">Exams, Reports, Criteria</span>
                                </div>
                            </div>
                            <span class="hub-btn-arrow"><x-icon name="chevron-right" class="w-3.5 h-3.5" /></span>
                        </button>

                        <button type="button" onclick="showRoleSubNav('settings')" class="sidebar-hub-card-btn {{ $roleSubGroup === 'settings' ? 'active' : '' }}">
                            <div class="hub-btn-left">
                                <span class="hub-btn-icon" style="background:rgba(148,163,184,0.15);color:#cbd5e1;"><x-icon name="sliders" /></span>
                                <div class="hub-btn-text">
                                    <span class="hub-btn-title">Administration</span>
                                    <span class="hub-btn-desc">Settings, Security, Quotas</span>
                                </div>
                            </div>
                            <span class="hub-btn-arrow"><x-icon name="chevron-right" class="w-3.5 h-3.5" /></span>
                        </button>
                    </div>

                    {{-- Principal Submenus --}}
                    <div id="subnav-students" class="sidebar-subnav-panel space-y-1" style="display: {{ $roleSubGroup === 'students' ? 'block' : 'none' }};">
                        <div class="subnav-header">
                            <button type="button" onclick="backToDashboard()" class="subnav-back-btn">
                                <span class="subnav-back-icon"><x-icon name="arrow-left" class="w-4 h-4" /></span>
                                <span>Back</span>
                            </button>
                            <div class="subnav-badge" style="color:#60a5fa;">
                                <x-icon name="users" class="w-3.5 h-3.5 text-[#60a5fa]" />
                                <span>Student Operations</span>
                            </div>
                        </div>
                        <a href="{{ route('principal.students.index') }}" class="glossy-nav-item {{ (request()->routeIs('principal.students.*') || request()->is('principal/students*')) ? 'active' : '' }}">
                            <span class="icon"><x-icon name="users" /></span>
                            <span>Student Directory</span>
                        </a>
                        <a href="{{ route('principal.directory.index') }}" class="glossy-nav-item {{ (request()->routeIs('principal.directory.*') || request()->is('principal/directory*')) ? 'active' : '' }}">
                            <span class="icon"><x-icon name="building-columns" /></span>
                            <span>Search Profile</span>
                        </a>
                        <a href="{{ route('principal.attendance') }}" class="glossy-nav-item {{ (request()->routeIs('principal.attendance*') || request()->is('principal/attendance*')) ? 'active' : '' }}">
                            <span class="icon"><x-icon name="clipboard-user" /></span>
                            <span>Daily Student Attendance</span>
                        </a>
                        <a href="{{ route('principal.scholarships.index') }}" class="glossy-nav-item {{ (request()->routeIs('principal.scholarships.*') || request()->is('principal/scholarships*')) ? 'active' : '' }}">
                            <span class="icon"><x-icon name="award" /></span>
                            <span>Scholarship Programs</span>
                        </a>
                    </div>

                    <div id="subnav-staff" class="sidebar-subnav-panel space-y-1" style="display: {{ $roleSubGroup === 'staff' ? 'block' : 'none' }};">
                        <div class="subnav-header">
                            <button type="button" onclick="backToDashboard()" class="subnav-back-btn">
                                <span class="subnav-back-icon"><x-icon name="arrow-left" class="w-4 h-4" /></span>
                                <span>Back</span>
                            </button>
                            <div class="subnav-badge" style="color:#eab308;">
                                <x-icon name="chalkboard-user" class="w-3.5 h-3.5 text-[#eab308]" />
                                <span>Faculty &amp; Staff</span>
                            </div>
                        </div>
                        <a href="{{ route('principal.staff.index') }}" class="glossy-nav-item {{ (request()->routeIs('principal.staff.*') || request()->is('principal/staff*')) ? 'active' : '' }}">
                            <span class="icon"><x-icon name="chalkboard-user" /></span>
                            <span>Faculty &amp; Staff Directory</span>
                        </a>
                        <a href="{{ route('principal.teachers.availability.index') }}" class="glossy-nav-item {{ (request()->routeIs('principal.teachers.availability.*') || request()->is('principal/teachers/availability*')) ? 'active' : '' }}">
                            <span class="icon"><x-icon name="calendar-check" /></span>
                            <span>Teacher Availabilities</span>
                        </a>
                        <a href="{{ route('principal.staff.authorities') }}" class="glossy-nav-item {{ request()->routeIs('principal.staff.authorities') ? 'active' : '' }}">
                            <span class="icon"><x-icon name="shield-halved" /></span>
                            <span>Assigned Authorities</span>
                        </a>
                        <a href="{{ route('principal.rooms.index') }}" class="glossy-nav-item {{ (request()->routeIs('principal.rooms.*') || request()->is('principal/rooms*')) ? 'active' : '' }}">
                            <span class="icon"><x-icon name="door-open" /></span>
                            <span>Campus Rooms</span>
                        </a>
                        <a href="{{ route('principal.password-resets.index') }}" class="glossy-nav-item {{ (request()->routeIs('*.password-resets.*') || request()->is('*/password-resets*')) ? 'active' : '' }}">
                            <span class="icon"><x-icon name="key" /></span>
                            <span>Staff Password Resets</span>
                        </a>
                    </div>

                    <div id="subnav-finance" class="sidebar-subnav-panel space-y-1" style="display: {{ $roleSubGroup === 'finance' ? 'block' : 'none' }};">
                        <div class="subnav-header">
                            <button type="button" onclick="backToDashboard()" class="subnav-back-btn">
                                <span class="subnav-back-icon"><x-icon name="arrow-left" class="w-4 h-4" /></span>
                                <span>Back</span>
                            </button>
                            <div class="subnav-badge" style="color:#4ade80;">
                                <x-icon name="receipt" class="w-3.5 h-3.5 text-[#4ade80]" />
                                <span>Financial Operations</span>
                            </div>
                        </div>
                        <a href="{{ route('principal.settings.index', ['tab' => 'financial']) }}" class="glossy-nav-item {{ (request()->routeIs('principal.settings.*') && request('tab') === 'financial') ? 'active' : '' }}">
                            <span class="icon"><x-icon name="receipt" /></span>
                            <span>Financial Ledger &amp; Expenses</span>
                        </a>
                        <a href="{{ route('principal.settings.index', ['tab' => 'bank']) }}" class="glossy-nav-item {{ (request()->routeIs('principal.settings.*') && request('tab') === 'bank') ? 'active' : '' }}">
                            <span class="icon"><x-icon name="file-invoice-dollar" /></span>
                            <span>Fee Invoices &amp; Billing</span>
                        </a>
                    </div>

                    <div id="subnav-academic" class="sidebar-subnav-panel space-y-1" style="display: {{ $roleSubGroup === 'academic' ? 'block' : 'none' }};">
                        <div class="subnav-header">
                            <button type="button" onclick="backToDashboard()" class="subnav-back-btn">
                                <span class="subnav-back-icon"><x-icon name="arrow-left" class="w-4 h-4" /></span>
                                <span>Back</span>
                            </button>
                            <div class="subnav-badge" style="color:#c084fc;">
                                <x-icon name="calendar-days" class="w-3.5 h-3.5 text-[#c084fc]" />
                                <span>Academic &amp; Timetable</span>
                            </div>
                        </div>
                        <a href="{{ route('principal.academic-terms.index') }}" class="glossy-nav-item {{ (request()->routeIs('principal.academic-terms.*') || request()->is('principal/academic-terms*')) ? 'active' : '' }}">
                            <span class="icon"><x-icon name="calendar-check" /></span>
                            <span>Academic Terms &amp; Years</span>
                        </a>
                        <a href="{{ route('principal.classes-subjects.index') }}" class="glossy-nav-item {{ (request()->routeIs('principal.classes-subjects.*') || request()->is('principal/classes-subjects*')) ? 'active' : '' }}">
                            <span class="icon"><x-icon name="layer-group" /></span>
                            <span>Classes &amp; Subjects</span>
                        </a>
                        <a href="{{ route('principal.timetables.index') }}" class="glossy-nav-item {{ (request()->routeIs('principal.timetables.*') || request()->is('principal/timetables*')) ? 'active' : '' }}">
                            <span class="icon"><x-icon name="calendar-days" /></span>
                            <span>Timetable Matrix</span>
                        </a>
                    </div>

                    <div id="subnav-ai" class="sidebar-subnav-panel space-y-1" style="display: {{ $roleSubGroup === 'ai' ? 'block' : 'none' }};">
                        <div class="subnav-header">
                            <button type="button" onclick="backToDashboard()" class="subnav-back-btn">
                                <span class="subnav-back-icon"><x-icon name="arrow-left" class="w-4 h-4" /></span>
                                <span>Back</span>
                            </button>
                            <div class="subnav-badge" style="color:#f472b6;">
                                <x-icon name="brain" class="w-3.5 h-3.5 text-[#f472b6]" />
                                <span>AI &amp; Learning Suite</span>
                            </div>
                        </div>
                        <a href="{{ route('lms.subjects.list') }}" class="glossy-nav-item {{ (request()->routeIs('lms.subjects.*') || request()->routeIs('lms.materials.*') || request()->is('lms/subjects*') || request()->is('lms/materials*')) ? 'active' : '' }}">
                            <span class="icon"><x-icon name="book-bookmark" /></span>
                            <span>Course Materials &amp; Notes</span>
                        </a>
                        @if(Route::has('lms.chatbot.index'))
                            <a href="{{ route('lms.chatbot.index') }}" class="glossy-nav-item {{ request()->routeIs('lms.chatbot.*') ? 'active' : '' }}">
                                <span class="icon"><x-icon name="robot" /></span>
                                <span>AI Study Assistant</span>
                            </a>
                        @endif
                        @if(Route::has('lms.practice-test.index'))
                            <a href="{{ route('lms.practice-test.index') }}" class="glossy-nav-item {{ request()->routeIs('lms.practice-test.*') ? 'active' : '' }}">
                                <span class="icon"><x-icon name="bolt" /></span>
                                <span>AI Practice Tests</span>
                            </a>
                        @endif
                    </div>

                    <div id="subnav-exams" class="sidebar-subnav-panel space-y-1" style="display: {{ $roleSubGroup === 'exams' ? 'block' : 'none' }};">
                        <div class="subnav-header">
                            <button type="button" onclick="backToDashboard()" class="subnav-back-btn">
                                <span class="subnav-back-icon"><x-icon name="arrow-left" class="w-4 h-4" /></span>
                                <span>Back</span>
                            </button>
                            <div class="subnav-badge" style="color:#2dd4bf;">
                                <x-icon name="file-signature" class="w-3.5 h-3.5 text-[#2dd4bf]" />
                                <span>Assessments &amp; Grading</span>
                            </div>
                        </div>
                        <a href="{{ route('lms.assessments.index') }}" class="glossy-nav-item {{ (request()->routeIs('lms.assessments.*') || request()->is('lms/assessments*')) ? 'active' : '' }}">
                            <span class="icon"><x-icon name="file-pen" /></span>
                            <span>Assessment Creator</span>
                        </a>
                        <a href="{{ route('lms.mocks.create') }}" class="glossy-nav-item {{ (request()->routeIs('lms.mocks.create') || request()->routeIs('teacher.mocks.create')) ? 'active' : '' }}">
                            <span class="icon"><x-icon name="bullseye" /></span>
                            <span>🎯 Generate Mocks</span>
                        </a>
                        <a href="{{ route('lms.mocks.index') }}" class="glossy-nav-item {{ (request()->routeIs('lms.mocks.index') || request()->routeIs('teacher.mocks.index') || request()->routeIs('lms.mocks.show') || request()->routeIs('teacher.mocks.show')) ? 'active' : '' }}">
                            <span class="icon"><x-icon name="list-check" /></span>
                            <span>📋 View Mocks</span>
                        </a>
                        <a href="{{ route('lms.test-results.index') }}" class="glossy-nav-item {{ (request()->routeIs('lms.test-results.*') || request()->is('lms/test-results*')) ? 'active' : '' }}">
                            <span class="icon"><x-icon name="square-poll-vertical" /></span>
                            <span>Test Results &amp; Grading</span>
                        </a>
                        <a href="{{ route('lms.datesheet.index') }}" class="glossy-nav-item {{ (request()->routeIs('lms.datesheet.*') || request()->is('lms/datesheet*')) ? 'active' : '' }}">
                            <span class="icon"><x-icon name="calendar-week" /></span>
                            <span>Exam Datesheets</span>
                        </a>
                        <a href="{{ route('lms.exam-report.index') }}" class="glossy-nav-item {{ (request()->routeIs('lms.exam-report.*') || request()->is('lms/exam-report*')) ? 'active' : '' }}">
                            <span class="icon"><x-icon name="file-lines" /></span>
                            <span>Exam Performance Reports</span>
                        </a>
                        <a href="{{ route('lms.grades.weightages.page') }}" class="glossy-nav-item {{ (request()->routeIs('lms.grades.*') || request()->is('lms/grades*')) ? 'active' : '' }}">
                            <span class="icon"><x-icon name="scale-balanced" /></span>
                            <span>Grading Weights &amp; Criteria</span>
                        </a>
                    </div>

                    <div id="subnav-settings" class="sidebar-subnav-panel space-y-1" style="display: {{ $roleSubGroup === 'settings' ? 'block' : 'none' }};">
                        <div class="subnav-header">
                            <button type="button" onclick="backToDashboard()" class="subnav-back-btn">
                                <span class="subnav-back-icon"><x-icon name="arrow-left" class="w-4 h-4" /></span>
                                <span>Back</span>
                            </button>
                            <div class="subnav-badge" style="color:#cbd5e1;">
                                <x-icon name="sliders" class="w-3.5 h-3.5 text-[#cbd5e1]" />
                                <span>Administration Settings</span>
                            </div>
                        </div>
                        <a href="{{ route('principal.settings.index') }}" class="glossy-nav-item {{ (request()->routeIs('principal.settings.*') && !request('tab')) ? 'active' : '' }}">
                            <span class="icon"><x-icon name="gear" /></span>
                            <span>Institute Settings</span>
                        </a>
                        <a href="{{ route('profile.edit') }}" class="glossy-nav-item {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                            <span class="icon"><x-icon name="user-gear" /></span>
                            <span>Personal Settings</span>
                        </a>
                    </div>

                @else
                    <div>
                        <a href="{{ route('dashboard') }}" class="glossy-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <span class="icon"><x-icon name="chart-pie" /></span>
                            <span>Dashboard</span>
                        </a>
                    </div>
                @endif
            @endauth
        </nav>
    </div>

    <div class="p-3 border-t border-[#2A2C30] bg-[#121316]">
        <div class="flex flex-col gap-1.5">
            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl text-[11px] font-bold text-[#E05252] hover:text-[#FF7B7B] border border-[#351C1C] bg-[#191212] hover:bg-[#2A1717] transition cursor-pointer">
                    <x-icon name="arrow-right-from-bracket" class="w-3.5 h-3.5 text-[#E05252]" />
                    <span>Sign Out</span>
                </button>
            </form>
        </div>
    </div>
</aside>
