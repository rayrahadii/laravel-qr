<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FormInput\BarangController;

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
    return view('login');
});

Route::controller(BarangController::class)->group(function () {
  Route::prefix('barang')->group(function () {
    Route::get('/input','inputView');
  });
});

Route::get('/test', function () {
    return view('barang/input');
});
