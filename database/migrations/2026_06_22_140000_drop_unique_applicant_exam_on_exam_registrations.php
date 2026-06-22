<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const UNIQUE_INDEX = 'exam_registrations_applicant_id_exam_id_unique';

    private const APPLICANT_INDEX = 'exam_registrations_applicant_id_index';

    public function up(): void
    {
        if (! Schema::hasIndex('exam_registrations', self::UNIQUE_INDEX)) {
            return;
        }

        // MySQL may use the composite unique index for the applicant_id FK; add a
        // standalone index before dropping the unique constraint.
        if (! Schema::hasIndex('exam_registrations', self::APPLICANT_INDEX)) {
            Schema::table('exam_registrations', function (Blueprint $table) {
                $table->index('applicant_id', self::APPLICANT_INDEX);
            });
        }

        Schema::table('exam_registrations', function (Blueprint $table) {
            $table->dropUnique(['applicant_id', 'exam_id']);
        });
    }

    public function down(): void
    {
        if (Schema::hasIndex('exam_registrations', self::UNIQUE_INDEX)) {
            return;
        }

        Schema::table('exam_registrations', function (Blueprint $table) {
            $table->unique(['applicant_id', 'exam_id']);
        });

        if (Schema::hasIndex('exam_registrations', self::APPLICANT_INDEX)) {
            Schema::table('exam_registrations', function (Blueprint $table) {
                $table->dropIndex(self::APPLICANT_INDEX);
            });
        }
    }
};
