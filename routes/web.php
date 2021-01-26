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

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::get('/products/create','ProductController@create')->name('product.new');
Route::post('/products/create','ProductController@store')->name('product.store');
Route::get('products/all','ProductController@viewProducts')->name('product.all');
Route::get('/products/delete/{id}','ProductController@deleteProducts')->name('product.delete');
Route::get('/products/deleteall','ProductController@deleteAllProducts')->name('product.deleteall');