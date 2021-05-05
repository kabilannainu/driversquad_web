<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFleetDevicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fleet_devices', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('fleet_id');
            $table->string('udid');
            $table->string('token');
            $table->string('sns_arn')->nullable();
            $table->enum('type', ['android', 'ios']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fleet_devices');
    }
}
