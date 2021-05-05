<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAppleToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            \DB::statement("ALTER TABLE users CHANGE login_by login_by ENUM('manual','facebook','google','apple')");
          
        });

        Schema::table('providers', function (Blueprint $table) {
            \DB::statement("ALTER TABLE providers CHANGE login_by login_by ENUM('manual','facebook','google','apple')");
          
        });       
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
        });
    }
}
