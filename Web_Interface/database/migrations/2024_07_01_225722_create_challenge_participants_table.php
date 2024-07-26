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
        Schema::create('challenge_participants', function (Blueprint $table) {
            $table->unsignedBigInteger('challenge_id');
            $table->unsignedBigInteger('participant_id');
            $table->foreign('challenge_id')->references('id')->on('challenges')->onDelete('cascade');
            $table->foreign('participant_id')->references('id')->on('participants')->onDelete('cascade');
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('challenge_participants');
    }
};
