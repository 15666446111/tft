<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOpearteToCashsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cashs', function (Blueprint $table) {
            
            $table->dropColumn('opearte');

            $table->string('operate')->default('')->comment('操盘号')->after('remark');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cashs', function (Blueprint $table) {
            //
        });
    }
}
