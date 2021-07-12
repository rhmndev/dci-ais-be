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

Route::get('/label-qc-pdf', 'LabelController@printLabelQC');
Route::get('/label-long-pdf', 'LabelController@printLabelLong');

Route::get('/label-box', function() {
    return view('label_box_pdf');
});