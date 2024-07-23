<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('challenge_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('challenge_id')->constrained('challenges')->onDelete('cascade');
            $table->unsignedBigInteger('participant_id');
            $table->foreign('participant_id')->references('participant_id')->on('participants')->onDelete('cascade');
            $table->integer('score');
            $table->integer('deducted_marks');
            $table->integer('time_taken');
            $table->boolean('completed')->default(false);
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('challenge_attempts');
    }
};
