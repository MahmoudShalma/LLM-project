<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->string('video_url', 500)->nullable();
            $table->longText('content')->nullable();
            $table->smallInteger('order_column')->unsigned()->default(0);
            $table->boolean('is_free_preview')->default(false);
            $table->boolean('is_required')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['course_id', 'slug']);
            $table->index(['course_id', 'order_column']);
            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
