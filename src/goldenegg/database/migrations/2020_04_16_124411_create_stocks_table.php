<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->bigIncrements('id');

            // 将来的に証券コードは英字も含まれるため
            // https://www.jpx.co.jp/sicc/securities-code/nlsgeu00000329ri-att/20190926syouraiko-do.pdf
            $table->string('code', 8)->comment('銘柄コード');
            $table->string('company_name', 100)->comment('企業名')->default('');
            $table->string('market', 20)->comment('市場')->default('');
            $table->string('sector', 100)->comment('業種')->default('');
            $table->double('eps', 8, 2)->comment('EPS（1株あたり純利益）');
            $table->double('dividend', 8, 2)->comment('配当金');
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
        Schema::dropIfExists('stocks');
    }
}
