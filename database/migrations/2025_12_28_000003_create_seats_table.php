<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSeatsTable extends Migration
{
    public function up()
    {
        Schema::create('seats', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('seat_zone_id')->index();

            $table->string('row_label', 20)->nullable();
            $table->string('seat_number', 20)->nullable();

            // Coordinate nella piantina (in pixel o percentuale, da decidere lato UI)
            $table->decimal('x', 8, 2)->nullable();
            $table->decimal('y', 8, 2)->nullable();

            // Stato del posto: free, reserved, sold, blocked
            $table->string('status', 20)->default('free');

            // Prezzo specifico per il singolo posto (se diverso dal ticket/zona)
            $table->decimal('price_override', 10, 2)->nullable();

            $table->timestamps();

            $table->foreign('seat_zone_id')->references('id')->on('seat_zones')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('seats');
    }
}





