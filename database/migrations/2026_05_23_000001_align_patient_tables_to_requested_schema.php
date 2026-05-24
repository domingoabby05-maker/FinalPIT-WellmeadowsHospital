<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        if (Schema::hasTable('patients')) {
            DB::statement('ALTER TABLE patients ALTER COLUMN first_name TYPE VARCHAR(50)');
            DB::statement('ALTER TABLE patients ALTER COLUMN last_name TYPE VARCHAR(50)');
            DB::statement('ALTER TABLE patients ALTER COLUMN address TYPE TEXT');
            DB::statement('ALTER TABLE patients ALTER COLUMN phone TYPE VARCHAR(20)');
            DB::statement('ALTER TABLE patients ALTER COLUMN marital_status TYPE VARCHAR(20)');
        }

        if (Schema::hasTable('next_of_kin')) {
            DB::statement('ALTER TABLE next_of_kin ALTER COLUMN full_name TYPE VARCHAR(100)');
            DB::statement('ALTER TABLE next_of_kin ALTER COLUMN relationship TYPE VARCHAR(50)');
            DB::statement('ALTER TABLE next_of_kin ALTER COLUMN address TYPE TEXT');
            DB::statement('ALTER TABLE next_of_kin ALTER COLUMN phone TYPE VARCHAR(20)');
        }

        if (Schema::hasTable('local_doctors')) {
            DB::statement('ALTER TABLE local_doctors ALTER COLUMN full_name TYPE VARCHAR(100)');
            DB::statement('ALTER TABLE local_doctors ALTER COLUMN clinic_number TYPE VARCHAR(50)');
            DB::statement('ALTER TABLE local_doctors ALTER COLUMN address TYPE TEXT');
            DB::statement('ALTER TABLE local_doctors ALTER COLUMN phone TYPE VARCHAR(20)');
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        if (Schema::hasTable('patients')) {
            DB::statement('ALTER TABLE patients ALTER COLUMN first_name TYPE VARCHAR(100)');
            DB::statement('ALTER TABLE patients ALTER COLUMN last_name TYPE VARCHAR(100)');
            DB::statement('ALTER TABLE patients ALTER COLUMN address TYPE VARCHAR(255)');
            DB::statement('ALTER TABLE patients ALTER COLUMN phone TYPE VARCHAR(30)');
            DB::statement('ALTER TABLE patients ALTER COLUMN marital_status TYPE VARCHAR(30)');
        }

        if (Schema::hasTable('next_of_kin')) {
            DB::statement('ALTER TABLE next_of_kin ALTER COLUMN relationship TYPE VARCHAR(80)');
            DB::statement('ALTER TABLE next_of_kin ALTER COLUMN address TYPE VARCHAR(255)');
            DB::statement('ALTER TABLE next_of_kin ALTER COLUMN phone TYPE VARCHAR(30)');
        }

        if (Schema::hasTable('local_doctors')) {
            DB::statement('ALTER TABLE local_doctors ALTER COLUMN clinic_number TYPE VARCHAR(255)');
            DB::statement('ALTER TABLE local_doctors ALTER COLUMN address TYPE VARCHAR(255)');
            DB::statement('ALTER TABLE local_doctors ALTER COLUMN phone TYPE VARCHAR(30)');
        }
    }
};
