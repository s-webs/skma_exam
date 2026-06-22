<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['exam_types', 'exams'] as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->string('name_ru')->nullable();
                $blueprint->string('name_kk')->nullable();
                $blueprint->string('name_en')->nullable();
            });

            DB::table($table)->update(['name_ru' => DB::raw('name')]);

            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropColumn('name');
            });
        }
    }

    public function down(): void
    {
        foreach (['exam_types', 'exams'] as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->string('name')->nullable();
            });

            DB::table($table)->update(['name' => DB::raw('name_ru')]);

            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropColumn(['name_ru', 'name_kk', 'name_en']);
            });
        }
    }
};
