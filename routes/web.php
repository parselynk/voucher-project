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


Route::get('/', 'VoucherController@index');
Route::get('/offers', 'OfferController@index');
Route::post('/createoffer', 'OfferController@create');
Route::post('/createvouchers', 'VoucherController@createVouchers');
Route::get('/validatex', 'VoucherController@validateVoucher');
