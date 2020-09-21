<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFilesToMerchantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('merchants', function (Blueprint $table) {
            
            $table->string('name_attr')->nullable()->comment('商户简称')->after('name');

            $table->smallInteger('mch_type')->default(2)->comment('商户类型: 0-企业商户，1-个体商户，2-小微商户')->after('name_attr');

            $table->smallInteger('mch_category')->default(1)->comment('商户业务类别')->after('mch_type');

            $table->string('mcc')->nullable()->comment('商户MCC')->after('mch_category');

            $table->string('addr')->nullable()->comment('商户经营地址')->after('mcc');

            $table->string('area_code')->nullable()->comment('商户地区码')->after('addr');

            $table->string('owner_name')->nullable()->comment('商户法人姓名')->after('area_code');

            $table->string('owner_cert_id')->nullable()->comment('商户法人身份证号')->after('phone');

            $table->string('owner_cert_sdate')->nullable()->comment('身份证有效期:起')->after('owner_cert_id');

            $table->string('owner_cert_edate')->nullable()->comment('身份证有效期:止')->after('owner_cert_sdate');

            $table->string('license_no')->nullable()->comment('营业执照编号')->after('owner_cert_edate');

            $table->string('license_sdate')->nullable()->comment('营业执照有效期:起')->after('license_no');

            $table->string('license_edate')->nullable()->comment('营业执照有效期:止')->after('license_sdate');

            $table->string('settle_acc_no')->nullable()->comment('结算银行账户')->after('license_edate');
            
            $table->string('settle_acc_name')->nullable()->comment('结算银行账户户名')->after('settle_acc_no');

            $table->string('settle_acc_type')->default(0)->comment('结算银行账户类型0对私 2对公')->after('settle_acc_name');

            $table->string('settle_bank_name')->nullable()->comment('结算银行名称')->after('settle_acc_type');

            $table->string('settle_bank_branch')->nullable()->comment('结算银行支行名称')->after('settle_bank_name');

            $table->string('settle_bank_branch_no')->nullable()->comment('结算银行联行号')->after('settle_bank_branch');

            $table->string('d0_flag')->default(1)->comment('D0结算标志 0-不开通，1-开通')->after('settle_bank_branch_no');
            

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
