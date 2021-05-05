<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCityLimitsToServiceTypesGeoFencingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('service_types_geo_fencings', function (Blueprint $table) {
            $table->double('city_limits',10,2)->after('non_geo_price')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('service_types_geo_fencings', function (Blueprint $table) {
            //
        });
    }
}
