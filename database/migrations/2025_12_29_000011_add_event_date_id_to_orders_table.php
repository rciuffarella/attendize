<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddEventDateIdToOrdersTable extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedInteger('event_date_id')->nullable()->after('event_id');
            $table->foreign('event_date_id')->references('id')->on('event_dates')->onDelete('set null');
            $table->index('event_date_id');
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['event_date_id']);
            $table->dropColumn('event_date_id');
        });
    }
}
