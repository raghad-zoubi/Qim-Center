<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('track_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId("id_booking")->constrained("booking")->cascadeOnDelete();
            $table->foreignId("id_content")->constrained("contents")->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('track_contents');
    }
};
