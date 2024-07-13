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
        Schema::create('rejected_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('participant_id')->constrained('users');
            $table->string('username');
            $table->string('firstname');
            $table->string('lastname');
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->text('reason');
            $table->string('email');
            $table->date('date_of_birth');
            $table->timestamps();
            $table->unique('participant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rejected_participants');
    }
};
