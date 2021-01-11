<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DefaultReportTemplate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('default_report_template', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name',100)->nullable();
            $table->string('default',100)->nullable()->default("pdf");
            $table->longText('template')->nullable();
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
        Schema::dropIfExists('default_report_template');
    }
}
