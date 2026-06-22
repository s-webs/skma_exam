<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_registrations', function (Blueprint $table) {
            $table->dropUnique(['applicant_id', 'exam_id']);
        });
    }

    public function down(): void
    {
        Schema::table('exam_registrations', function (Blueprint $table) {
            $table->unique(['applicant_id', 'exam_id']);
        });
    }
};
