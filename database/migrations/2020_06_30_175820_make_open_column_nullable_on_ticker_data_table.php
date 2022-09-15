<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeOpenColumnNullableOnTickerDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ticker_data', function (Blueprint $table) {
            $table->float('open')->nullable()->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ticker_data', function (Blueprint $table) {
            $table->float('open')->nullable(false)->default(0.0)->change();
        });
    }
}
