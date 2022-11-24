<?php

use Illuminate\Support\Facades\Route;

use App\Http\Middleware\ValidateUser;

use App\Http\Controllers\AppController;
use App\Http\Controllers\LaborerController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\POSController;

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
  Route::get('/', [AppController::class, 'index'])->name('dashboard');

  Route::get('/laborers', [LaborerController::class, 'index'])->name('laborers');
  Route::post('/laborer', [LaborerController::class, 'updateOrCreate']);
  Route::delete('/laborer/{id}', [LaborerController::class, 'destroy']);

  Route::get('/categories', [ItemController::class, 'categories'])->name('categories');
  Route::post('/category', [ItemController::class, 'updateOrCreateCategory']);
  Route::delete('/category/{id}', [ItemController::class, 'destroyCategory']);

  Route::get('/items', [ItemController::class, 'items'])->name('items');
  Route::post('/item', [ItemController::class, 'updateOrCreateItem']);
  Route::delete('/item/{id}', [ItemController::class, 'destroyItem']);
  Route::get('/item/{sku}/barcode', [ItemController::class, 'generateBarcode']);

  Route::get('/suppliers', [ItemController::class, 'suppliers'])->name('suppliers');
  Route::post('/supplier', [ItemController::class, 'updateOrCreateSupplier']);

  Route::get('/settings', [AppController::class, 'settings'])->name('settings');
  Route::post('/settings', [AppController::class, 'updateSettings'])->name('updateSettings');

  Route::get('/transactions', [TransactionController::class, 'transactions'])->name('transactions');
  Route::post('/transaction', [TransactionController::class, 'transaction']);

  Route::get('/logout', [AppController::class, 'logout']);
});
