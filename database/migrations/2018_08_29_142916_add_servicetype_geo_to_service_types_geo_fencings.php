<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddServicetypeGeoToServiceTypesGeoFencings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('service_types_geo_fencings', function (Blueprint $table) {
            $table->double('fixed',10,2)->after('service_type_id');
            $table->double('price',10,2)->after('service_type_id');
            $table->double('minute',10,2)->after('service_type_id');
            $table->string('hour')->after('service_type_id')->nullable();
            $table->double('distance',10,2)->after('service_type_id');
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
