<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_type_role', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['exam_type_id', 'role_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_type_role');
    }
};
