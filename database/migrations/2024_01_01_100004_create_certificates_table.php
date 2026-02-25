<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('enrollment_id')->constrained()->cascadeOnDelete();
            $table->timestamp('issued_at')->useCurrent();
            // Guard for email-once guarantee
            $table->timestamp('completion_email_sent_at')->nullable();
            $table->timestamps();

            // One certificate per enrollment
            $table->unique('enrollment_id');

            $table->index('user_id');
            $table->index('uuid');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
