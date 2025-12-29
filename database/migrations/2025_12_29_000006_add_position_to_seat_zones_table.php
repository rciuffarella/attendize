<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddPositionToSeatZonesTable extends Migration
{
    public function up()
    {
        Schema::table('seat_zones', function (Blueprint $table) {
            $table->decimal('position_x', 5, 2)->nullable()->after('price_modifier');
            $table->decimal('position_y', 5, 2)->nullable()->after('position_x');
        });
    }

    public function down()
    {
        Schema::table('seat_zones', function (Blueprint $table) {
            $table->dropColumn(['position_x', 'position_y']);
        });
    }
}


