<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DefaultArticles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('articles');
        Schema::create('default_articles', function (Blueprint $table) {
            $table->bigIncrements('id');           
            $table->longText('content');
            $table->longText('headlines');
            $table->text('logo');
            $table->string('title');            
            $table->string('tag');
            $table->timestamps();
            $table->bigInteger('hits');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('articles');
    }
}
