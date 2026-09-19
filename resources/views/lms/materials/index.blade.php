@extends('lms.layouts.app')

@section('title', 'Course Materials & Notes')
@section('breadcrumb', 'Course Materials')

@section('content')
@php
    $subject = \App\Models\Subject::with('instituteClass')->find($subjectId);
    $isTeacherOrPrincipal = auth()->user()->isTeacher() || auth()->user()->isPrincipal();
@endphp

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px">
    <div>
        <h1 style="font-family:'Space Grotesk',sans-serif;font-size:24px;font-weight:700">📚 Course Materials &amp; Notes</h1>
        <p style="color:var(--text-muted);font-size:14px;margin-top:2px">
            {{ $subject->subject_name ?? 'Subject' }} (@if($subject)? {{ $subject->subject_code }} @endif) — Upload syllabus documents and course readings.
        </p>
    </div>
    <a href="{{ $subject && $subject->institute_class_id ? route('lms.subjects.class', $subject->institute_class_id) : route('lms.subjects.list') }}" class="btn btn-ghost btn-sm">← Back to Subjects</a>
</div>

<div class="grid-2">
    {{-- Upload Material --}}
    @if($isTeacherOrPrincipal)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">📤 Upload Study Material</h3>
            </div>
            <form method="POST" action="{{ route('lms.materials.store', $subjectId) }}" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label>Title *</label>
                    <input type="text" name="title" required maxlength="255" placeholder="e.g., Algebra Textbook Chapter 1">
                </div>
                <div class="form-group">
                    <label>Document Type</label>
                    <select name="document_type">
                        <option value="textbook">Textbook</option>
                        <option value="notes">Notes</option>
                        <option value="syllabus">Syllabus</option>
                        <option value="reference">Reference</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Document File * (PDF, DOC, DOCX - No max size limit)</label>
                    <input type="file" name="document" accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" required>
                </div>
                <button type="submit" class="btn btn-primary">Upload & Index into RAG</button>
            </form>
        </div>
    @endif

    {{-- RAG Chatbot --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">🤖 Study Assistant</h3>
            <a href="{{ route('lms.chatbot.index', ['subject_id' => $subjectId]) }}" class="btn btn-ghost btn-sm">Full Screen Chat ↗</a>
        </div>
        <p class="muted" style="margin-bottom:12px">Ask questions about this subject. The assistant answers using the uploaded materials.</p>
        <div id="chat-log" style="background:var(--surface2);border:1px solid var(--border);border-radius:10px;padding:14px;min-height:140px;max-height:260px;overflow-y:auto;margin-bottom:12px;font-size:13px">
            <div style="color:var(--text-muted)">Ask a question below to get started…</div>
        </div>
        <form id="chat-form" style="display:flex;gap:8px">
            <input type="text" id="chat-input" placeholder="e.g., What is covered in chapter 3?" style="flex:1">
            <button type="submit" class="btn btn-primary btn-sm">Send</button>
        </form>
    </div>
</div>

{{-- Materials List --}}
<div class="card">
    <div class="card-header">
        <h3 class="card-title">📄 Indexed Materials ({{ $materials->count() }})</h3>
    </div>

    @forelse($materials as $material)
        <div style="display:flex;align-items:center;justify-content:space-between;gap:14px;padding:14px;border-bottom:1px solid var(--border)">
            <div style="display:flex;align-items:center;gap:14px;min-width:0">
                <div style="width:38px;height:38px;border-radius:8px;background:rgba(212,138,46,0.15);color:var(--accent2);display:flex;align-items:center;justify-content:center;flex-shrink:0">📕</div>
                <div style="min-width:0">
                    <div style="font-weight:600;font-size:14px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $material->title }}</div>
                    <div class="muted" style="font-size:12px;margin-top:2px">
                        {{ ucfirst($material->document_type) }} ·
                        {{ $material->file_size_formatted }} ·
                        uploaded by {{ $material->uploader->name ?? '—' }}
                    </div>
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:10px;flex-shrink:0">
                @if($material->is_rag_indexed)
                    <span class="badge badge-green">Indexed</span>
                @else
                    <span class="badge badge-yellow">Not Indexed</span>
                @endif
                @if($isTeacherOrPrincipal && (auth()->id() === $material->uploaded_by || auth()->user()->isPrincipal()))
                    <form method="POST" action="{{ route('lms.materials.destroy', [$subjectId, $material]) }}"
                          onsubmit="return confirm('Delete this material?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">✕</button>
                    </form>
                @endif
            </div>
        </div>
    @empty
        <div style="text-align:center;padding:40px">
            <div style="font-size:36px;margin-bottom:10px">📕</div>
            <p class="muted">No materials uploaded yet for this subject.</p>
        </div>
    @endforelse
</div>

@endsection

@section('modals')
<script>
    document.getElementById('chat-form').addEventListener('submit', async function (e) {
        e.preventDefault();
        const input = document.getElementById('chat-input');
        const question = input.value.trim();
        if (!question) return;

        const log = document.getElementById('chat-log');
        log.querySelector('div[color]')?.remove();

        const userBubble = document.createElement('div');
        userBubble.style.textAlign = 'right';
        userBubble.style.marginBottom = '8px';
        userBubble.innerHTML = `<div style="display:inline-block;background:var(--accent);color:#fff;border-radius:10px 10px 2px 10px;padding:8px 12px;max-width:80%">${escapeHtml(question)}</div>`;
        log.appendChild(userBubble);

        const aiBubble = document.createElement('div');
        aiBubble.style.marginBottom = '8px';
        aiBubble.innerHTML = `<div style="display:inline-block;background:var(--surface);border:1px solid var(--border);border-radius:10px 10px 10px 2px;padding:8px 12px;max-width:85%;color:var(--text-muted)">Thinking…</div>`;
        log.appendChild(aiBubble);
        log.scrollTop = log.scrollHeight;

        try {
            const res = await fetch('{{ route('lms.materials.chatbot', $subjectId) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ question }),
            });

            if (!res.ok) throw new Error(await res.text());

            const data = await res.json();
            let sources = data.sources && data.sources.length
                ? '<br><div style="margin-top:8px;font-size:11px;color:var(--accent2);font-weight:600">Sources: ' + data.sources.join(', ') + '</div>'
                : '';
            aiBubble.innerHTML = `<div style="display:inline-block;background:var(--surface);border:1px solid var(--border);border-radius:10px 10px 10px 2px;padding:12px 14px;max-width:90%">${formatMarkdownToHtml(data.answer)}${sources}</div>`;
        } catch (err) {
            aiBubble.innerHTML = `<div style="display:inline-block;background:var(--surface);border:1px solid var(--danger);border-radius:10px 10px 10px 2px;padding:12px 14px;max-width:85%;color:var(--danger)">Error: ${escapeHtml(err.message)}</div>`;
        }
        input.value = '';
        log.scrollTop = log.scrollHeight;
    });

    function formatMarkdownToHtml(text) {
        if (!text) return '';
        let clean = text.replace(/<>/g, ' ').replace(/< >/g, ' ').replace(/&lt;&gt;/g, ' ');
        let html = escapeHtml(clean);
        html = html.replace(/^### (.*$)/gim, '<h4 style="font-size:14px;font-weight:700;color:var(--accent2);margin:14px 0 6px">$1</h4>');
        html = html.replace(/^#### (.*$)/gim, '<h5 style="font-size:12px;font-weight:700;color:var(--accent);margin:10px 0 4px">$1</h5>');
        html = html.replace(/^## (.*$)/gim, '<h3 style="font-size:15px;font-weight:700;color:var(--text);margin:16px 0 8px">$1</h3>');
        html = html.replace(/--- Source: (.*?) ---/gi, '<div style="background:rgba(212,138,46,0.12);border-left:3px solid var(--accent);padding:6px 10px;border-radius:6px;margin:10px 0;font-weight:600;font-size:12px;color:var(--warning)">📖 Source: $1</div>');
        html = html.replace(/\[Page (\d+)\]/gi, '<span style="display:inline-block;background:var(--surface2);border:1px solid var(--border);padding:2px 6px;border-radius:4px;font-weight:700;font-size:10px;color:var(--accent2);margin:4px 4px 4px 0">Page $1</span>');
        html = html.replace(/\*\*(.*?)\*\*/g, '<strong style="color:var(--text);font-weight:600">$1</strong>');
        html = html.replace(/^\s*[-*]\s+(.*$)/gim, '<li style="margin-left:14px;margin-bottom:6px;line-height:1.6">$1</li>');
        html = html.replace(/\n\n+/g, '</p><p style="margin-bottom:12px;line-height:1.6">');
        html = html.replace(/\n/g, '<br>');
        return `<div style="line-height:1.6;font-size:13px"><p style="margin-bottom:12px;line-height:1.6">${html}</p></div>`;
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }
</script>
@endsection