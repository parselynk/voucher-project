<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/verify', function () {
    return response()->json([
        'data' => [ 'errors' => 'atleast an email needs to be provided as parameter for the API' ],
                    'sucess' => false
    ], 404);
});

Route::get('/get', function () {
    return response()->json([
        'data' => [ 'errors' => 'atleast an email needs to be provided as parameter for the API' ],
                    'sucess' => false
    ], 404);
});

Route::get('/verify/{email}/{code}', 'ApiController@verify');
Route::get('/verify/{email}', 'ApiController@show');
Route::get('/get/{email}', 'ApiController@show');
