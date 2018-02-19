<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApiPagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('api_pages', function (Blueprint $table) {
            $table->increments('id');
            $table->string('pageId');
            $table->string('next_change_id');
            $table->integer('stashes')->default(0);
            $table->integer('items')->default(0);
            $table->text('stats');
            $table->boolean('processed')->default(0);
            $table->timestamps();
        });

        Schema::table('api_pages', function($table) {
            $table->index('processed');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('api_pages');
    }
}
