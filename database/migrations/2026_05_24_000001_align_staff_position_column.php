<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('staff') || Schema::hasColumn('staff', 'staff_position')) {
            return;
        }

        Schema::table('staff', function (Blueprint $table) {
            $table->string('staff_position', 100)->nullable()->after('nin');
        });

        if (Schema::hasColumn('staff', 'position')) {
            DB::table('staff')->update([
                'staff_position' => DB::raw('position'),
            ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('staff') && Schema::hasColumn('staff', 'staff_position')) {
            Schema::table('staff', function (Blueprint $table) {
                $table->dropColumn('staff_position');
            });
        }
    }
};
