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
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');


Route::get('/sender',function (){
	event(new \App\Events\listenMessage('Hello every body'));
	return view('sender');
})->name('sender');


Route::get('/looker',function (){
	return view('looker');
})->name('looker');

Route::get('/{any}',function (){
	return view('app');
})->where(['any'=>'.*']);


