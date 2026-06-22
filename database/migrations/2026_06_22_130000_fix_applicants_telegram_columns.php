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
            if (! Schema::hasColumn('applicants', 'verified')) {
                $table->boolean('verified')->default(false)->after('language');
            }

            if (! Schema::hasColumn('applicants', 'telegram_token')) {
                $table->string('telegram_token')->nullable()->unique()->after('verified');
            }

            if (! Schema::hasColumn('applicants', 'telegram_chat_id')) {
                $table->string('telegram_chat_id')->nullable()->after('telegram_token');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('applicants')) {
            return;
        }

        Schema::table('applicants', function (Blueprint $table) {
            $columns = array_filter(
                ['telegram_chat_id', 'telegram_token', 'verified'],
                fn (string $column) => Schema::hasColumn('applicants', $column)
            );

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
