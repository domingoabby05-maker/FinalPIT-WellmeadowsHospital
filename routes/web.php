<?php

use App\Http\Controllers\HospitalController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HospitalController::class, 'index'])->name('hospital.index');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/create-account', [AuthController::class, 'register'])->name('register');
Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.forgot');

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard/live', [HospitalController::class, 'dashboardData'])->name('dashboard.live');
    Route::post('/patients', [HospitalController::class, 'storePatient'])->name('patients.store');
    Route::put('/patients/{patient}', [HospitalController::class, 'updatePatient'])->name('patients.update');
    Route::delete('/patients/{patient}', [HospitalController::class, 'destroyPatient'])->name('patients.destroy');
    Route::post('/staff', [HospitalController::class, 'storeStaff'])->name('staff.store');
    Route::put('/staff/{staff}', [HospitalController::class, 'updateStaff'])->name('staff.update');
    Route::delete('/staff/{staff}', [HospitalController::class, 'destroyStaff'])->name('staff.destroy');
    Route::post('/account-requests/{request}/approve', [HospitalController::class, 'approveAccountRequest'])->name('account-requests.approve');
    Route::post('/account-requests/{request}/reject', [HospitalController::class, 'rejectAccountRequest'])->name('account-requests.reject');
    Route::put('/admissions/{admission}', [HospitalController::class, 'updateAdmission'])->name('admissions.update');
    Route::delete('/admissions/{admission}', [HospitalController::class, 'destroyAdmission'])->name('admissions.destroy');
    Route::post('/admissions', [HospitalController::class, 'storeAdmission'])->name('admissions.store');
    Route::post('/appointments', [HospitalController::class, 'storeAppointment'])->name('appointments.store');
    Route::put('/appointments/{appointment}', [HospitalController::class, 'updateAppointment'])->name('appointments.update');
    Route::delete('/appointments/{appointment}', [HospitalController::class, 'destroyAppointment'])->name('appointments.destroy');
    Route::post('/medications', [HospitalController::class, 'storeMedication'])->name('medications.store');
    Route::put('/medications/{medication}', [HospitalController::class, 'updateMedication'])->name('medications.update');
    Route::delete('/medications/{medication}', [HospitalController::class, 'destroyMedication'])->name('medications.destroy');
});
