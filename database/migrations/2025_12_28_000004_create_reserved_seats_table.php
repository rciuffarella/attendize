<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateReservedSeatsTable extends Migration
{
    public function up()
    {
        Schema::create('reserved_seats', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('seat_id')->index();
            $table->unsignedInteger('event_id')->index();

            $table->string('session_id')->index();
            $table->dateTime('expires_at');

            $table->timestamps();

            $table->foreign('seat_id')->references('id')->on('seats')->onDelete('cascade');
            $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('reserved_seats');
    }
}



