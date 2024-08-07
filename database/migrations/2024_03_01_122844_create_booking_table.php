<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('booking', function (Blueprint $table) {
            $table->id();
            $table->foreignId("id_online_center")->constrained("online_centers")->cascadeOnDelete();
            $table->foreignId("id_user")->constrained("users")->cascadeOnDelete();
            $table->bigInteger("mark");
            $table->enum("status",["0","1"])->default("0");
            $table->enum("done",["0","1"])->default("0");
            $table->enum("can",["0","1"])->default("1");
            $table->integer("count")->default("0");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('booking');
    }
};
