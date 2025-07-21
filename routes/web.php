<?php

use Illuminate\Support\Facades\Route;

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
Route::get('/privacy_policy', function () {
    return view('privacy_policy');
});
Route::get('/delete_account_request', function () {
    return view('delete_account_request');
});
Route::get('/customer_chat/{id}', "App\Http\Controllers\CustomerController@customer_chat");
