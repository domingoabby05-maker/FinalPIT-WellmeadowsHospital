<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AppointmentController extends HospitalController
{
    public function storeAppointment(Request $request)
    {
        $this->authorizeModule('appointments');
        $validated = $this->validateAppointment($request);
        $appointment = $this->appointmentColumns($validated);

        DB::transaction(function () use ($validated, $appointment) {
            if ($this->usesStringKey('appointments', 'appointment_id')) {
                $appointmentId = $this->nextPrefixedId('appointments', 'appointment_id', 'AP', 5, $validated['patient_id'] ?? null);
                DB::table('appointments')->insert($appointment + ['appointment_id' => $appointmentId]);
            } else {
                $appointmentId = DB::table('appointments')->insertGetId($appointment, 'appointment_id');
            }

            $this->syncAppointmentRecommendation($appointmentId, $validated);
        });

        return $this->moduleRedirect('appointments', 'Appointment scheduled successfully.');
    }

    public function updateAppointment(Request $request, string $appointment)
    {
        $this->authorizeModule('appointments');
        $validated = $this->validateAppointment($request);
        $appointmentData = $this->appointmentColumns($validated);

        DB::transaction(function () use ($appointment, $validated, $appointmentData) {
            DB::table('appointments')->where('appointment_id', $appointment)->update($appointmentData);
            $this->syncAppointmentRecommendation($appointment, $validated);
        });

        return $this->moduleRedirect('appointments', 'Appointment updated successfully.');
    }

    public function destroyAppointment(string $appointment)
    {
        $this->authorizeModule('appointments');
        DB::table('appointments')->where('appointment_id', $appointment)->delete();

        return $this->moduleRedirect('appointments', 'Appointment deleted successfully.');
    }
}

