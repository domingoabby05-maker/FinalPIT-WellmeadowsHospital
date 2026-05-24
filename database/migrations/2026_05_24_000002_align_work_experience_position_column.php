<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('work_experience') || Schema::hasColumn('work_experience', 'staff_position')) {
            return;
        }

        Schema::table('work_experience', function (Blueprint $table) {
            $table->string('staff_position', 100)->nullable()->after('staff_id');
        });

        if (Schema::hasColumn('work_experience', 'position')) {
            DB::table('work_experience')->update([
                'staff_position' => DB::raw('position'),
            ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('work_experience') && Schema::hasColumn('work_experience', 'staff_position')) {
            Schema::table('work_experience', function (Blueprint $table) {
                $table->dropColumn('staff_position');
            });
        }
    }
};
