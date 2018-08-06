<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AreaSetup extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::beginTransaction();
        Schema::create('provinces', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 10)->comment('省份名称');
            $table->timestamps();
        });

        Schema::create('cities', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('province_id')->index()->comment('省份id');
            $table->string('name', '10')->comment('城市名称');
            $table->timestamps();
        });

        Schema::create('areas', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('city_id')->index()->comment('城市id');
            $table->string('name',10)->comment('区县名称');
            $table->timestamps();
        });
        DB::commit();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('areas');
        Schema::dropIfExists('cities');
        Schema::dropIfExists('provinces');
    }
}
