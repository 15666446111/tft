<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRegionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('regions', function (Blueprint $table) {
            
            $table->bigIncrements('id');

            $table->string('area_code')->nullable()->comment('地区编码');

            $table->string('area')->nullable()->comment('地区');

            $table->string('city_code')->nullable()->comment('城市编码');

            $table->string('city')->nullable()->comment('城市');

            $table->string('province_code')->nullable()->comment('省份编码');

            $table->string('province')->nullable()->comment('省份');

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
        Schema::dropIfExists('regions');
    }
}
