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
            $table->id();
            $table->Integer('user_id');
            $table->Integer('school_id');
            $table->Integer('challenge_id');
            $table->integer('attempts_left')->default(3);
            $table->integer('total_score')->default(0);
            $table->boolean('completed')->default(false);
            $table->integer('time_taken')->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('school_id')->references('id')->on('schools');
            $table->foreign('challenge_id')->references('id')->on('challenges');
            $table->unique(['user_id', 'school_id', 'challenge_id']);
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
