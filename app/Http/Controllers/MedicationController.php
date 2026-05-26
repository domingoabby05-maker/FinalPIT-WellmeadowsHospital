<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MedicationController extends HospitalController
{
    public function storeMedication(Request $request)
    {
        $this->authorizeModule('medications');
        $validated = $this->validateMedication($request);

        DB::table('patient_medication')->insert($validated);

        return $this->moduleRedirect('medications', 'Medication prescribed successfully.');
    }

    public function updateMedication(Request $request, string $medication)
    {
        $this->authorizeModule('medications');
        $validated = $this->validateMedication($request);

        DB::table('patient_medication')->where('medication_id', $medication)->update($validated);

        return $this->moduleRedirect('medications', 'Medication updated successfully.');
    }

    public function destroyMedication(string $medication)
    {
        $this->authorizeModule('medications');
        DB::table('patient_medication')->where('medication_id', $medication)->delete();

        return $this->moduleRedirect('medications', 'Medication deleted successfully.');
    }
}

