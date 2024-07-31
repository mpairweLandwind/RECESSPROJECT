<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    { {
            Schema::create('analytics', function (Blueprint $table) {
                $table->id();
                $table->string('type');
                $table->json('data');
                $table->timestamps();
            });

            // Add the unique constraint using a raw SQL statement
            DB::statement('CREATE UNIQUE INDEX analytics_type_user_id_unique ON analytics (type, (data->>\'user_id\'))');
        }

    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analytics');
    }
};
