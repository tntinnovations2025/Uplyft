<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $assessment->title }} - {{ $withAnswers ? 'Marking Scheme' : 'Official Examination Paper' }}</title>
    
    <!-- Google Fonts: Manrope & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

    <style>
        :root {
            --primary: #1B1A17;
            --muted: #55534E;
            --border: #D1CFCA;
            --bg-page: #F4F2EB;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            margin: 0;
            padding: 0;
            background: var(--bg-page);
            color: #000000;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            font-size: 13px;
            line-height: 1.5;
        }

        /* ── Non-printing Control Toolbar ── */
        .no-print-toolbar {
            position: sticky;
            top: 0;
            z-index: 1000;
            background: #1B1A17;
            color: #FFFFFF;
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 14px rgba(0,0,0,0.25);
            font-family: 'Inter', sans-serif;
        }

        .toolbar-group {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn-toolbar {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            border: 1px solid transparent;
            transition: all 0.15s ease;
        }

        .btn-toolbar-primary {
            background: #D48A2E;
            color: #FFFFFF;
        }
        .btn-toolbar-primary:hover {
            background: #C07A22;
        }

        .btn-toolbar-outline {
            background: rgba(255,255,255,0.08);
            border-color: rgba(255,255,255,0.2);
            color: #FFFFFF;
        }
        .btn-toolbar-outline:hover {
            background: rgba(255,255,255,0.18);
            border-color: #FFFFFF;
        }

        .btn-toolbar-active {
            background: #FFFFFF;
            color: #1B1A17;
            border-color: #FFFFFF;
        }

        /* ── Printable Paper Container ── */
        .paper-container {
            max-width: 860px;
            margin: 24px auto 48px auto;
            background: #FFFFFF;
            padding: 40px 48px;
            border: 1px solid #E1DFD7;
            box-shadow: 0 4px 24px rgba(0,0,0,0.06);
            border-radius: 8px;
        }

        /* ── Cambridge Examination Header Block ── */
        .exam-header {
            border-bottom: 2px solid #000000;
            padding-bottom: 16px;
            margin-bottom: 20px;
        }

        .exam-title-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            margin-bottom: 14px;
        }

        .institute-name {
            font-family: 'Manrope', sans-serif;
            font-size: 18px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin: 0;
            color: #000000;
        }

        .syllabus-label {
            font-family: 'Manrope', sans-serif;
            font-size: 13px;
            font-weight: 700;
            margin-top: 3px;
            color: #333333;
        }

        .exam-standard-pill {
            display: inline-block;
            border: 1.5px solid #000000;
            font-family: 'Manrope', sans-serif;
            font-size: 11px;
            font-weight: 800;
            padding: 3px 10px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        /* ── Candidate Entry Table ── */
        .candidate-meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }

        .candidate-meta-table td {
            border: 1px solid #000000;
            padding: 6px 10px;
            font-size: 12px;
            vertical-align: middle;
        }

        .candidate-box-cell {
            letter-spacing: 12px;
            font-weight: bold;
            font-family: monospace;
            text-align: center;
        }

        /* ── Instructions Box ── */
        .instructions-box {
            border: 1px solid #333333;
            background: #FAFAFA;
            padding: 12px 16px;
            margin-bottom: 24px;
            font-size: 11.5px;
            line-height: 1.45;
        }

        .instructions-box h4 {
            margin: 0 0 6px 0;
            font-size: 11.5px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .instructions-box ul {
            margin: 0;
            padding-left: 18px;
        }

        .instructions-box li {
            margin-bottom: 3px;
        }

        /* ── Question Styling ── */
        .question-block {
            margin-bottom: 22px;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .question-header {
            display: flex;
            align-items: baseline;
            gap: 8px;
            margin-bottom: 8px;
        }

        .q-number {
            font-family: 'Manrope', sans-serif;
            font-size: 13.5px;
            font-weight: 800;
            min-width: 24px;
        }

        .q-statement {
            font-size: 13px;
            font-weight: 500;
            color: #000000;
            line-height: 1.5;
            flex: 1;
        }

        .q-mark {
            font-size: 11px;
            font-weight: 700;
            color: #444444;
            white-space: nowrap;
        }

        .options-list {
            margin-left: 32px;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .option-row {
            display: flex;
            align-items: baseline;
            gap: 10px;
            font-size: 12.5px;
        }

        .option-letter {
            font-family: 'Manrope', sans-serif;
            font-weight: 800;
            min-width: 20px;
        }

        .option-text {
            flex: 1;
        }

        .option-correct-badge {
            background: #E3EFE2;
            border: 1px solid #2E6E42;
            color: #2E6E42;
            font-weight: 800;
            font-size: 11px;
            padding: 1px 6px;
            border-radius: 4px;
        }

        .examiner-rationale {
            margin-top: 6px;
            margin-left: 32px;
            background: #F4F2EB;
            border-left: 3px solid #D48A2E;
            padding: 6px 10px;
            font-size: 11px;
            color: #444444;
        }

        /* ── Marking Scheme Summary Table ── */
        .marking-scheme-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
            page-break-before: always;
        }

        .marking-scheme-table th, .marking-scheme-table td {
            border: 1px solid #333333;
            padding: 6px 8px;
            font-size: 11.5px;
            text-align: center;
        }

        .marking-scheme-table th {
            background: #EAEAEA;
            font-weight: 800;
        }

        /* ── Print Media Queries ── */
        @media print {
            body {
                background: #FFFFFF !important;
                color: #000000 !important;
            }
            .no-print-toolbar {
                display: none !important;
            }
            .paper-container {
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                border: none !important;
                box-shadow: none !important;
            }
            .instructions-box {
                background: #FFFFFF !important;
            }
            @page {
                size: A4 portrait;
                margin: 15mm 12mm 15mm 12mm;
            }
            .page-break-after {
                page-break-after: always;
            }
        }
    </style>
</head>
<body>

    <!-- NON-PRINTING TOOLBAR -->
    <div class="no-print-toolbar">
        <div class="toolbar-group">
            <a href="{{ route('teacher.mocks.show', $assessment->id) }}" class="btn-toolbar btn-toolbar-outline">
                <i class="fa-solid fa-arrow-left"></i> Back to Review
            </a>
            <span style="font-size:13px;font-weight:700;color:#E1DFD7">
                {{ $assessment->title }}
            </span>
            <span style="font-size:11px;background:#333;padding:3px 8px;border-radius:6px;color:#D48A2E;font-weight:bold">
                {{ $withAnswers ? 'MARKING SCHEME' : 'EXAM QUESTION PAPER' }}
            </span>
        </div>

        <div class="toolbar-group">
            <a href="?with_answers={{ $withAnswers ? '0' : '1' }}" class="btn-toolbar btn-toolbar-outline">
                <i class="fa-solid fa-{{ $withAnswers ? 'file-lines' : 'key' }}"></i>
                <span>{{ $withAnswers ? 'Switch to Question Paper Only' : 'Include Marking Scheme & Answers' }}</span>
            </a>
            <a href="{{ route('teacher.mocks.pdf', [$assessment->id, 'with_answers' => $withAnswers ? 1 : 0]) }}" class="btn-toolbar btn-toolbar-outline">
                <i class="fa-solid fa-file-pdf text-[#D48A2E]"></i> Download as PDF
            </a>
            <button type="button" onclick="window.print()" class="btn-toolbar btn-toolbar-primary">
                <i class="fa-solid fa-print"></i> Print Examination Paper
            </button>
        </div>
    </div>

    <!-- MAIN EXAM PAPER -->
    <div class="paper-container">

        <!-- HEADER -->
        <div class="exam-header">
            <div class="exam-title-row">
                <div>
                    <h1 class="institute-name">
                        {{ $assessment->subject?->instituteClass?->institute?->name ?? 'ACADEMIC INSTITUTION' }}
                    </h1>
                    <div class="syllabus-label">
                        {{ $assessment->subject?->name ?? 'Subject' }} ({{ $assessment->subject?->subject_code ?? '5054' }}) · {{ $assessment->title }}
                    </div>
                    <div style="font-size:12px;color:#555;margin-top:2px">
                        {{ $assessment->classSection?->instituteClass?->name ?? 'Class' }} — Section {{ $assessment->classSection?->name ?? 'All' }} · {{ $assessment->academicTerm?->name ?? 'Academic Session' }}
                    </div>
                </div>

                <div style="text-align:right">
                    <span class="exam-standard-pill">
                        {{ strtoupper(str_replace('_', ' ', $assessment->exam_standard ?? 'CAIE O LEVEL')) }}
                    </span>
                    <div style="font-size:11.5px;font-weight:bold;margin-top:4px;color:#222">
                        Paper 1 Multiple Choice
                    </div>
                    <div style="font-size:11.5px;color:#555">
                        Time: {{ $assessment->duration_minutes ?? 45 }} Minutes
                    </div>
                </div>
            </div>

            <!-- CANDIDATE ENTRY BLOCK -->
            <table class="candidate-meta-table">
                <tr>
                    <td style="width:18%;font-weight:bold;background:#FAFAFA">Candidate Name:</td>
                    <td style="width:42%"></td>
                    <td style="width:18%;font-weight:bold;background:#FAFAFA">Center Number:</td>
                    <td style="width:22%;text-align:center" class="candidate-box-cell">PK&nbsp;0&nbsp;0&nbsp;1</td>
                </tr>
                <tr>
                    <td style="font-weight:bold;background:#FAFAFA">Roll / Seat No:</td>
                    <td></td>
                    <td style="font-weight:bold;background:#FAFAFA">Candidate No:</td>
                    <td style="text-align:center" class="candidate-box-cell">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                </tr>
            </table>

            <div style="display:flex;justify-content:space-between;font-size:11px;color:#555">
                <span><strong>Additional Materials:</strong> Multiple Choice Answer Sheet · Soft clean eraser · Soft pencil (type B or HB is recommended)</span>
                <span><strong>Total Marks:</strong> {{ $assessment->questions->count() }} Marks</span>
            </div>
        </div>

        <!-- INSTRUCTIONS TO CANDIDATES -->
        <div class="instructions-box">
            <h4>READ THESE INSTRUCTIONS FIRST</h4>
            <ul>
                <li>Write your name, Centre number and candidate number in the spaces provided at the top of this page.</li>
                <li>Write in soft pencil. Do not use staples, paper clips, highlighters, glue or correction fluid.</li>
                <li>There are <strong>{{ $assessment->questions->count() }}</strong> questions on this paper. Answer <strong>all</strong> questions.</li>
                <li>For each question there are <strong>{{ $assessment->options_per_mcq ?? 4 }}</strong> possible answers A, B, C and D. Choose the one you consider correct and mark your choice clearly.</li>
                <li>Each correct answer will score one mark. A mark will not be deducted for a wrong answer.</li>
                <li>Any rough working should be done on paper. Calculators may be used where appropriate.</li>
            </ul>
        </div>

        <!-- QUESTIONS LIST -->
        <div class="questions-container">
            @foreach($assessment->questions as $index => $q)
                @php
                    $opts = is_array($q->options) ? $q->options : json_decode($q->options ?? '{}', true);
                    $correctKey = strtoupper(trim($q->correct_answer));
                @endphp
                <div class="question-block">
                    <div class="question-header">
                        <span class="q-number">{{ $index + 1 }}</span>
                        <div class="q-statement">
                            {{ $q->statement }}
                        </div>
                        <span class="q-mark">[1]</span>
                    </div>

                    <div class="options-list">
                        @foreach($opts as $letter => $val)
                            @php
                                $isCorrect = ($withAnswers && strtoupper(trim($letter)) === $correctKey);
                            @endphp
                            <div class="option-row">
                                <span class="option-letter" style="{{ $isCorrect ? 'color:#2E6E42;font-weight:900' : '' }}">
                                    <strong>{{ $letter }}</strong>
                                </span>
                                <span class="option-text" style="{{ $isCorrect ? 'font-weight:700;color:#2E6E42' : '' }}">
                                    {{ $val }}
                                </span>
                                @if($isCorrect)
                                    <span class="option-correct-badge">✓ Correct Key</span>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    @if($withAnswers && $q->explanation)
                        <div class="examiner-rationale">
                            <strong>Chief Examiner Citation / Topic:</strong> {{ $q->chapter_reference ?: 'Core Syllabus Concept' }}<br>
                            <strong>Rationale:</strong> {{ $q->explanation }}
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <!-- OPTIONAL SUMMARY MARKING SCHEME GRID AT END (IF WITH_ANSWERS) -->
        @if($withAnswers)
            <div style="margin-top:40px;padding-top:20px;border-top:2px solid #000000;page-break-before:always">
                <h3 style="font-family:'Manrope',sans-serif;font-size:16px;font-weight:800;margin:0 0 10px 0;text-align:center;text-transform:uppercase">
                    Official Cambridge Marking Scheme &amp; Key Answer Grid
                </h3>
                <p style="text-align:center;font-size:11.5px;color:#555;margin:0 0 16px 0">
                    {{ $assessment->title }} · {{ $assessment->subject?->name }} ({{ $assessment->subject?->subject_code }})
                </p>

                @php
                    $chunks = $assessment->questions->chunk(10);
                @endphp
                <div style="display:grid;grid-template-columns:repeat({{ min(4, $chunks->count()) }}, 1fr);gap:14px">
                    @foreach($chunks as $chunk)
                        <table style="width:100%;border-collapse:collapse;border:1px solid #000000;font-size:11.5px">
                            <thead>
                                <tr style="background:#F2EFEB">
                                    <th style="border:1px solid #000;padding:4px;text-align:center">Q#</th>
                                    <th style="border:1px solid #000;padding:4px;text-align:center">Key</th>
                                    <th style="border:1px solid #000;padding:4px;text-align:center">Topic</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($chunk as $cq)
                                    <tr>
                                        <td style="border:1px solid #000;padding:4px;text-align:center;font-weight:bold">Q{{ $cq->sort_order }}</td>
                                        <td style="border:1px solid #000;padding:4px;text-align:center;font-weight:900;color:#2E6E42">{{ strtoupper($cq->correct_answer) }}</td>
                                        <td style="border:1px solid #000;padding:4px;font-size:10px">{{ Str::limit($cq->chapter_reference ?: 'Syllabus', 18) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endforeach
                </div>
            </div>
        @endif

    </div>

</body>
</html>
