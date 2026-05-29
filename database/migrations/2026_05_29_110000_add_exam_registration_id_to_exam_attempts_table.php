<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_attempts', function (Blueprint $table) {
            $table->foreignId('exam_registration_id')
                ->nullable()
                ->after('applicant_id')
                ->constrained('exam_registrations')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('exam_attempts', function (Blueprint $table) {
            $table->dropForeign(['exam_registration_id']);
            $table->dropColumn('exam_registration_id');
        });
    }
};
