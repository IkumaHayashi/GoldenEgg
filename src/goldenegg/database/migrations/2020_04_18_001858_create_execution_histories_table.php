<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExecutionHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('execution_histories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('execution_date')->comment('約定日');
            $table->string('code', 8)->comment('銘柄コード');
            $table->integer('quantity')->comment('約定数量');
            $table->double('unitprice', 8, 2)->comment('約定単価');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            $table->index('user_id');
            $table->foreign('user_id')
                  ->references('id')->on('users')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('execution_histories');
    }
}
