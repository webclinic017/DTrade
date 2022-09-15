<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlatformCookiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('platform_cookies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->longText('data');
            $table->integer('platform_data_id')->unsigned()->index();
            $table->foreign('platform_data_id')->references('id')->on('platform_data')->onDelete('cascade');
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
        Schema::dropIfExists('platform_cookies');
    }
}
