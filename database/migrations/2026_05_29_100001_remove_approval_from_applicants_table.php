<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('applicants')) {
            return;
        }

        Schema::table('applicants', function (Blueprint $table) {
            if (Schema::hasColumn('applicants', 'approved_by')) {
                $table->dropForeign(['approved_by']);
            }
        });

        $columnsToDrop = array_filter(
            ['approved', 'approved_at', 'approved_by'],
            fn (string $column) => Schema::hasColumn('applicants', $column)
        );

        if ($columnsToDrop !== []) {
            Schema::table('applicants', function (Blueprint $table) use ($columnsToDrop) {
                $table->dropColumn($columnsToDrop);
            });
        }
    }

    public function down(): void
    {
        Schema::table('applicants', function (Blueprint $table) {
            if (! Schema::hasColumn('applicants', 'approved')) {
                $table->boolean('approved')->default(false);
            }
            if (! Schema::hasColumn('applicants', 'approved_at')) {
                $table->timestamp('approved_at')->nullable();
            }
            if (! Schema::hasColumn('applicants', 'approved_by')) {
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            }
        });
    }
};
