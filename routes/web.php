<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Dashboard;
use App\Livewire\PatientManagement;
use App\Livewire\MriScanManagement;
use App\Livewire\ReportManagement;
use App\Livewire\UserManagement;
use App\Livewire\ChatManagement;
use App\Http\Controllers\ReportPrintController;

Route::redirect('/', '/dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/patients', PatientManagement::class)->name('patients')->middleware('role:admin,mri_technician');
    Route::get('/scans', MriScanManagement::class)->name('scans');
    Route::get('/reports', ReportManagement::class)->name('reports')->middleware('role:admin,doctor');
    Route::get('/reports/{report}/print', [ReportPrintController::class, 'show'])
        ->name('reports.print')
        ->middleware('role:admin,doctor,mri_technician');
    Route::get('/messages', ChatManagement::class)->name('messages')->middleware('role:admin,doctor,mri_technician');
    Route::get('/users', UserManagement::class)->name('users')->middleware('role:admin');
});

require __DIR__.'/auth.php';