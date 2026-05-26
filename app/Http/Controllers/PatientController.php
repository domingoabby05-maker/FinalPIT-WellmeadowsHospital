<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PatientController extends HospitalController
{
    public function storePatient(Request $request)
    {
        $this->authorizeModule('patients');
        $validated = $this->validatePatient($request);

        DB::transaction(function () use ($validated) {
            $patient = $validated['patient'];
            $patient['date_registered'] = now()->toDateString();

            if ($this->usesStringKey('patients', 'patient_id')) {
                $patientId = $this->nextPrefixedId('patients', 'patient_id', 'P', 5);
                DB::table('patients')->insert($this->onlyExistingColumns('patients', $patient + ['patient_id' => $patientId]));
            } else {
                $patientId = DB::table('patients')->insertGetId($this->onlyExistingColumns('patients', $patient));
            }

            $this->savePatientDetails($patientId, $validated);
        });

        return $this->moduleRedirect('patients', 'Patient registered successfully.');
    }

    public function updatePatient(Request $request, string $patient)
    {
        $this->authorizeModule('patients');
        $validated = $this->validatePatient($request);

        DB::transaction(function () use ($patient, $validated) {
            DB::table('patients')->where('patient_id', $patient)->update($this->onlyExistingColumns('patients', $validated['patient']));
            $this->savePatientDetails($patient, $validated, true);
        });

        return $this->moduleRedirect('patients', 'Patient record updated successfully.');
    }

    public function destroyPatient(string $patient)
    {
        $this->authorizeModule('patients');
        DB::table('patients')->where('patient_id', $patient)->delete();

        return $this->moduleRedirect('patients', 'Patient record deleted successfully.');
    }
}

