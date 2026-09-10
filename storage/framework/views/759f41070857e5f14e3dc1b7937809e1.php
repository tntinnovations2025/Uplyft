

<?php $__env->startSection('title', 'AI Study Assistant'); ?>
<?php $__env->startSection('breadcrumb', 'AI Study Assistant'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $currentSubject = $selectedSubjectId ? $subjects->firstWhere('id', $selectedSubjectId) : null;
?>

<div id="chatbot-wrapper" style="width:100%;max-width:1400px;margin:0 auto;display:flex;flex-direction:column;gap:20px">

    
    <div id="subject-selection-screen" style="<?php echo e($selectedSubjectId ? 'display:none;' : 'display:flex;'); ?>flex-direction:column;align-items:center;justify-content:center;padding:48px 32px;background:var(--surface);border:1px solid var(--border);border-radius:24px;box-shadow:0 16px 40px rgba(0,0,0,0.3);margin:20px auto;max-width:760px;width:100%">
        
        
        <div style="width:80px;height:80px;border-radius:24px;background:linear-gradient(135deg,var(--accent),var(--accent-hover));display:flex;align-items:center;justify-content:center;font-size:42px;margin-bottom:20px;box-shadow:0 8px 28px rgba(212,138,46,0.4)">
            🤖
        </div>

        <h2 style="font-family:'Space Grotesk',sans-serif;font-size:28px;font-weight:800;color:var(--text);margin-bottom:12px;text-align:center">
            UPLYFT AI Study Assistant
        </h2>

        
        <div style="background:rgba(212,138,46,0.08);border:1px solid rgba(212,138,46,0.3);border-radius:18px;padding:18px 24px;margin-bottom:32px;max-width:580px;width:100%;text-align:center">
            <p style="font-size:15px;color:var(--warning);font-weight:600;line-height:1.6;margin:0">
                "I am here to help you achieve more .. let's team up . Please choose a subject &amp; response mode" 🤝🌟
            </p>
        </div>

        
        <?php if(!auth()->user()->isStudent() && ($isAdministration || $classes->count() > 1)): ?>
            <div style="width:100%;max-width:580px;margin-bottom:20px">
                <label style="font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:1px;color:var(--text-muted);display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
                    <span>🏫 Step 1: Select Class / Grade</span>
                    <span style="font-size:11px;font-weight:600;color:var(--accent2);text-transform:none">
                        <?php echo e($classes->count()); ?> Classes available
                    </span>
                </label>
                <select id="center-class-selector" onchange="onClassFilterChange(this.value)" style="width:100%;padding:14px 18px;background:var(--surface2);border:2px solid var(--accent);border-radius:14px;color:var(--text);font-size:15px;font-weight:600;outline:none;font-family:inherit;cursor:pointer;box-shadow:0 4px 16px rgba(0,0,0,0.15);transition:border-color 0.2s">
                    <option value="">— Select a Class —</option>
                    <?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cls): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($cls->id); ?>" <?php if($selectedClassId == $cls->id): echo 'selected'; endif; ?>>
                            <?php echo e($cls->name); ?> (<?php echo e($cls->subjects_count ?? $cls->subjects->count()); ?> Subjects)
                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div id="no-class-prompt" style="<?php echo e((!$selectedClassId) ? 'display:block;' : 'display:none;'); ?>width:100%;max-width:580px;margin-bottom:24px;padding:16px 20px;background:rgba(212,138,46,0.06);border:1px dashed rgba(212,138,46,0.35);border-radius:16px;text-align:center">
                <div style="font-size:24px;margin-bottom:6px">👆</div>
                <div style="font-size:13px;font-weight:700;color:var(--text);margin-bottom:2px">Please select a class first</div>
                <div style="font-size:11px;color:var(--text-muted)">Choose your class from the dropdown above to view and access its subjects.</div>
            </div>
        <?php elseif(auth()->user()->isStudent()): ?>
            <?php if($classes->isNotEmpty()): ?>
                
                <div style="width:100%;max-width:580px;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;background:rgba(212,138,46,0.06);border:1px solid rgba(212,138,46,0.25);border-radius:14px;padding:12px 18px">
                    <div style="display:flex;align-items:center;gap:10px">
                        <span style="font-size:22px">🎓</span>
                        <div>
                            <div style="font-size:10.5px;font-weight:800;text-transform:uppercase;letter-spacing:0.8px;color:var(--text-muted)">Enrolled Class</div>
                            <div style="font-size:14px;font-weight:700;color:var(--warning)"><?php echo e($classes->pluck('name')->implode(', ')); ?></div>
                        </div>
                    </div>
                    <span class="badge badge-purple" style="font-size:11px;font-weight:700">
                        <?php echo e($subjects->count()); ?> Assigned <?php echo e(Str::plural('Subject', $subjects->count())); ?>

                    </span>
                </div>
            <?php endif; ?>
        <?php elseif($classes->isNotEmpty()): ?>
            
            <div style="width:100%;max-width:580px;margin-bottom:20px;display:flex;align-items:center;gap:12px;background:rgba(212,138,46,0.08);border:1px solid rgba(212,138,46,0.25);border-radius:14px;padding:12px 18px">
                <span style="font-size:22px">🎓</span>
                <div>
                    <div style="font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:1px;color:var(--text-muted)">Your Assigned Class</div>
                    <div style="font-size:14px;font-weight:700;color:var(--warning)"><?php echo e($classes->pluck('name')->implode(', ')); ?></div>
                </div>
            </div>
        <?php endif; ?>

        
        <?php if(auth()->user()->isStudent() && $subjects->isEmpty()): ?>
            <div style="width:100%;max-width:580px;margin-bottom:24px;padding:24px 20px;background:rgba(239,68,68,0.08);border:1px dashed rgba(239,68,68,0.3);border-radius:16px;text-align:center">
                <div style="font-size:28px;margin-bottom:8px">🎓</div>
                <div style="font-size:15px;font-weight:800;color:var(--danger);margin-bottom:4px">You are not currently enrolled in any class or subjects</div>
                <div style="font-size:12px;color:var(--text-muted);line-height:1.5">Please contact your campus administration to assign your class section and course enrollments.</div>
            </div>
        <?php endif; ?>

        
        <div id="step2-subject-section" style="<?php echo e((!auth()->user()->isStudent() && ($isAdministration || $classes->count() > 1) && !$selectedClassId) || (auth()->user()->isStudent() && $subjects->isEmpty()) ? 'display:none;' : 'display:block;'); ?>width:100%;max-width:580px;margin-bottom:28px">
            <label style="font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:1px;color:var(--text-muted);display:block;margin-bottom:10px">
                📚 <?php echo e((!auth()->user()->isStudent() && ($isAdministration || $classes->count() > 1)) ? 'Step 2: Choose Subject' : 'Step 1: Choose Your Enrolled Subject'); ?>

            </label>
            <select id="center-subject-selector" onchange="onCenterSubjectChange(this.value)" style="width:100%;padding:14px 18px;background:var(--surface2);border:2px solid var(--accent);border-radius:14px;color:var(--text);font-size:15px;font-weight:600;outline:none;font-family:inherit;cursor:pointer;box-shadow:0 4px 16px rgba(0,0,0,0.15)">
                <option value="">— Select a Subject —</option>
            </select>

            <div id="quick-subj-container" style="display:flex;gap:8px;flex-wrap:wrap;margin-top:12px"></div>
        </div>

        
        <div style="width:100%;max-width:580px;margin-bottom:32px">
            <label style="font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:1px;color:var(--text-muted);display:block;margin-bottom:12px">
                ⚙️ <?php echo e((!auth()->user()->isStudent() && ($isAdministration || $classes->count() > 1)) ? 'Step 3: Select Preferred Answer Mode' : 'Step 2: Select Preferred Answer Mode'); ?>

            </label>
            
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px">
                
                <div class="welcome-mode-card active" id="card-mode-short" onclick="setWelcomeMode('short')">
                    <div style="font-size:24px;margin-bottom:6px">⚡</div>
                    <div style="font-size:14px;font-weight:700;color:var(--text);margin-bottom:4px">Short Answer</div>
                    <div style="font-size:11px;color:var(--text-muted);line-height:1.4">Direct &amp; to the point in a few lines</div>
                </div>

                
                <div class="welcome-mode-card" id="card-mode-long" onclick="setWelcomeMode('long')">
                    <div style="font-size:24px;margin-bottom:6px">📖</div>
                    <div style="font-size:14px;font-weight:700;color:var(--text);margin-bottom:4px">Long &amp; Detailed</div>
                    <div style="font-size:11px;color:var(--text-muted);line-height:1.4">In-depth answers from notes/books</div>
                </div>

                
                <div class="welcome-mode-card" id="card-mode-summary" onclick="setWelcomeMode('summary')">
                    <div style="font-size:24px;margin-bottom:6px">📋</div>
                    <div style="font-size:14px;font-weight:700;color:var(--text);margin-bottom:4px">Summary Option</div>
                    <div style="font-size:11px;color:var(--text-muted);line-height:1.4">Summarize topic with headings</div>
                </div>
            </div>
        </div>

        
        <button id="start-chat-btn" onclick="confirmCenterSubject()" class="btn btn-primary" style="padding:16px 48px;border-radius:16px;font-size:16px;font-weight:800;box-shadow:0 8px 24px rgba(212,138,46,0.4)" <?php echo e(!$selectedSubjectId ? 'disabled' : ''); ?>>
            Open Chatbot Interface 💬
        </button>
    </div>

    
    <div id="chat-workspace" style="<?php echo e($selectedSubjectId ? 'display:flex;' : 'display:none;'); ?>gap:20px;height:720px;max-height:calc(100vh - 140px);min-height:550px">

        
        <div style="width:280px;background:var(--surface);border:1px solid var(--border);border-radius:20px;display:flex;flex-direction:column;flex-shrink:0;box-shadow:0 8px 24px rgba(0,0,0,0.2);overflow:hidden">
            
            
            <div style="padding:16px 18px;border-bottom:1px solid var(--border);background:var(--surface2);display:flex;align-items:center;justify-content:space-between">
                <div>
                    <span style="font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:1px;color:var(--text-muted);display:block">Active Subject</span>
                    <span style="font-size:14px;font-weight:700;color:var(--accent2)" id="sidebar-subject-name">
                        <?php echo e($currentSubject ? $currentSubject->subject_name . ($currentSubject->instituteClass ? ' (' . $currentSubject->instituteClass->name . ')' : '') : 'Select Subject'); ?>

                    </span>
                </div>
                <button onclick="openSubjectPicker()" class="btn" style="background:var(--surface);border:1px solid var(--border);padding:6px 12px;border-radius:10px;font-size:11px;font-weight:700;color:var(--text);cursor:pointer" title="Switch Subject">
                    🔄 Switch
                </button>
            </div>

            
            <div style="padding:14px 16px;border-bottom:1px solid var(--border)">
                <button id="new-chat-btn" onclick="startNewChat()" class="btn btn-primary" style="width:100%;justify-content:center;padding:12px;border-radius:12px;font-weight:700;font-size:13px">
                    ✨ New Conversation
                </button>
            </div>

            
            <div style="flex:1;overflow-y:auto;padding:12px">
                <div style="display:flex;align-items:center;justify-content:space-between;padding:0 6px 10px">
                    <span style="font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:1px;color:var(--text-muted)">
                        💬 Past Chats
                    </span>
                    <span id="session-count-badge" class="badge badge-purple" style="font-size:10px">
                        <?php echo e(count($sessions)); ?>

                    </span>
                </div>

                <div id="sessions-list">
                    <?php $__currentLoopData = $sessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sess): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="session-item <?php echo e(($selectedSessionId ?? '') === $sess['session_id'] ? 'active' : ''); ?>"
                             onclick="loadSession('<?php echo e($sess['session_id']); ?>')"
                             data-session="<?php echo e($sess['session_id']); ?>">
                            <div style="font-size:13px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:var(--text)">
                                <?php echo e(Str::limit($sess['first_message'], 32)); ?>

                            </div>
                            <div style="font-size:11px;color:var(--text-muted);margin-top:4px">
                                🕒 <?php echo e(\Carbon\Carbon::parse($sess['last_active'])->diffForHumans()); ?>

                            </div>
                            <button class="delete-session-btn" onclick="event.stopPropagation(); deleteSession('<?php echo e($sess['session_id']); ?>')" title="Delete Chat">✕</button>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php if(empty($sessions)): ?>
                        <div id="no-sessions-msg" style="text-align:center;padding:40px 10px;color:var(--text-muted);font-size:13px;line-height:1.6">
                            No past chats yet.<br>Click <b>✨ New Conversation</b> to ask a question!
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        
        <div style="flex:1;display:flex;flex-direction:column;min-width:0;background:var(--surface);border:1px solid var(--border);border-radius:20px;box-shadow:0 8px 24px rgba(0,0,0,0.2);overflow:hidden">
            
            
            <div style="padding:14px 20px;border-bottom:1px solid var(--border);background:var(--surface2);display:flex;align-items:center;justify-content:space-between;flex-shrink:0;gap:12px;flex-wrap:wrap">
                
                
                <div style="display:flex;align-items:center;gap:12px">
                    <div style="width:40px;height:40px;border-radius:12px;background:linear-gradient(135deg,var(--accent),var(--accent-hover));display:flex;align-items:center;justify-content:center;font-size:20px;box-shadow:0 4px 12px rgba(212,138,46,0.3)">
                        🤖
                    </div>
                    <div>
                        <div style="font-family:'Space Grotesk',sans-serif;font-size:16px;font-weight:700;color:var(--text)" id="chat-subject-name">
                            <?php echo e($currentSubject ? $currentSubject->subject_name . ($currentSubject->instituteClass ? ' (' . $currentSubject->instituteClass->name . ')' : '') . ' — AI Tutor' : 'UPLYFT RAG AI Assistant'); ?>

                        </div>
                        <div style="font-size:11px;color:var(--text-muted);margin-top:2px" id="chat-status">
                            ⚡ RAG Active · <?php echo e($currentSubject->materials_count ?? 0); ?> indexed documents loaded
                        </div>
                    </div>
                </div>


                
                <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
                    
                    
                    <div style="display:flex;background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:3px;gap:3px">
                        <button type="button" onclick="setAnswerMode('short')" id="mode-btn-short" class="mode-pill active" title="Concise answer in a few lines">
                            ⚡ Short
                        </button>
                        <button type="button" onclick="setAnswerMode('long')" id="mode-btn-long" class="mode-pill" title="In-depth detailed answer">
                            📖 Detailed
                        </button>
                        <button type="button" onclick="setAnswerMode('summary')" id="mode-btn-summary" class="mode-pill" title="Summary with headings">
                            📋 Summary
                        </button>
                    </div>

                    
                    <button onclick="openSubjectPicker()" class="btn" style="background:var(--surface);border:1px solid var(--accent);padding:7px 14px;border-radius:10px;font-size:12px;font-weight:700;color:var(--accent2);display:flex;align-items:center;gap:6px;cursor:pointer">
                        🔄 Switch Subject
                    </button>
                </div>
            </div>

            
            <div id="chat-messages" style="flex:1;overflow-y:auto;padding:20px;display:flex;flex-direction:column;gap:16px">
                <?php if(!empty($messages)): ?>
                    <?php $__currentLoopData = $messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $msg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if($msg['role'] === 'user'): ?>
                            <div class="chat-bubble user-bubble">
                                <div class="bubble-content user-content"><?php echo e($msg['message']); ?></div>
                                <div class="bubble-meta"><?php echo e(\Carbon\Carbon::parse($msg['created_at'])->format('h:i A')); ?></div>
                            </div>
                        <?php else: ?>
                            <div class="chat-bubble ai-bubble">
                                <div style="display:flex;gap:12px;align-items:flex-start">
                                    <div class="ai-avatar">🤖</div>
                                    <div style="min-width:0;flex:1">
                                        <div class="bubble-content ai-content"><?php echo nl2br(e($msg['message'])); ?></div>
                                        <?php if(!empty($msg['sources'])): ?>
                                            <div class="source-tags">
                                                <?php $__currentLoopData = $msg['sources']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $src): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <span class="source-tag">📄 <?php echo e($src); ?></span>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </div>
                                        <?php endif; ?>
                                        <div class="bubble-meta"><?php echo e(\Carbon\Carbon::parse($msg['created_at'])->format('h:i A')); ?></div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php else: ?>
                    
                    <div id="welcome-state" style="margin:auto;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:30px;max-width:600px;width:100%">
                        <div style="font-size:48px;margin-bottom:14px">🧠</div>
                        <h3 style="font-family:'Space Grotesk',sans-serif;font-size:20px;font-weight:700;margin-bottom:8px;color:var(--text)">
                            <?php echo e($currentSubject->subject_name ?? 'Subject'); ?> AI Tutor
                        </h3>
                        <p style="color:var(--text-muted);line-height:1.5;margin-bottom:20px;font-size:13px">
                            Ask any question about your textbook topics or concepts. I am here to help you achieve more!
                        </p>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;width:100%">
                            <div class="suggestion-chip" onclick="useSuggestion('Explain key concepts from Chapter 1')">
                                📖 Key concepts from Chapter 1
                            </div>
                            <div class="suggestion-chip" onclick="useSuggestion('Solve a practice problem step by step')">
                                🧮 Practice problem breakdown
                            </div>
                            <div class="suggestion-chip" onclick="useSuggestion('Summarize the main topics covered in the syllabus')">
                                📋 Full syllabus summary
                            </div>
                            <div class="suggestion-chip" onclick="useSuggestion('Create revision notes for exam prep')">
                                🗂️ Exam revision notes
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            
            <div style="padding:16px 20px;border-top:1px solid var(--border);background:var(--surface2);flex-shrink:0">
                <form id="chat-form" onsubmit="sendChatMessage(event)" style="display:flex;gap:10px;align-items:center">
                    <div style="flex:1;position:relative">
                        <textarea id="chat-input"
                            placeholder="<?php echo e($selectedSubjectId ? 'Type your question about ' . ($currentSubject->subject_name ?? 'this subject') . '...' : 'Select a subject to start...'); ?>"
                            rows="1"
                            style="width:100%;padding:12px 16px;background:var(--surface);border:1px solid var(--border);border-radius:12px;color:var(--text);font-size:14px;outline:none;resize:none;max-height:120px;line-height:1.5;font-family:inherit"
                            onkeydown="handleKeyDown(event)"
                            oninput="autoResize(this)"></textarea>
                    </div>
                    <button type="submit" id="send-btn" class="btn btn-primary" style="height:44px;width:44px;padding:0;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/></svg>
                    </button>
                </form>
                <div style="text-align:center;margin-top:6px;font-size:10px;color:var(--text-muted)">
                    ⚡ Answers strictly generated from verified subject materials.
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Welcome Mode Selection Cards */
    .welcome-mode-card {
        padding: 16px 12px;
        background: var(--surface2);
        border: 2px solid var(--border);
        border-radius: 16px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .welcome-mode-card:hover {
        border-color: var(--accent);
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(0,0,0,0.15);
    }
    .welcome-mode-card.active {
        background: rgba(212,138,46,0.12);
        border-color: var(--accent);
        box-shadow: 0 6px 20px rgba(212,138,46,0.25);
    }

    .quick-subj-btn {
        background: var(--surface2);
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: 6px 12px;
        font-size: 12px;
        font-weight: 600;
        color: var(--text);
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .quick-subj-btn:hover {
        border-color: var(--accent);
    }
    .quick-subj-btn.active {
        background: var(--accent);
        color: var(--accent-on);
        border-color: var(--accent);
    }

    /* Inline Mode Switcher Pills */
    .mode-pill {
        padding: 5px 12px;
        border-radius: 8px;
        font-size: 11px;
        font-weight: 700;
        cursor: pointer;
        background: transparent;
        color: var(--text-muted);
        border: none;
        transition: all 0.2s ease;
    }
    .mode-pill:hover {
        color: var(--text);
    }
    .mode-pill.active {
        background: var(--accent);
        color: var(--accent-on);
        box-shadow: 0 2px 8px rgba(212,138,46,0.3);
    }

    /* Chat Session Item */
    .session-item {
        padding: 10px 12px;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.15s ease;
        margin-bottom: 6px;
        position: relative;
        border: 1px solid transparent;
        background: var(--surface);
    }
    .session-item:hover {
        background: var(--surface2);
        border-color: var(--border);
    }
    .session-item.active {
        background: linear-gradient(90deg, rgba(212,138,46,0.15), transparent);
        border-color: var(--accent);
        border-left: 4px solid var(--accent);
    }
    .delete-session-btn {
        position: absolute;
        right: 8px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: var(--text-muted);
        cursor: pointer;
        font-size: 13px;
        opacity: 0;
        transition: opacity 0.15s;
        padding: 4px;
    }
    .session-item:hover .delete-session-btn { opacity: 1; }
    .delete-session-btn:hover { color: var(--danger); }

    /* Chat Bubbles */
    .chat-bubble { max-width: 85%; animation: fadeInUp 0.25s ease; }
    .user-bubble { align-self: flex-end; }
    .ai-bubble { align-self: flex-start; max-width: 90%; }

    .bubble-content {
        padding: 12px 16px;
        border-radius: 16px;
        font-size: 14px;
        line-height: 1.6;
        word-wrap: break-word;
    }
    .user-content {
        background: linear-gradient(135deg, var(--accent), var(--accent-hover));
        color: var(--accent-on);
        border-radius: 16px 16px 4px 16px;
        box-shadow: 0 4px 12px rgba(212,138,46,0.25);
    }
    .ai-content {
        background: var(--surface2);
        border: 1px solid var(--border);
        border-radius: 16px 16px 16px 4px;
        color: var(--text);
    }
    .ai-avatar {
        width: 32px;
        height: 32px;
        border-radius: 10px;
        background: linear-gradient(135deg, var(--accent), var(--accent-hover));
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
        box-shadow: 0 2px 8px rgba(212,138,46,0.2);
    }
    .bubble-meta {
        font-size: 10px;
        color: var(--text-muted);
        margin-top: 4px;
        padding: 0 4px;
    }
    .source-tags {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
        margin-top: 8px;
    }
    .source-tag {
        font-size: 10px;
        padding: 3px 8px;
        background: rgba(212,138,46,0.12);
        color: var(--warning);
        border-radius: 6px;
        border: 1px solid rgba(212,138,46,0.3);
    }
    .suggestion-chip {
        padding: 10px 14px;
        background: var(--surface2);
        border: 1px solid var(--border);
        border-radius: 12px;
        font-size: 12px;
        cursor: pointer;
        transition: all 0.2s ease;
        color: var(--text);
        text-align: left;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .suggestion-chip:hover {
        background: var(--surface);
        border-color: var(--accent);
        transform: translateY(-2px);
    }

    .typing-indicator {
        display: flex;
        gap: 5px;
        padding: 12px 16px;
        background: var(--surface2);
        border-radius: 16px;
        border: 1px solid var(--border);
        width: fit-content;
    }
    .typing-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: var(--accent);
        animation: typingBounce 1.4s ease-in-out infinite;
    }
    .typing-dot:nth-child(2) { animation-delay: 0.2s; }
    .typing-dot:nth-child(3) { animation-delay: 0.4s; }

    @keyframes typingBounce {
        0%, 60%, 100% { transform: translateY(0); opacity: 0.4; }
        30% { transform: translateY(-5px); opacity: 1; }
    }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(6px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .streaming-cursor {
        display: inline-block;
        color: var(--accent);
        animation: cursorBlink 0.8s infinite;
        font-weight: 800;
        margin-left: 3px;
    }
    @keyframes cursorBlink {
        0%, 100% { opacity: 1; }
        50% { opacity: 0; }
    }
</style>

<script>
    let currentClassId = <?php echo e($selectedClassId ? (int)$selectedClassId : 'null'); ?>;
    let currentSubjectId = <?php echo e($selectedSubjectId ?? 'null'); ?>;
    let currentSessionId = <?php echo $selectedSessionId ? "'" . $selectedSessionId . "'" : 'null'; ?>;
    let currentAnswerMode = 'short';
    let isAdministration = <?php echo e($isAdministration ? 'true' : 'false'); ?>;
    const allSubjects = <?php echo json_encode($subjectsJson ?? [], 15, 512) ?>;

    function escapeHtml(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta && meta.content ? meta.content : '<?php echo e(csrf_token()); ?>';
    }

    function onClassFilterChange(classId) {
        currentClassId = classId ? parseInt(classId) : null;
        applyClassFilter(currentClassId);
    }

    function applyClassFilter(classId) {
        const step2Section = document.getElementById('step2-subject-section');
        const subjectSelect = document.getElementById('center-subject-selector');
        const quickSubjContainer = document.getElementById('quick-subj-container');
        const noClassPrompt = document.getElementById('no-class-prompt');

        const isStudent = <?php echo e(auth()->user()->isStudent() ? 'true' : 'false'); ?>;

        if (!isStudent && !classId && (isAdministration || <?php echo e($classes->count() > 1 ? 'true' : 'false'); ?>)) {
            if (step2Section) step2Section.style.display = 'none';
            if (noClassPrompt) noClassPrompt.style.display = 'block';
            if (subjectSelect) {
                subjectSelect.innerHTML = '<option value="">— Select a Class first —</option>';
            }
            if (quickSubjContainer) {
                quickSubjContainer.innerHTML = '';
            }
            onCenterSubjectChange('');
            return;
        }

        // We have a class selected or this is a student with 1 class
        const filtered = allSubjects.filter(s => !classId || s.class_id === classId);

        if (step2Section) step2Section.style.display = 'block';
        if (noClassPrompt) noClassPrompt.style.display = 'none';

        if (subjectSelect) {
            subjectSelect.innerHTML = '';
            const defaultOpt = document.createElement('option');
            defaultOpt.value = '';
            defaultOpt.textContent = filtered.length > 0 ? '— Select a Subject —' : '— No subjects registered for this class —';
            subjectSelect.appendChild(defaultOpt);

            filtered.forEach(s => {
                const opt = document.createElement('option');
                opt.value = s.id;
                opt.textContent = s.name + (s.materials_count > 0 ? ` — (${s.materials_count} study books loaded)` : '');
                if (currentSubjectId === s.id) {
                    opt.selected = true;
                }
                subjectSelect.appendChild(opt);
            });
        }

        if (quickSubjContainer) {
            quickSubjContainer.innerHTML = '';
            filtered.forEach(s => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'quick-subj-btn' + (currentSubjectId === s.id ? ' active' : '');
                btn.innerHTML = `📖 ${escapeHtml(s.name)}`;
                btn.onclick = function() { selectSubjectQuick(s.id); };
                quickSubjContainer.appendChild(btn);
            });
        }

        const isCurrentSubjectValid = filtered.some(s => s.id === currentSubjectId);
        if (isCurrentSubjectValid && currentSubjectId) {
            if (subjectSelect) subjectSelect.value = currentSubjectId;
            onCenterSubjectChange(currentSubjectId);
        } else {
            if (subjectSelect) subjectSelect.value = '';
            onCenterSubjectChange('');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        applyClassFilter(currentClassId);
    });

    function setWelcomeMode(mode) {
        currentAnswerMode = mode;
        document.querySelectorAll('.welcome-mode-card').forEach(el => el.classList.remove('active'));
        const card = document.getElementById('card-mode-' + mode);
        if (card) card.classList.add('active');

        // Sync with workspace top bar
        syncWorkspaceModePills(mode);
    }

    function setAnswerMode(mode) {
        currentAnswerMode = mode;
        syncWorkspaceModePills(mode);
        
        // Sync with welcome card
        document.querySelectorAll('.welcome-mode-card').forEach(el => el.classList.remove('active'));
        const card = document.getElementById('card-mode-' + mode);
        if (card) card.classList.add('active');
    }

    function syncWorkspaceModePills(mode) {
        document.querySelectorAll('.mode-pill').forEach(el => el.classList.remove('active'));
        const btn = document.getElementById('mode-btn-' + mode);
        if (btn) btn.classList.add('active');
    }

    function onCenterSubjectChange(val) {
        const btn = document.getElementById('start-chat-btn');
        if (btn) btn.disabled = !val;
        currentSubjectId = val ? parseInt(val) : null;
        document.querySelectorAll('.quick-subj-btn').forEach(b => {
            b.classList.toggle('active', parseInt(b.getAttribute('onclick')?.match(/\d+/)?.[0]) === currentSubjectId);
        });
    }

    function selectSubjectQuick(subjectId) {
        const select = document.getElementById('center-subject-selector');
        if (select) {
            select.value = subjectId;
            onCenterSubjectChange(subjectId);
        }
    }

    function confirmCenterSubject() {
        const val = document.getElementById('center-subject-selector')?.value;
        if (val) {
            selectSubject(val);
        }
    }

    function selectSubject(subjectId) {
        if (!subjectId) return;
        let url = "<?php echo e(route('lms.chatbot.index')); ?>?subject_id=" + subjectId;
        if (currentClassId) {
            url += "&class_id=" + currentClassId;
        }
        window.location.href = url;
    }

    function openSubjectPicker() {
        document.getElementById('subject-selection-screen').style.display = 'flex';
        document.getElementById('chat-workspace').style.display = 'none';
        if (isAdministration || currentClassId) {
            applyClassFilter(currentClassId);
        }
    }


    async function loadSessions(subjectId) {
        try {
            const res = await fetch(`/lms/chatbot/sessions?subject_id=${subjectId}`, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': getCsrfToken() }
            });
            const data = await res.json();
            renderSessions(data.sessions);
        } catch (err) {
            console.error('Failed to load sessions:', err);
        }
    }

    function renderSessions(sessions) {
        const container = document.getElementById('sessions-list');
        const countBadge = document.getElementById('session-count-badge');
        if (countBadge) countBadge.textContent = sessions.length;

        if (!sessions.length) {
            container.innerHTML = '<div style="text-align:center;padding:30px 10px;color:var(--text-muted);font-size:13px">No past chats yet.<br>Click ✨ New Conversation!</div>';
            return;
        }

        const top10 = sessions.slice(0, 10);

        container.innerHTML = top10.map(s => `
            <div class="session-item ${s.session_id === currentSessionId ? 'active' : ''}"
                 onclick="loadSession('${s.session_id}')"
                 data-session="${s.session_id}">
                <div style="font-size:13px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:var(--text)">
                    ${escapeHtml(s.first_message?.substring(0, 32) || 'New Chat')}
                </div>
                <div style="font-size:11px;color:var(--text-muted);margin-top:3px">
                    🕒 ${timeAgo(s.last_active)}
                </div>
                <button class="delete-session-btn" onclick="event.stopPropagation(); deleteSession('${s.session_id}')" title="Delete">✕</button>
            </div>
        `).join('');
    }

    async function loadSession(sessionId) {
        if (!currentSubjectId) return;
        currentSessionId = sessionId;

        document.querySelectorAll('.session-item').forEach(el => {
            el.classList.toggle('active', el.dataset.session === sessionId);
        });

        try {
            const res = await fetch(`/lms/chatbot/history?subject_id=${currentSubjectId}&session_id=${sessionId}`, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': getCsrfToken() }
            });
            const data = await res.json();
            renderMessages(data.messages);
        } catch (err) {
            console.error('Failed to load session history:', err);
        }
    }

    function startNewChat() {
        if (!currentSubjectId) return;
        currentSessionId = null;
        document.querySelectorAll('.session-item').forEach(el => el.classList.remove('active'));
        clearChat();
        document.getElementById('chat-input').focus();
    }

    function clearChat() {
        const messagesDiv = document.getElementById('chat-messages');
        messagesDiv.innerHTML = `
            <div id="welcome-state" style="margin:auto;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:30px;max-width:600px;width:100%">
                <div style="font-size:48px;margin-bottom:14px">🧠</div>
                <h3 style="font-family:'Space Grotesk',sans-serif;font-size:20px;font-weight:700;margin-bottom:8px;color:var(--text)">
                    UPLYFT AI Tutor
                </h3>
                <p style="color:var(--text-muted);line-height:1.5;margin-bottom:20px;font-size:13px">
                    Ask any question regarding your subject syllabus or textbook topics.
                </p>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;width:100%">
                    <div class="suggestion-chip" onclick="useSuggestion('Explain key concepts from Chapter 1')">
                        📖 Key concepts from Chapter 1
                    </div>
                    <div class="suggestion-chip" onclick="useSuggestion('Solve a practice problem step by step')">
                        🧮 Practice problem breakdown
                    </div>
                    <div class="suggestion-chip" onclick="useSuggestion('Summarize the main topics covered in the syllabus')">
                        📋 Full syllabus summary
                    </div>
                    <div class="suggestion-chip" onclick="useSuggestion('Create revision notes for exam prep')">
                        🗂️ Exam revision notes
                    </div>
                </div>
            </div>
        `;
    }

    function renderMessages(messages) {
        const messagesDiv = document.getElementById('chat-messages');
        if (!messages || !messages.length) {
            clearChat();
            return;
        }

        messagesDiv.innerHTML = messages.map(msg => {
            const isUser = msg.role === 'user';
            const timeStr = msg.created_at ? new Date(msg.created_at).toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'}) : '';

            if (isUser) {
                return `
                    <div class="chat-bubble user-bubble">
                        <div class="bubble-content user-content">${escapeHtml(msg.message)}</div>
                        <div class="bubble-meta">${timeStr}</div>
                    </div>
                `;
            } else {
                const sourcesHtml = msg.sources && msg.sources.length ? `
                    <div class="source-tags">
                        ${msg.sources.map(s => `<span class="source-tag">📄 ${escapeHtml(s)}</span>`).join('')}
                    </div>
                ` : '';

                return `
                    <div class="chat-bubble ai-bubble">
                        <div style="display:flex;gap:12px;align-items:flex-start">
                            <div class="ai-avatar">🤖</div>
                            <div style="min-width:0;flex:1">
                                <div class="bubble-content ai-content">${formatMarkdown(msg.message)}</div>
                                ${sourcesHtml}
                                <div class="bubble-meta">${timeStr}</div>
                            </div>
                        </div>
                    </div>
                `;
            }
        }).join('');

        scrollToBottom();
    }

    async function sendChatMessage(e) {
        e.preventDefault();
        if (!currentSubjectId) return;

        const input = document.getElementById('chat-input');
        const question = input.value.trim();
        if (!question) return;

        const welcome = document.getElementById('welcome-state');
        if (welcome) welcome.remove();

        const messagesDiv = document.getElementById('chat-messages');
        const now = new Date().toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'});

        // 1. Render User Bubble
        messagesDiv.insertAdjacentHTML('beforeend', `
            <div class="chat-bubble user-bubble">
                <div class="bubble-content user-content">${escapeHtml(question)}</div>
                <div class="bubble-meta">${now}</div>
            </div>
        `);

        input.value = '';
        autoResize(input);
        scrollToBottom();

        // 2. Render Streaming Assistant Bubble
        const aiBubbleId = 'ai-stream-' + Date.now();
        const contentId = 'ai-content-' + Date.now();
        const metaId = 'ai-meta-' + Date.now();
        const sourcesContainerId = 'ai-sources-' + Date.now();

        messagesDiv.insertAdjacentHTML('beforeend', `
            <div class="chat-bubble ai-bubble" id="${aiBubbleId}">
                <div style="display:flex;gap:12px;align-items:flex-start">
                    <div class="ai-avatar">🤖</div>
                    <div style="min-width:0;flex:1">
                        <div class="bubble-content ai-content" id="${contentId}"><span class="streaming-cursor">▌</span></div>
                        <div id="${sourcesContainerId}"></div>
                        <div class="bubble-meta" id="${metaId}">${now}</div>
                    </div>
                </div>
            </div>
        `);
        scrollToBottom();
        setInputState(false);

        let accumulatedAnswer = '';
        let streamSucceeded = false;

        try {
            const response = await fetch('<?php echo e(route('lms.chatbot.stream')); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'text/event-stream',
                    'X-CSRF-TOKEN': getCsrfToken()
                },
                body: JSON.stringify({
                    subject_id: currentSubjectId,
                    question: question,
                    session_id: currentSessionId,
                    mode: currentAnswerMode
                })
            });

            if (!response.ok || !response.body) {
                throw new Error('Streaming connection failed, status: ' + response.status);
            }

            const reader = response.body.getReader();
            const decoder = new TextDecoder('utf-8');
            let buffer = '';

            while (true) {
                const { done, value } = await reader.read();
                if (done) break;

                buffer += decoder.decode(value, { stream: true });
                const lines = buffer.split('\n');
                buffer = lines.pop(); // keep last partial line

                let currentEvent = 'token';

                for (const line of lines) {
                    const trimmed = line.trim();
                    if (!trimmed) continue;

                    if (trimmed.startsWith('event:')) {
                        currentEvent = trimmed.substring(6).trim();
                        continue;
                    }

                    if (trimmed.startsWith('data:')) {
                        const jsonStr = trimmed.substring(5).trim();
                        try {
                            const payload = JSON.parse(jsonStr);

                            if (currentEvent === 'done' || payload.full_answer) {
                                streamSucceeded = true;
                                if (payload.session_id) currentSessionId = payload.session_id;

                                if (payload.sources && payload.sources.length) {
                                    const sourcesHtml = `
                                        <div class="source-tags" style="margin-top:8px">
                                            ${payload.sources.map(s => `<span class="source-tag">📄 ${escapeHtml(s)}</span>`).join('')}
                                        </div>
                                    `;
                                    const srcEl = document.getElementById(sourcesContainerId);
                                    if (srcEl) srcEl.innerHTML = sourcesHtml;
                                }

                                if (payload.full_answer && !accumulatedAnswer) {
                                    accumulatedAnswer = payload.full_answer;
                                }
                            } else if (payload.token) {
                                streamSucceeded = true;
                                accumulatedAnswer += payload.token;
                                const contentEl = document.getElementById(contentId);
                                if (contentEl) {
                                    contentEl.innerHTML = formatMarkdown(accumulatedAnswer) + '<span class="streaming-cursor">▌</span>';
                                }
                                scrollToBottom();
                            }
                        } catch (e) {
                            // Ignored partial parse
                        }
                    }
                }
            }

            // Remove cursor on complete
            const contentEl = document.getElementById(contentId);
            if (contentEl) {
                contentEl.innerHTML = formatMarkdown(accumulatedAnswer || 'No response generated.');
            }
            scrollToBottom();
            loadSessions(currentSubjectId);

        } catch (streamErr) {
            console.warn('Live SSE Stream error, attempting REST fallback:', streamErr);

            if (!streamSucceeded || !accumulatedAnswer) {
                try {
                    const res = await fetch('<?php echo e(route('lms.chatbot.send')); ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': getCsrfToken()
                        },
                        body: JSON.stringify({
                            subject_id: currentSubjectId,
                            question: question,
                            session_id: currentSessionId,
                            mode: currentAnswerMode
                        })
                    });

                    const data = await res.json();
                    if (!res.ok) throw new Error(data.error || 'Failed to get answer.');

                    currentSessionId = data.session_id;

                    const sourcesHtml = data.sources && data.sources.length ? `
                        <div class="source-tags" style="margin-top:8px">
                            ${data.sources.map(s => `<span class="source-tag">📄 ${escapeHtml(s)}</span>`).join('')}
                        </div>
                    ` : '';

                    const contentEl = document.getElementById(contentId);
                    if (contentEl) contentEl.innerHTML = formatMarkdown(data.answer);
                    const srcEl = document.getElementById(sourcesContainerId);
                    if (srcEl) srcEl.innerHTML = sourcesHtml;

                    scrollToBottom();
                    loadSessions(currentSubjectId);
                } catch (fallbackErr) {
                    const contentEl = document.getElementById(contentId);
                    if (contentEl) {
                        contentEl.innerHTML = `<span style="color:var(--danger)">⚠️ ${escapeHtml(fallbackErr.message || 'An error occurred.')}</span>`;
                    }
                    scrollToBottom();
                }
            }
        } finally {
            setInputState(true);
        }
    }

    async function deleteSession(sessionId) {
        if (!confirm('Are you sure you want to delete this chat session?')) return;
        try {
            await fetch('<?php echo e(route('lms.chatbot.deleteSession')); ?>', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                },
                body: JSON.stringify({
                    subject_id: currentSubjectId,
                    session_id: sessionId
                })
            });

            if (currentSessionId === sessionId) {
                currentSessionId = null;
                clearChat();
            }

            loadSessions(currentSubjectId);
        } catch (err) {
            console.error('Failed to delete session:', err);
        }
    }

    function useSuggestion(text) {
        if (!currentSubjectId) {
            openSubjectPicker();
            return;
        }
        const input = document.getElementById('chat-input');
        input.value = text;
        autoResize(input);
        input.focus();
    }

    function handleKeyDown(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendChatMessage(e);
        }
    }

    function autoResize(el) {
        el.style.height = 'auto';
        el.style.height = Math.min(el.scrollHeight, 120) + 'px';
    }

    function setInputState(enabled) {
        document.getElementById('chat-input').disabled = !enabled;
        document.getElementById('send-btn').disabled = !enabled;
        if (enabled) document.getElementById('chat-input').focus();
    }

    function scrollToBottom() {
        const el = document.getElementById('chat-messages');
        el.scrollTop = el.scrollHeight;
    }

    function escapeHtml(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function formatMarkdown(str) {
        if (!str) return '';
        let raw = str;
        // Pre-strip closed <think> or <reasoning> blocks
        raw = raw.replace(/<think>[\s\S]*?<\/think>/gi, '')
                 .replace(/<reasoning>[\s\S]*?<\/reasoning>/gi, '');

        // If <think> tag is unclosed, strip everything inside <think> up to the final answer text
        if (raw.includes('<think>')) {
            const parts = raw.split('<think>');
            const lastPart = parts[parts.length - 1];
            raw = lastPart.replace(/^[\s\S]*?(?=\n\n[A-Z0-9#]|\n\n(?:Hello|To understand|According|###))/i, '');
        }

        // Remove any explicit "Here's a thinking process:" text
        raw = raw.replace(/^(?:#*\s*)?(?:Here's a )?thinking process:[\s\S]*?(?=\n\n[A-Z0-9#]|\n\n(?:Hello|To understand|According|###))/gi, '');

        let text = escapeHtml(raw.trim());
        // Formatted Markdown Headings (###, ##, #)
        text = text.replace(/^### (.*$)/gim, '<h4 style="font-size:14px;font-weight:800;color:var(--accent2);margin-top:12px;margin-bottom:6px">$1</h4>');
        text = text.replace(/^## (.*$)/gim, '<h3 style="font-size:15px;font-weight:800;color:var(--text);margin-top:14px;margin-bottom:6px">$1</h3>');
        text = text.replace(/^# (.*$)/gim, '<h2 style="font-size:16px;font-weight:800;color:var(--text);margin-top:16px;margin-bottom:8px">$1</h2>');
        // Bold text **bold**
        text = text.replace(/\*\*(.*?)\*\*/g, '<strong style="color:var(--accent2);font-weight:700">$1</strong>');
        // Linebreaks
        text = text.replace(/\n/g, '<br>');
        return text;
    }

    function timeAgo(dateStr) {
        if (!dateStr) return '';
        const date = new Date(dateStr);
        const now = new Date();
        const seconds = Math.floor((now - date) / 1000);
        if (seconds < 60) return 'Just now';
        const minutes = Math.floor(seconds / 60);
        if (minutes < 60) return minutes + 'm ago';
        const hours = Math.floor(minutes / 60);
        if (hours < 24) return hours + 'h ago';
        const days = Math.floor(hours / 24);
        return days + 'd ago';
    }
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('lms.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\UPLYFT\uplifyt\resources\views\lms\chatbot\index.blade.php ENDPATH**/ ?>