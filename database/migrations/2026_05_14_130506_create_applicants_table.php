<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('applicants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('identifier')->unique(); // ИИН
            $table->text('address');
            $table->string('phone');
            $table->string('graduate_organization');
            $table->string('graduate_year');
            $table->string('speciality');
            $table->string('language'); // kz, ru, en
            $table->boolean('verified')->default(false);
            $table->boolean('approved')->default(false);
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('telegram_token')->nullable()->unique();
            $table->string('document_front')->nullable();
            $table->string('document_back')->nullable();
            $table->string('diplom')->nullable();
            $table->string('certificate')->nullable();
            $table->string('photo')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applicants');
    }
};
