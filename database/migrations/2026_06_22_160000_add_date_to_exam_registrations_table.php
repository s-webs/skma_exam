<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_registrations', function (Blueprint $table) {
            $table->date('date')->nullable()->after('exam_id');
        });

        DB::table('exam_registrations')->update([
            'date' => DB::raw('DATE(created_at)'),
        ]);

        $registrations = DB::table('exam_registrations')
            ->select(['id', 'date'])
            ->get();

        foreach ($registrations as $registration) {
            DB::table('exam_attempts')
                ->where('exam_registration_id', $registration->id)
                ->update(['date' => $registration->date]);
        }
    }

    public function down(): void
    {
        Schema::table('exam_registrations', function (Blueprint $table) {
            $table->dropColumn('date');
        });
    }
};
