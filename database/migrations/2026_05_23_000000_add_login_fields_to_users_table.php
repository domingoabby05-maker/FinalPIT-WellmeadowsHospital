<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('microsoft_email')->nullable()->unique()->after('email');
            $table->string('hospital_id')->nullable()->unique()->after('microsoft_email');
            $table->string('role')->nullable()->after('hospital_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['microsoft_email']);
            $table->dropUnique(['hospital_id']);
            $table->dropColumn(['microsoft_email', 'hospital_id', 'role']);
        });
    }
};
