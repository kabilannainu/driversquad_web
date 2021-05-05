<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCurrentToProviderDevicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('provider_devices', function (Blueprint $table) {
           $table->string('mobile')->nullable();
           $table->string('otp')->nullable();
           $table->integer('current')->after('type')->default(0);
           $table->integer('status')->after('type')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('provider_devices', function (Blueprint $table) {
            //
        });
    }
}
