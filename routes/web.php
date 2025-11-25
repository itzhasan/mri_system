<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Dashboard;
use App\Livewire\PatientManagement;
use App\Livewire\MriScanManagement;
use App\Livewire\ReportManagement;

Route::redirect('/', '/dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/patients', PatientManagement::class)->name('patients')->middleware('role:admin,mri_technician');
    Route::get('/scans', MriScanManagement::class)->name('scans');
    Route::get('/reports', ReportManagement::class)->name('reports')->middleware('role:admin,doctor');
});

require __DIR__.'/auth.php';