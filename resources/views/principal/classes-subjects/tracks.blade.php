@extends('layouts.app')

@section('title', 'Academic Tracks & Subject Pool - ' . $class->name)
@section('page-header', 'Curriculum Tracks & Subject Bundles')

@section('content')
<div class="space-y-8 max-w-7xl mx-auto">
    <!-- Header Banner & Navigation -->
    <div class="liquid-glass-card p-6 md:p-8 border border-slate-700 bg-[#1E293B] shadow-2xs relative">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <a href="{{ route('principal.classes-subjects.index') }}" class="text-xs font-semibold text-teal-400 hover:text-teal-300 flex items-center gap-1 transition-colors">
                        <i class="fa-solid fa-arrow-left"></i> Back to Classes & Subjects
                    </a>
                    <span class="text-slate-500">•</span>
                    <span class="text-xs text-slate-400 uppercase tracking-wider font-bold">Curriculum Engineering</span>
                </div>
                <h1 class="text-2xl md:text-3xl font-extrabold text-[#F8FAFC] font-display flex items-center gap-3">
                    <i class="fa-solid fa-network-wired text-teal-400"></i>
                    <span>{{ $class->name }}</span>
                    <span class="text-xs px-3 py-1 rounded-full bg-teal-500/10 text-teal-400 border border-teal-500/30 font-sans font-semibold">
                        {{ count($poolData['tracks']) }} Active Tracks
                    </span>
                </h1>
                <p class="text-sm text-slate-400 mt-1">
                    Configure the class subject pool (Compulsory vs. Elective) and bundle electives into specialized academic tracks (e.g., Pre-Medical, Pre-Engineering, ICS).
                </p>
            </div>

            <div class="flex items-center gap-3">
                <button type="button" onclick="openCreateTrackModal()" class="btn-primary py-2.5 px-4 text-xs font-bold shadow-md flex items-center gap-2">
                    <i class="fa-solid fa-plus"></i> Create Academic Track
                </button>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm font-semibold flex items-center gap-2">
            <i class="fa-solid fa-circle-check"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- LEFT: SUBJECT POOL CONFIGURATION (COMPULSORY VS ELECTIVE) -->
        <div class="lg:col-span-6 space-y-6">
            <div class="liquid-glass-card p-6 border border-slate-700 bg-[#1E293B] shadow-2xs">
                <div class="border-b border-slate-700 pb-4 mb-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-[#F8FAFC] font-display flex items-center gap-2">
                            <i class="fa-solid fa-layer-group text-teal-400"></i> Class Subject Pool
                        </h2>
                        <p class="text-xs text-slate-400 mt-0.5">Flag each available subject as Compulsory or Elective.</p>
                    </div>
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-md bg-slate-800 text-slate-300 border border-slate-700">
                        {{ $allClassSubjects->count() }} Subjects
                    </span>
                </div>

                @if($allClassSubjects->isEmpty())
                    <div class="p-8 text-center text-slate-400 border border-dashed border-slate-700 rounded-xl">
                        <i class="fa-solid fa-book-open text-3xl mb-2 text-slate-500"></i>
                        <p class="text-sm font-semibold">No subjects registered for this class yet.</p>
                        <p class="text-xs text-slate-500 mt-1">Please add subjects to the class catalog first.</p>
                    </div>
                @else
                    <form action="{{ route('principal.classes.subject-pool.store', $class) }}" method="POST" class="space-y-3">
                        @csrf
                        <div class="space-y-2 max-h-[500px] overflow-y-auto pr-1">
                            @foreach($allClassSubjects as $idx => $subj)
                                @php
                                    $existingConfig = $class->classSubjects->firstWhere('subject_id', $subj->id);
                                    $currentType = $existingConfig?->subject_type ?? 'compulsory';
                                    $creditHours = $existingConfig?->credit_hours ?? $subj->credit_hours ?? 3;
                                @endphp
                                <div class="p-3.5 rounded-xl bg-[#0F172A] border border-slate-700/80 hover:border-slate-600 transition-colors flex items-center justify-between gap-3">
                                    <input type="hidden" name="subjects[{{ $idx }}][subject_id]" value="{{ $subj->id }}">
                                    
                                    <div class="min-w-0">
                                        <div class="font-bold text-sm text-[#F8FAFC] truncate">{{ $subj->subject_name }}</div>
                                        <div class="text-[11px] text-slate-400 font-mono">{{ $subj->subject_code ?: 'SUB-' . $subj->id }}</div>
                                    </div>

                                    <div class="flex items-center gap-3 shrink-0">
                                        <div class="flex items-center gap-1.5">
                                            <input type="number" name="subjects[{{ $idx }}][credit_hours]" value="{{ $creditHours }}" min="1" max="10" placeholder="Cr" title="Credit Hours" class="w-14 p-1.5 text-center text-xs font-bold rounded-lg bg-slate-800 border border-slate-700 text-[#F8FAFC]">
                                            <span class="text-[10px] text-slate-500 font-semibold">CR</span>
                                        </div>

                                        <select name="subjects[{{ $idx }}][subject_type]" class="p-2 rounded-lg text-xs font-bold bg-slate-800 text-slate-200 border border-slate-700 outline-none focus:border-teal-500">
                                            <option value="compulsory" {{ $currentType === 'compulsory' ? 'selected' : '' }}>Compulsory</option>
                                            <option value="elective" {{ $currentType === 'elective' ? 'selected' : '' }}>Elective</option>
                                        </select>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="pt-3 border-t border-slate-700 flex justify-end">
                            <button type="submit" class="btn-primary py-2 px-5 text-xs font-bold shadow flex items-center gap-2">
                                <i class="fa-solid fa-floppy-disk"></i> Save Subject Pool
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>

        <!-- RIGHT: ACADEMIC TRACKS / BUNDLES LIST -->
        <div class="lg:col-span-6 space-y-6">
            <div class="liquid-glass-card p-6 border border-slate-700 bg-[#1E293B] shadow-2xs">
                <div class="border-b border-slate-700 pb-4 mb-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-[#F8FAFC] font-display flex items-center gap-2">
                            <i class="fa-solid fa-cubes-stacked text-teal-400"></i> Curriculum Tracks (Bundles)
                        </h2>
                        <p class="text-xs text-slate-400 mt-0.5">Predefined elective bundles for career pathways.</p>
                    </div>
                    <button type="button" onclick="openCreateTrackModal()" class="text-xs font-bold text-teal-400 hover:text-teal-300">
                        + New Track
                    </button>
                </div>

                <div class="space-y-4">
                    @forelse($class->academicTracks as $track)
                        <div class="p-4 rounded-xl bg-[#0F172A] border border-slate-700 relative group hover:border-teal-500/40 transition-colors">
                            <div class="flex items-start justify-between gap-3 mb-2">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="font-extrabold text-sm text-[#F8FAFC]">{{ $track->track_name }}</h3>
                                        @if($track->track_code)
                                            <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded bg-slate-800 text-teal-400 border border-slate-700">
                                                {{ $track->track_code }}
                                            </span>
                                        @endif
                                    </div>
                                    @if($track->description)
                                        <p class="text-xs text-slate-400 mt-1">{{ $track->description }}</p>
                                    @endif
                                </div>

                                <div class="flex items-center gap-2">
                                    <form action="{{ route('principal.tracks.destroy', $track) }}" method="POST" onsubmit="return confirm('Delete track {{ $track->track_name }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-slate-500 hover:text-rose-400 text-xs p-1" title="Delete Track">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- Bundled Electives -->
                            <div class="mt-3 pt-3 border-t border-slate-800">
                                <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                                    <span>Bundled Electives ({{ $track->subjects->count() }})</span>
                                    @if($track->allow_custom_electives)
                                        <span class="text-[10px] text-teal-400 flex items-center gap-1 font-bold">
                                            <i class="fa-solid fa-sliders"></i> Custom Combinations Allowed
                                        </span>
                                    @else
                                        <span class="text-[10px] text-slate-500 flex items-center gap-1 font-medium">
                                            <i class="fa-solid fa-lock"></i> Strict Bundle
                                        </span>
                                    @endif
                                </div>
                                <div class="flex flex-wrap gap-1.5">
                                    @forelse($track->subjects as $ts)
                                        <span class="text-xs px-2.5 py-1 rounded-md bg-slate-800 text-slate-200 border border-slate-700/80 font-medium">
                                            {{ $ts->subject_name }}
                                        </span>
                                    @empty
                                        <span class="text-xs text-slate-500 italic">No subjects bundled yet.</span>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-slate-400 border border-dashed border-slate-700 rounded-xl">
                            <i class="fa-solid fa-box-open text-3xl mb-2 text-slate-500"></i>
                            <p class="text-sm font-semibold">No Academic Tracks defined for this class.</p>
                            <p class="text-xs text-slate-500 mt-1">If this class offers electives (e.g. FSc Pre-Med, Pre-Engg, ICS), create tracks to automate enrollment.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CREATE TRACK MODAL -->
<div id="createTrackModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-950/80 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="liquid-glass-card w-full max-w-lg p-6 border border-slate-700 bg-[#1E293B] shadow-2xl relative">
        <div class="flex items-center justify-between pb-4 border-b border-slate-700 mb-4">
            <h3 class="text-lg font-bold text-[#F8FAFC] font-display flex items-center gap-2">
                <i class="fa-solid fa-cube text-teal-400"></i> Create Academic Track
            </h3>
            <button type="button" onclick="closeCreateTrackModal()" class="text-slate-400 hover:text-slate-200">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form action="{{ route('principal.classes.tracks.store', $class) }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label for="track_name" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Track Name <span class="text-rose-500">*</span></label>
                <input type="text" name="track_name" id="track_name" placeholder="e.g. FSc Pre-Medical" required class="w-full p-3 rounded-xl text-sm bg-[#0F172A] text-[#F8FAFC] border border-slate-700 outline-none focus:border-teal-500">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="track_code" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Code / Abbr</label>
                    <input type="text" name="track_code" id="track_code" placeholder="FSC-MED" class="w-full p-3 rounded-xl text-sm bg-[#0F172A] text-[#F8FAFC] border border-slate-700 outline-none focus:border-teal-500 uppercase">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Open Electives</label>
                    <label class="flex items-center gap-2 mt-2 cursor-pointer text-xs text-slate-300 font-semibold">
                        <input type="checkbox" name="allow_custom_electives" value="1" class="accent-teal-500 w-4 h-4 rounded">
                        <span>Allow Custom Choices</span>
                    </label>
                </div>
            </div>

            <div>
                <label for="description" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Description</label>
                <textarea name="description" id="description" rows="2" placeholder="Curriculum bundle for medical science aspirants..." class="w-full p-3 rounded-xl text-sm bg-[#0F172A] text-[#F8FAFC] border border-slate-700 outline-none focus:border-teal-500"></textarea>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Select Bundled Elective Subjects <span class="text-rose-500">*</span></label>
                <div class="max-h-48 overflow-y-auto space-y-2 p-2 rounded-xl bg-[#0F172A] border border-slate-700">
                    @forelse($allClassSubjects as $sub)
                        <label class="flex items-center justify-between p-2 rounded-lg hover:bg-slate-800/80 cursor-pointer text-xs">
                            <span class="text-slate-200 font-semibold">{{ $sub->subject_name }}</span>
                            <input type="checkbox" name="subject_ids[]" value="{{ $sub->id }}" class="accent-teal-500 w-4 h-4 rounded">
                        </label>
                    @empty
                        <div class="text-xs text-slate-500 italic p-2">No subjects found in class pool.</div>
                    @endforelse
                </div>
            </div>

            <div class="pt-3 border-t border-slate-700 flex justify-end gap-3">
                <button type="button" onclick="closeCreateTrackModal()" class="px-4 py-2 text-xs font-bold text-slate-400 hover:text-slate-200">
                    Cancel
                </button>
                <button type="submit" class="btn-primary py-2 px-5 text-xs font-bold shadow flex items-center gap-2">
                    <i class="fa-solid fa-plus"></i> Create Track
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openCreateTrackModal() {
        document.getElementById('createTrackModal').classList.remove('hidden');
    }
    function closeCreateTrackModal() {
        document.getElementById('createTrackModal').classList.add('hidden');
    }
</script>
@endsection
