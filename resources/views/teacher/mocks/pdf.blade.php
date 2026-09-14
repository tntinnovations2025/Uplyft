<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $assessment->title }}</title>
    <style>
        @page {
            margin: 18mm 14mm 18mm 14mm;
        }
        body {
            font-family: Helvetica, Arial, sans-serif;
            font-size: 11pt;
            color: #000000;
            line-height: 1.4;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #000000;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .institute-title {
            font-size: 15pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .exam-subtitle {
            font-size: 11pt;
            font-weight: bold;
            color: #333333;
            margin-top: 3px;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .meta-table td {
            border: 1px solid #000000;
            padding: 5px 8px;
            font-size: 9.5pt;
        }
        .meta-label {
            background-color: #F0F0F0;
            font-weight: bold;
        }
        .instructions {
            border: 1px solid #333333;
            background-color: #FAFAFA;
            padding: 8px 12px;
            margin-bottom: 16px;
            font-size: 9pt;
            line-height: 1.35;
        }
        .instructions h4 {
            margin: 0 0 4px 0;
            font-size: 9pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .instructions ul {
            margin: 0;
            padding-left: 16px;
        }
        .instructions li {
            margin-bottom: 2px;
        }
        .question-item {
            margin-bottom: 14px;
            page-break-inside: avoid;
        }
        .q-table {
            width: 100%;
            border-collapse: collapse;
        }
        .q-num {
            width: 28px;
            font-weight: bold;
            vertical-align: top;
            font-size: 10.5pt;
        }
        .q-stem {
            vertical-align: top;
            font-size: 10.5pt;
        }
        .q-mark {
            width: 30px;
            text-align: right;
            vertical-align: top;
            font-size: 9pt;
            font-weight: bold;
        }
        .options-table {
            width: 100%;
            margin-left: 28px;
            margin-top: 4px;
            border-collapse: collapse;
        }
        .opt-letter {
            width: 22px;
            font-weight: bold;
            vertical-align: top;
            font-size: 10pt;
        }
        .opt-text {
            vertical-align: top;
            font-size: 10pt;
            padding-bottom: 3px;
        }
        .opt-correct {
            font-weight: bold;
            color: #1A5928;
        }
        .rationale-box {
            margin-left: 28px;
            margin-top: 4px;
            background-color: #F4F2EB;
            border-left: 3px solid #D48A2E;
            padding: 4px 8px;
            font-size: 8.5pt;
            color: #444444;
        }
        .page-break {
            page-break-before: always;
        }
        .marking-key-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .marking-key-table th, .marking-key-table td {
            border: 1px solid #000000;
            padding: 4px;
            font-size: 9pt;
            text-align: center;
        }
        .marking-key-table th {
            background-color: #EEEEEE;
        }
    </style>
</head>
<body>

    <!-- HEADER -->
    <table class="header-table">
        <tr>
            <td style="vertical-align: top;">
                <div class="institute-title">
                    {{ $assessment->subject?->instituteClass?->institute?->name ?? 'ACADEMIC INSTITUTION' }}
                </div>
                <div class="exam-subtitle">
                    {{ $assessment->subject?->name ?? 'Subject' }} ({{ $assessment->subject?->subject_code ?? 'CAIE' }}) · {{ $assessment->title }}
                </div>
                <div style="font-size: 9pt; color: #555555; margin-top: 2px;">
                    {{ $assessment->classSection?->instituteClass?->name ?? 'Class' }} — Section {{ $assessment->classSection?->name ?? 'All' }} · {{ $assessment->academicTerm?->name ?? 'Academic Term' }}
                </div>
            </td>
            <td style="vertical-align: top; text-align: right; width: 200px;">
                <div style="border: 1px solid #000; padding: 2px 6px; font-weight: bold; font-size: 9pt; display: inline-block;">
                    {{ strtoupper(str_replace('_', ' ', $assessment->exam_standard ?? 'CAIE O LEVEL')) }}
                </div>
                <div style="font-size: 9.5pt; font-weight: bold; margin-top: 4px;">Paper 1 Multiple Choice</div>
                <div style="font-size: 9pt; color: #444444;">Time: {{ $assessment->duration_minutes ?? 45 }} Minutes</div>
            </td>
        </tr>
    </table>

    <!-- CANDIDATE ENTRY BLOCK -->
    <table class="meta-table">
        <tr>
            <td class="meta-label" style="width: 20%;">Candidate Name:</td>
            <td style="width: 40%;"></td>
            <td class="meta-label" style="width: 20%;">Center Number:</td>
            <td style="width: 20%; text-align: center; font-weight: bold; font-family: monospace;">PK 0 0 1</td>
        </tr>
        <tr>
            <td class="meta-label">Roll / Seat No:</td>
            <td></td>
            <td class="meta-label">Candidate No:</td>
            <td style="text-align: center; font-weight: bold; font-family: monospace;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
        </tr>
    </table>

    <!-- INSTRUCTIONS -->
    <div class="instructions">
        <h4>READ THESE INSTRUCTIONS FIRST</h4>
        <ul>
            <li>Write your name, Centre number and candidate number in the spaces provided at the top of this page.</li>
            <li>Write in soft pencil. Do not use staples, paper clips, highlighters, glue or correction fluid.</li>
            <li>There are <strong>{{ $assessment->questions->count() }}</strong> questions on this paper. Answer <strong>all</strong> questions.</li>
            <li>For each question there are <strong>{{ $assessment->options_per_mcq ?? 4 }}</strong> possible answers A, B, C and D. Choose the one you consider correct.</li>
            <li>Each correct answer will score one mark. A mark will not be deducted for a wrong answer.</li>
        </ul>
    </div>

    <!-- QUESTIONS -->
    @foreach($assessment->questions as $index => $q)
        @php
            $opts = is_array($q->options) ? $q->options : json_decode($q->options ?? '{}', true);
            $correctKey = strtoupper(trim($q->correct_answer));
        @endphp
        <div class="question-item">
            <table class="q-table">
                <tr>
                    <td class="q-num">{{ $index + 1 }}</td>
                    <td class="q-stem">{{ $q->statement }}</td>
                    <td class="q-mark">[1]</td>
                </tr>
            </table>

            <table class="options-table">
                @foreach($opts as $letter => $val)
                    @php
                        $isCorrect = ($withAnswers && strtoupper(trim($letter)) === $correctKey);
                    @endphp
                    <tr>
                        <td class="opt-letter {{ $isCorrect ? 'opt-correct' : '' }}">
                            {{ $letter }}
                        </td>
                        <td class="opt-text {{ $isCorrect ? 'opt-correct' : '' }}">
                            {{ $val }}
                            @if($isCorrect)
                                &nbsp;<strong>[✓ Correct Key]</strong>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </table>

            @if($withAnswers && $q->explanation)
                <div class="rationale-box">
                    <strong>Topic / Citation:</strong> {{ $q->chapter_reference ?: 'Cambridge Syllabus' }}<br/>
                    <strong>Rationale:</strong> {{ $q->explanation }}
                </div>
            @endif
        </div>
    @endforeach

    @if($withAnswers)
        <div class="page-break"></div>
        <div style="text-align: center; margin-bottom: 12px;">
            <h3 style="margin: 0; text-transform: uppercase;">Official Marking Scheme &amp; Answer Key</h3>
            <p style="margin: 2px 0 0; font-size: 9pt; color: #555555;">
                {{ $assessment->title }} · Total Questions: {{ $assessment->questions->count() }}
            </p>
        </div>

        @php
            $chunks = $assessment->questions->chunk(10);
        @endphp
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                @foreach($chunks as $chunk)
                    <td style="vertical-align: top; padding: 4px; width: {{ 100 / count($chunks) }}%;">
                        <table class="marking-key-table">
                            <thead>
                                <tr>
                                    <th>Q#</th>
                                    <th>Key</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($chunk as $cq)
                                    <tr>
                                        <td><strong>Q{{ $cq->sort_order }}</strong></td>
                                        <td style="font-weight: bold; color: #1A5928;">{{ strtoupper($cq->correct_answer) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </td>
                @endforeach
            </tr>
        </table>
    @endif

</body>
</html>
