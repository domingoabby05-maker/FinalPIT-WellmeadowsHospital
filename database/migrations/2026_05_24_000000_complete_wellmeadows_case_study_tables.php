<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('appointments')) {
            Schema::table('appointments', function (Blueprint $table) {
                if (! Schema::hasColumn('appointments', 'recommendation')) {
                    $table->string('recommendation', 30)->nullable()->after('room');
                }
            });
        }

        if (Schema::hasTable('patient_medication')) {
            Schema::table('patient_medication', function (Blueprint $table) {
                if (! Schema::hasColumn('patient_medication', 'method_of_admin')) {
                    $table->string('method_of_admin', 80)->nullable()->after('units_per_day');
                }
            });
        }

        if (! Schema::hasTable('staff_rotas')) {
            $staffIdType = $this->columnType('staff', 'staff_id');
            $wardIdType = $this->columnType('wards', 'ward_id');

            Schema::create('staff_rotas', function (Blueprint $table) use ($staffIdType, $wardIdType) {
                $table->id('rota_id');
                $this->idColumn($table, 'staff_id', $staffIdType);
                $this->idColumn($table, 'ward_id', $wardIdType, true);
                $table->date('week_start_date');
                $table->string('shift', 20);
                $table->string('duty_area', 100)->nullable();
                $table->unique(['staff_id', 'week_start_date', 'shift']);
            });
        }
    }

    private function columnType(string $table, string $column): ?string
    {
        if (! Schema::hasColumn($table, $column)) {
            return null;
        }

        if (DB::getDriverName() === 'pgsql') {
            $result = DB::selectOne(
                'select data_type from information_schema.columns where table_schema = current_schema() and table_name = ? and column_name = ?',
                [$table, $column]
            );

            return $result->data_type ?? null;
        }

        return Schema::getColumnType($table, $column);
    }

    private function idColumn(Blueprint $table, string $column, ?string $type, bool $nullable = false): void
    {
        $definition = str_contains((string) $type, 'char')
            ? $table->string($column, 50)
            : $table->unsignedBigInteger($column);

        if ($nullable) {
            $definition->nullable();
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_rotas');

        if (Schema::hasTable('patient_medication') && Schema::hasColumn('patient_medication', 'method_of_admin')) {
            Schema::table('patient_medication', function (Blueprint $table) {
                $table->dropColumn('method_of_admin');
            });
        }

        if (Schema::hasTable('appointments') && Schema::hasColumn('appointments', 'recommendation')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->dropColumn('recommendation');
            });
        }
    }
};
