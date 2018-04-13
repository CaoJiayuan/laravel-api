<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGeoDistanceFunction extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sql = <<<SQL
CREATE FUNCTION getDistance
(
GPSLng DECIMAL(12,6),
GPSLat DECIMAL(12,6),
Lng  DECIMAL(12,6),
Lat DECIMAL(12,6)
)
RETURNS DECIMAL(12,4)
BEGIN
DECLARE result DECIMAL(12,4);
  set result=6371.004*ACOS(SIN(GPSLat/180*PI())*SIN(Lat/180*PI())+COS(GPSLat/180*PI())*COS(Lat/180*PI())*COS((GPSLng-Lng)/180*PI()));
RETURN result;
END
SQL;
        DB::unprepared($sql);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        DB::unprepared("DROP FUNCTION getDistance");
    }
}
