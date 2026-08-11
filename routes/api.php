<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\StudentAdmissionController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/admissions', [StudentAdmissionController::class, 'store'])->name('admissions.store');
Route::get('/admissions/{student}/invoice', [StudentAdmissionController::class, 'generateInvoicePdf'])->name('admissions.invoice');
