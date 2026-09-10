<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->enum('community', ['fad', 'genre']);
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('title', 150);
            $table->text('description');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('location', 150)->nullable();
            $table->timestamps();
            $table->index(['community', 'start_date']);
            $table->index(['community', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
