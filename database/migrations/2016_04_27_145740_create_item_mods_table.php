<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemModsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('item_mods', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('item_id');
            $table->integer('mod_id');
            $table->integer('value');
            $table->string('name');
            $table->softDeletes();
        });

        Schema::table('item_mods', function($table) {
            $table->index('item_id');
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('item_mods');
    }
}
