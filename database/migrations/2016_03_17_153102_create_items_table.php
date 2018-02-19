<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void

     */
    public function up()
    {
        Schema::create('items', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->integer('stash_id')->unsigned();
            $table->string('note');
            $table->text('implicitMods');
            $table->text('explicitMods');
            $table->text('craftedMods');
            $table->text('enchantMods');
            $table->text('properties');
            $table->string('type', 50);
            $table->text('sockets');
            $table->string('name');
            $table->string('typeLine');
            $table->string('league');
            $table->integer('frameType');
            $table->boolean('identified');
            $table->boolean('corrupted');
            $table->text('requirements');
            $table->text('icon');
            $table->string('itemId');// only for mods FK ->unique();
            $table->string('inventoryId');
            $table->integer('ilvl');
            $table->integer('socketNum');
            $table->integer('maxLinks');
            $table->string('socketColor');
            $table->string('validColor');
            $table->integer('w');
            $table->integer('h');
            $table->integer('x');
            $table->integer('y');
            $table->boolean('mods')->nullable();
            $table->softDeletes();
            $table->timestamps();


        });

        Schema::table('items', function($table) {
            $table->index('stash_id');
            $table->index('itemId');
            $table->index('type');
            $table->index('league');
            $table->index('mods');
            $table->index('deleted_at');
        });

        // Schema::table('items', function($table) {
        //     $table->foreign('stash_id')
        //           ->references('poeStashId')->on('stashes')
        //           ->onDelete('cascade');
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('items', function (Blueprint $table){
        //   $table->dropForeign('stash_id');
          $table->dropIndex(['mods']);
        });
    }
}
