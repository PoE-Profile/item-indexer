<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStashesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stashes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('accountName')->nullable();
            $table->string('stash');
            $table->string('lastCharacterName')->nullable();
            $table->string('poeStashId')->unique();
            $table->text('current_items')->nullable();
            $table->string('league')->default("Standard");

            $table->timestamps();
        });
        Schema::table('stashes', function($table) {
            $table->index('accountName');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('stashes');
    }
}
