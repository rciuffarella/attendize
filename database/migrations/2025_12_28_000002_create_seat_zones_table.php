<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSeatZonesTable extends Migration
{
    public function up()
    {
        Schema::create('seat_zones', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('seat_map_id')->index();
            $table->unsignedInteger('ticket_id')->nullable()->index();

            $table->string('name');
            $table->string('color', 20)->nullable();

            // Opzionale: maggiorazione o sconto sul prezzo del ticket associato
            $table->decimal('price_modifier', 10, 2)->default(0);

            $table->timestamps();

            $table->foreign('seat_map_id')->references('id')->on('seat_maps')->onDelete('cascade');
            $table->foreign('ticket_id')->references('id')->on('tickets')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('seat_zones');
    }
}



