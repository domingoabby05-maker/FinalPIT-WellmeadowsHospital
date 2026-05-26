<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdmissionController extends HospitalController
{
    public function storeAdmission(Request $request)
    {
        $this->authorizeModule('admissions');
        $validated = $this->validateAdmission($request);

        DB::transaction(function () use ($validated) {
            if ($this->usesStringKey('admissions', 'admission_id')) {
                $admissionId = $this->nextPrefixedId('admissions', 'admission_id', 'A', 5, $validated['patient_id'] ?? null);
                DB::table('admissions')->insert($validated + ['admission_id' => $admissionId]);
            } else {
                DB::table('admissions')->insert($validated);
            }

            if (! empty($validated['bed_id'])) {
                $this->syncBedStatus($validated['bed_id']);
            }
        });

        return $this->moduleRedirect('wards', 'Ward admission recorded successfully.');
    }

    public function updateAdmission(Request $request, string $admission)
    {
        $this->authorizeModule('admissions');
        $validated = $this->validateAdmission($request);

        DB::transaction(function () use ($admission, $validated) {
            $previousBedId = DB::table('admissions')->where('admission_id', $admission)->value('bed_id');
            DB::table('admissions')->where('admission_id', $admission)->update($validated);

            if ($previousBedId) {
                $this->syncBedStatus($previousBedId);
            }

            if (! empty($validated['bed_id'])) {
                $this->syncBedStatus($validated['bed_id']);
            }
        });

        return $this->moduleRedirect('wards', 'Ward admission updated successfully.');
    }

    public function destroyAdmission(string $admission)
    {
        $this->authorizeModule('admissions');
        $bedId = DB::table('admissions')->where('admission_id', $admission)->value('bed_id');

        DB::transaction(function () use ($admission, $bedId) {
            DB::table('admissions')->where('admission_id', $admission)->delete();

            if ($bedId) {
                $this->syncBedStatus($bedId);
            }
        });

        return $this->moduleRedirect('wards', 'Ward admission deleted successfully.');
    }
}

