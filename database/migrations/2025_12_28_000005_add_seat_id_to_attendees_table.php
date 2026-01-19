<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddSeatIdToAttendeesTable extends Migration
{
    public function up()
    {
        Schema::table('attendees', function (Blueprint $table) {
            $table->unsignedInteger('seat_id')->nullable()->after('ticket_id')->index();

            $table->foreign('seat_id')->references('id')->on('seats')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('attendees', function (Blueprint $table) {
            $table->dropForeign(['seat_id']);
            $table->dropColumn('seat_id');
        });
    }
}





