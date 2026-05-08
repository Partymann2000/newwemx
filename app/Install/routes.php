<?php

/*
|--------------------------------------------------------------------------
| Install Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::prefix('install')->middleware(['web'])->group(function () {
    Route::view('/', 'install::master')->name('install.index');
});
