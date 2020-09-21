<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMoreToMerchantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('merchants', function (Blueprint $table) {

            $table->smallInteger('verfity_state')->default(0)->comment('审核状态 0 待审核 1审核成功 -1 失败')->after('state');

            $table->string('remark')->nullable()->comment('备注信息')->after('activate_sn');

            $table->text('api_return')->nullable()->comment('腾付通返回数据')->after('remark');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('merchants', function (Blueprint $table) {
            //
        });
    }
}
