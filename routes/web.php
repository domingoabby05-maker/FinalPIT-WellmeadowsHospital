<?php

use App\Http\Controllers\HospitalController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HospitalController::class, 'index'])->name('hospital.index');
Route::post('/patients', [HospitalController::class, 'storePatient'])->name('patients.store');
Route::post('/staff', [HospitalController::class, 'storeStaff'])->name('staff.store');
Route::post('/appointments', [HospitalController::class, 'storeAppointment'])->name('appointments.store');
Route::post('/medications', [HospitalController::class, 'storeMedication'])->name('medications.store');
