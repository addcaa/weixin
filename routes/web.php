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

Route::get('/', function () {
    return view('welcome');
    // phpinfo();
});
Route::any('weixi/valid','Wxcontroller@valid');
Route::any('weixi/valid','Wxcontroller@index');

Route::any('weixi/AccessToren','Wxcontroller@AccessToren');
Route::any('weixi/test','Wxcontroller@test');
Route::any('weixi/a','Wxcontroller@a');

