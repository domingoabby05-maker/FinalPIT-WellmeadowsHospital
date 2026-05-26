<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StaffController extends HospitalController
{
    public function storeStaff(Request $request)
    {
        $this->authorizeModule('staff');
        $validated = $this->validateStaff($request);

        DB::transaction(function () use ($validated) {
            if ($this->usesStringKey('staff', 'staff_id')) {
                $staffId = $this->nextStaffNumber();
                DB::table('staff')->insert($this->onlyExistingColumns('staff', $validated['staff'] + ['staff_id' => $staffId]));
            } else {
                $staffId = DB::table('staff')->insertGetId($this->onlyExistingColumns('staff', $validated['staff']));
            }

            $this->saveStaffDetails($staffId, $validated);
        });

        return $this->moduleRedirect('staff', 'Staff record added successfully.');
    }

    public function updateStaff(Request $request, string $staff)
    {
        $this->authorizeModule('staff');
        $validated = $this->validateStaff($request, $staff);

        DB::transaction(function () use ($staff, $validated) {
            DB::table('staff')->where('staff_id', $staff)->update($this->onlyExistingColumns('staff', $validated['staff']));
            $this->saveStaffDetails($staff, $validated, true);
        });

        return $this->moduleRedirect('staff', 'Staff record updated successfully.');
    }

    public function destroyStaff(string $staff)
    {
        $this->authorizeModule('staff');
        DB::table('staff')->where('staff_id', $staff)->delete();

        return $this->moduleRedirect('staff', 'Staff record deleted successfully.');
    }

    public function approveAccountRequest(string $request)
    {
        $this->authorizeModule('staff');

        DB::transaction(function () use ($request) {
            $accountRequest = DB::table('account_requests')
                ->where('request_id', $request)
                ->where('status', 'Pending')
                ->lockForUpdate()
                ->first();

            abort_unless($accountRequest, 404);

            $staffId = $this->nextStaffNumber();
            $staff = [
                'staff_id' => $staffId,
                'first_name' => $accountRequest->first_name,
                'last_name' => $accountRequest->last_name,
                'address' => null,
                'phone' => null,
                'date_of_birth' => null,
                'sex' => null,
                'nin' => 'PENDING-' . $staffId,
                'position' => $accountRequest->role,
                'salary' => null,
                'salary_scale' => null,
            ];

            DB::table('staff')->insert($this->onlyExistingColumns('staff', $staff));

            DB::table('users')->updateOrInsert(
                ['email' => $accountRequest->email],
                [
                    'name' => trim($accountRequest->first_name . ' ' . $accountRequest->last_name),
                    'microsoft_email' => null,
                    'hospital_id' => $staffId,
                    'role' => $accountRequest->role,
                    'password' => $accountRequest->password,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            DB::table('account_requests')->where('request_id', $request)->update([
                'status' => 'Approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return $this->moduleRedirect('staff', 'Account request approved and staff login created.');
    }

    public function rejectAccountRequest(string $request)
    {
        $this->authorizeModule('staff');

        DB::table('account_requests')->where('request_id', $request)->where('status', 'Pending')->update([
            'status' => 'Rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'updated_at' => now(),
        ]);

        return $this->moduleRedirect('staff', 'Account request rejected.');
    }
}

