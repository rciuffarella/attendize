<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStartLabelsToSeatZonesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('seat_zones', function (Blueprint $table) {
            if (!Schema::hasColumn('seat_zones', 'start_row_alpha')) {
                $table->integer('start_row_alpha')->default(65)->after('position_y'); // 'A'
            }
            if (!Schema::hasColumn('seat_zones', 'start_col_num')) {
                $table->integer('start_col_num')->default(1)->after('start_row_alpha');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('seat_zones', function (Blueprint $table) {
            if (Schema::hasColumn('seat_zones', 'start_row_alpha')) {
                $table->dropColumn('start_row_alpha');
            }
            if (Schema::hasColumn('seat_zones', 'start_col_num')) {
                $table->dropColumn('start_col_num');
            }
        });
    }
}


