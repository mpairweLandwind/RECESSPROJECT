<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileUploadController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ChallengeController;
use App\Http\Controllers\ReportController;


Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard/{section?}', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/analytics', [ChallengeController::class, 'analytics'])->name('analytics');
    Route::post('/upload-questions', [FileUploadController::class, 'uploadQuestions'])->name('uploadQuestions');
    Route::post('/upload-answers', [FileUploadController::class, 'uploadAnswers'])->name('uploadAnswers');
    Route::post('/set-challenge', [ChallengeController::class, 'setChallenge'])->name('setChallenge');
    Route::post('/upload-schools', [FileUploadController::class, 'uploadSchools'])->name('upload.schools');
    Route::get('/sendemail/participant/pdf/{participant_id}', [ReportController::class, 'sendParticipantPdf'])->name('sendemail.participant.pdf');
    Route::get('/generateprint/participant/pdf/{participant_id}', [ReportController::class, 'generateParticipantPdf'])->name('generateprint.participant.pdf');
    Route::get('/sendemail/school/pdf/{school_id}', [ReportController::class, 'sendSchoolPdf'])->name('sendemail.school.pdf');
    Route::get('/generateprint/school/pdf/{school_id}', [ReportController::class, 'generateSchoolPdf'])->name('generateprint.school.pdf');



});


