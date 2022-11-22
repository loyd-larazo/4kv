<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AppController;
use App\Http\Middleware\ValidateUser;

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

Route::get('/login', [AppController::class, 'loginPage']);
Route::post('/login', [AppController::class, 'login']);

Route::middleware([ValidateUser::class])->group(function () {
  Route::get('/', [AppController::class, 'index']);
});
