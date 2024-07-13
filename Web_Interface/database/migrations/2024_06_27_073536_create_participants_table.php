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
        Schema::create('participants', function (Blueprint $table) {
            $table->id(); // Primary key for participants
            $table->foreignId('participant_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->foreignId('challenge_id')->nullable()->constrained('challenges')->onDelete('cascade');
            $table->integer('attempts_left')->default(3);
            $table->integer('total_score')->default(0);
            $table->boolean('completed')->default(false);
            $table->integer('time_taken')->default(0);
            $table->timestamps();
            // Add the unique constraint
            $table->unique(['participant_id', 'challenge_id'], 'unique_participant_challenge');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('participants');
    }
};
