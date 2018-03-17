<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// \DB::listen(function($sql) {
//     var_dump($sql->sql);
// });


Route::get('/', ['as' => 'queries.index', 'uses' => 'SearchController@index']);
Route::post('/', ['as' => 'queries.search', 'uses' => 'SearchController@search']);
