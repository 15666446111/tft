<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMccsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mccs', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('mcc')->nullable()->comment('mcc编码');

            $table->string('name')->nullable()->comment('行业');

            $table->string('desc', 800)->nullable()->comment('描述');

            $table->string('category')->nullable()->comment('分类');

            $table->string('remark', 800)->nullable()->comment('备注');

            $table->string('big_type_code')->nullable()->comment('大分类编码');

            $table->string('big_type_name')->nullable()->comment('大分类行业');

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
        Schema::dropIfExists('mccs');
    }
}
