<?php

use App\Http\Controllers\HospitalController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdmissionController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\MedicationController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\StaffController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HospitalController::class, 'index'])->name('hospital.index');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/create-account', [AuthController::class, 'register'])->name('register');
Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.forgot');

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard/live', [HospitalController::class, 'dashboardData'])->name('dashboard.live');
    Route::post('/patients', [PatientController::class, 'storePatient'])->name('patients.store');
    Route::put('/patients/{patient}', [PatientController::class, 'updatePatient'])->name('patients.update');
    Route::delete('/patients/{patient}', [PatientController::class, 'destroyPatient'])->name('patients.destroy');
    Route::post('/staff', [StaffController::class, 'storeStaff'])->name('staff.store');
    Route::put('/staff/{staff}', [StaffController::class, 'updateStaff'])->name('staff.update');
    Route::delete('/staff/{staff}', [StaffController::class, 'destroyStaff'])->name('staff.destroy');
    Route::post('/account-requests/{request}/approve', [StaffController::class, 'approveAccountRequest'])->name('account-requests.approve');
    Route::post('/account-requests/{request}/reject', [StaffController::class, 'rejectAccountRequest'])->name('account-requests.reject');
    Route::put('/admissions/{admission}', [AdmissionController::class, 'updateAdmission'])->name('admissions.update');
    Route::delete('/admissions/{admission}', [AdmissionController::class, 'destroyAdmission'])->name('admissions.destroy');
    Route::post('/admissions', [AdmissionController::class, 'storeAdmission'])->name('admissions.store');
    Route::post('/appointments', [AppointmentController::class, 'storeAppointment'])->name('appointments.store');
    Route::put('/appointments/{appointment}', [AppointmentController::class, 'updateAppointment'])->name('appointments.update');
    Route::delete('/appointments/{appointment}', [AppointmentController::class, 'destroyAppointment'])->name('appointments.destroy');
    Route::post('/medications', [MedicationController::class, 'storeMedication'])->name('medications.store');
    Route::put('/medications/{medication}', [MedicationController::class, 'updateMedication'])->name('medications.update');
    Route::delete('/medications/{medication}', [MedicationController::class, 'destroyMedication'])->name('medications.destroy');
});
