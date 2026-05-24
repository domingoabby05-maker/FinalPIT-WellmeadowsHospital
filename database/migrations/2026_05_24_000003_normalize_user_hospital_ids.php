<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users') || ! Schema::hasColumn('users', 'hospital_id')) {
            return;
        }

        $users = DB::table('users')
            ->select('id', 'name', 'hospital_id', 'role')
            ->whereNull('hospital_id')
            ->orWhere('hospital_id', 'not like', 'S%')
            ->orderBy('id')
            ->get();

        foreach ($users as $user) {
            $staffId = $this->matchingStaffId($user);

            DB::table('users')->where('id', $user->id)->update([
                'hospital_id' => $staffId ?: $this->nextUserStaffId(),
            ]);
        }
    }

    private function matchingStaffId(object $user): ?string
    {
        if (! Schema::hasTable('staff') || blank($user->name)) {
            return null;
        }

        return DB::table('staff')
            ->where(function ($query) use ($user) {
                $query->whereRaw("lower(first_name) = lower(?)", [$user->name])
                    ->orWhereRaw("lower(first_name || ' ' || last_name) = lower(?)", [$user->name]);
            })
            ->when($user->role, function ($query) use ($user) {
                $query->where(function ($roleQuery) use ($user) {
                    $roleQuery->where('staff_position', $user->role)
                        ->orWhere('position', $user->role);
                });
            })
            ->value('staff_id');
    }

    private function nextUserStaffId(): string
    {
        $lastStaffId = Schema::hasTable('staff')
            ? DB::table('staff')->where('staff_id', 'like', 'S%')->orderByDesc('staff_id')->value('staff_id')
            : null;

        $lastUserId = DB::table('users')->where('hospital_id', 'like', 'S%')->orderByDesc('hospital_id')->value('hospital_id');
        $next = max(
            (int) preg_replace('/\D/', '', (string) $lastStaffId),
            (int) preg_replace('/\D/', '', (string) $lastUserId),
        ) + 1;

        do {
            $hospitalId = 'S' . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
            $next++;
        } while (DB::table('users')->where('hospital_id', $hospitalId)->exists());

        return $hospitalId;
    }

    public function down(): void
    {
        //
    }
};
