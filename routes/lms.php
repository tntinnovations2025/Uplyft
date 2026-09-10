<?php

use App\Http\Controllers\Lms\AssessmentController;
use App\Http\Controllers\Lms\ChatbotController;
use App\Http\Controllers\Lms\GradeController;
use App\Http\Controllers\Lms\PracticeTestController;
use App\Http\Controllers\Lms\SubjectMaterialController;
use App\Http\Controllers\Lms\TestResultController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Module 6: LMS, RAG Chatbot, Assessments & Final Grading Routes
|--------------------------------------------------------------------------
|
| This route file is fully decoupled and can be registered in the
| RouteServiceProvider or bootstrap/app.php independently.
|
| All routes require authentication.
|
*/

Route::middleware(['auth'])->prefix('lms')->name('lms.')->group(function () {

    // ── Upload Subjects RAG Listing & Materials ────────────────────────────
    Route::middleware(['feature:lms_content', 'permission:lms_content,view'])->group(function () {
        Route::get('/subjects', [SubjectMaterialController::class, 'subjectList'])->name('subjects.list');
        Route::get('/subjects/class/{classId}', [SubjectMaterialController::class, 'classSubjects'])->name('subjects.class');

        Route::prefix('subjects/{subjectId}/materials')->name('materials.')->group(function () {
            Route::get('/', [SubjectMaterialController::class, 'index'])->name('index');
            Route::post('/', [SubjectMaterialController::class, 'store'])->name('store');
            Route::delete('/{material}', [SubjectMaterialController::class, 'destroy'])->name('destroy');
        });
    });

    // ── Full-Page & Embedded RAG Chatbot ──────────────────────────────────
    Route::middleware(['feature:ai_bot', 'permission:ai_bot,view'])->group(function () {
        Route::post('subjects/{subjectId}/materials/chatbot', [SubjectMaterialController::class, 'askChatbot'])->middleware('throttle:20,1')->name('materials.chatbot');

        Route::prefix('chatbot')->name('chatbot.')->group(function () {
            Route::get('/', [ChatbotController::class, 'index'])->name('index');
            Route::post('/send', [ChatbotController::class, 'sendMessage'])->middleware('throttle:20,1')->name('send');
            Route::match(['get', 'post'], '/stream', [ChatbotController::class, 'streamMessage'])->middleware('throttle:20,1')->name('stream');
            Route::get('/history', [ChatbotController::class, 'getHistory'])->name('history');
            Route::get('/sessions', [ChatbotController::class, 'getSessions'])->name('sessions');
            Route::post('/delete-session', [ChatbotController::class, 'deleteSession'])->name('deleteSession');
        });
    });

    // ── Assessments ──────────────────────────────────────────────────────
    Route::middleware(['feature:assessment_engine', 'permission:assessment_engine,view'])->prefix('assessments')->name('assessments.')->group(function () {
        Route::get('/', [AssessmentController::class, 'index'])->name('index');
        Route::get('/paper-marksheet', [AssessmentController::class, 'paperMarksheetForm'])->name('paperMarksheet.form');
        Route::post('/paper-marksheet', [AssessmentController::class, 'storePaperMarksheet'])->name('paperMarksheet.store');
        Route::post('/', [AssessmentController::class, 'store'])->name('store');
        Route::put('/{assessment}', [AssessmentController::class, 'update'])->name('update');
        Route::delete('/{assessment}', [AssessmentController::class, 'destroy'])->name('destroy');

        // Student: take an assessment
        Route::get('/{assessment}/take', [AssessmentController::class, 'take'])->name('take');

        // Question management & AI test generation
        Route::post('/{assessment}/questions', [AssessmentController::class, 'addQuestions'])->name('questions.add');
        Route::post('/generate-ai', [AssessmentController::class, 'generateAiAssessment'])->name('generateAi');
        Route::post('/practice-quiz', [AssessmentController::class, 'generateStudentPracticeQuiz'])->name('practiceQuiz');

        // Lifecycle actions
        Route::post('/{assessment}/publish', [AssessmentController::class, 'publish'])->name('publish');
        Route::post('/{assessment}/schedule', [AssessmentController::class, 'setSchedule'])->name('schedule');

        // Student submission
        Route::post('/{assessment}/submit', [AssessmentController::class, 'submitAnswers'])->name('submit');

        // Grading
        Route::post('/{assessment}/grade', [AssessmentController::class, 'gradeStudent'])->name('grade');
        Route::post('/{assessment}/auto-grade-mcqs', [AssessmentController::class, 'bulkAutoGradeMcqs'])->name('autoGradeMcqs');
    });

    // ── Practice Tests (Student Self-Testing with AI Evaluation) ──────────
    Route::middleware(['feature:practice_tests', 'permission:ai_bot,view'])->prefix('practice-test')->name('practice-test.')->group(function () {
        Route::get('/', [PracticeTestController::class, 'index'])->name('index');
        Route::post('/generate', [PracticeTestController::class, 'generate'])->middleware('throttle:5,1')->name('generate');
        Route::get('/{id}/take', [PracticeTestController::class, 'take'])->name('take');
        Route::post('/{id}/submit', [PracticeTestController::class, 'submit'])->name('submit');
        Route::get('/{id}/result', [PracticeTestController::class, 'result'])->name('result');
        Route::get('/history/{subjectId}', [PracticeTestController::class, 'history'])->name('history');
    });

    // ── Test Results Folder & Marksheets ─────────────────────────────────
    Route::middleware(['feature:assessment_engine'])->prefix('test-results')->name('test-results.')->group(function () {
        Route::get('/', [TestResultController::class, 'index'])->name('index');
        Route::get('/{assessment}/marksheet', [TestResultController::class, 'showMarksheet'])->name('marksheet');
        Route::post('/{assessment}/save-marksheet', [TestResultController::class, 'saveMarksheet'])->name('saveMarksheet');
        Route::post('/{assessment}/update-student-marks', [TestResultController::class, 'updateStudentMarks'])->name('updateStudentMarks');
    });

    // ── Grade Weightages & Reports ───────────────────────────────────────
    Route::middleware(['feature:grading_normalizer'])->prefix('grades')->name('grades.')->group(function () {
        // Weightage config page
        Route::get('/weightages', [GradeController::class, 'getWeightages'])->name('weightages.get');
        Route::get('/weightages/page', [GradeController::class, 'weightagesPage'])->name('weightages.page');
        Route::post('/weightages', [GradeController::class, 'saveWeightages'])->name('weightages.save');
        Route::post('/weightages/defaults', [GradeController::class, 'applyDefaults'])->name('weightages.defaults');
        Route::post('/subject-config', [GradeController::class, 'updateSubjectConfig'])->name('subjectConfig.update');

        // Report pages
        Route::get('/report', [GradeController::class, 'reportPage'])->name('report.page');
        Route::get('/student-report', [GradeController::class, 'studentReport'])->name('studentReport');
        Route::get('/class-report', [GradeController::class, 'classReport'])->name('classReport');
    });

    // ── Official Class Exam Datesheets ─────────────────────────────────────
    Route::middleware(['feature:datesheet_manager'])->prefix('datesheet')->name('datesheet.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Lms\DatesheetController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\Lms\DatesheetController::class, 'store'])->name('store');
        Route::post('/class-section/{classSectionId}/toggle-visibility', [\App\Http\Controllers\Lms\DatesheetController::class, 'toggleClassVisibility'])->name('toggleVisibility');
        Route::put('/{id}', [\App\Http\Controllers\Lms\DatesheetController::class, 'update'])->name('update');
        Route::delete('/class-section/{classSectionId}', [\App\Http\Controllers\Lms\DatesheetController::class, 'destroyClassSectionDatesheet'])->name('destroyClass');
        Route::delete('/{id}', [\App\Http\Controllers\Lms\DatesheetController::class, 'destroy'])->name('destroy');
    });

    // ── Official Exams Report (Midterms, Finals & Term Exams) ────────────
    Route::middleware(['feature:exam_reports'])->prefix('exam-report')->name('exam-report.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Lms\ExamReportController::class, 'index'])->name('index');
        Route::get('/{assessment}/marksheet', [\App\Http\Controllers\Lms\ExamReportController::class, 'showMarksheet'])->name('marksheet');
        Route::post('/{assessment}/save-marks', [\App\Http\Controllers\Lms\ExamReportController::class, 'storeMarks'])->name('storeMarks');
    });
});
