<?php

use Illuminate\Database\Seeder;

use Carbon\Carbon;

class GeoFencingTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	DB::table('geo_fencings')->truncate();
        DB::table('geo_fencings')->insert([
            [
                'city_name' => 'chennai',
                'ranges' => '[{"lat":"13.226820","lng":"80.266232"},{"lat":"13.133222","lng":"80.016293"},{"lat":"12.945919","lng":"80.051998"},{"lat":"12.841503","lng":"80.236019"},{"lat":"12.996773","lng":"80.288204"},{"lat":"13.186711","lng":"80.343136"}]'
            ]
        ]);
    }
}
