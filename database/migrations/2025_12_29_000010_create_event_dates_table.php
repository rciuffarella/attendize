<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEventDatesTable extends Migration
{
    public function up()
    {
        Schema::create('event_dates', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('event_id');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->integer('quantity_available')->nullable()->comment('Disponibilità totale per questa data (opzionale, se null usa quella dell\'evento)');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');
            $table->index(['event_id', 'start_date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('event_dates');
    }
}
