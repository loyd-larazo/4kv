<?php

use Illuminate\Support\Facades\Route;

use App\Http\Middleware\ValidateUser;

use App\Http\Controllers\AppController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\POSController;
use App\Http\Controllers\ReportController;

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
Route::post('/reset-password', [UserController::class, 'resetPassword']);

Route::middleware([ValidateUser::class])->group(function () {
  Route::get('/', [AppController::class, 'index'])->name('dashboard');

  Route::get('/users', [UserController::class, 'index'])->name('users');
  Route::post('/user', [UserController::class, 'updateOrCreate']);
  Route::delete('/user/{id}', [UserController::class, 'destroy']);

  Route::get('/categories', [ItemController::class, 'categories'])->name('categories');
  Route::post('/category', [ItemController::class, 'updateOrCreateCategory']);
  Route::delete('/category/{id}', [ItemController::class, 'destroyCategory']);

  Route::get('/items', [ItemController::class, 'items'])->name('items');
  Route::post('/item', [ItemController::class, 'updateOrCreateItem']);
  Route::delete('/item/{id}', [ItemController::class, 'destroyItem']);
  Route::get('/item/{sku}/barcode', [ItemController::class, 'generateBarcode']);
  Route::get('/validate/item/category/{categoryId}', [ItemController::class, 'validateProductName']);
  Route::get('/return-items', [ItemController::class, 'returnItems'])->name('returnItems');
  Route::post('/return-items/{salesId}', [ItemController::class, 'saveReturnItems'])->name('saveReturnItems');
  Route::get('/return-items/damage-type', [ItemController::class, 'damageItems'])->name('damageItems');

  Route::get('/suppliers', [ItemController::class, 'suppliers'])->name('suppliers');
  Route::post('/supplier', [ItemController::class, 'updateOrCreateSupplier']);

  Route::get('/settings', [AppController::class, 'settings'])->name('settings');
  Route::post('/settings', [AppController::class, 'updateSettings'])->name('updateSettings');

  Route::get('/transactions', [TransactionController::class, 'transactions'])->name('transactions');
  Route::get('/transaction', [TransactionController::class, 'addTransactionPage'])->name('addTransactionPage');
  Route::post('/transaction', [TransactionController::class, 'transaction']);

  Route::get('/cashier', [POSController::class, 'cashier'])->name('cashier');
  Route::post('/cashier/open', [POSController::class, 'openCashier'])->name('openCashier');
  Route::post('/cashier/close', [POSController::class, 'closeCashier'])->name('closeCashier');
  Route::get('/sales', [POSController::class, 'sales'])->name('sales');
  Route::post('/sales', [POSController::class, 'saveSales']);
  Route::get('/daily-sales', [POSController::class, 'dailySales'])->name('dailySales');
  Route::get('/sale/{saleId}', [POSController::class, 'printSale']);

  Route::get('/reports', [ReportController::class, 'index']);
  Route::get('/reports/load', [ReportController::class, 'loadData']);
  Route::get('/report/{type}/print', [ReportController::class, 'print']);

  Route::get('/logout', [AppController::class, 'logout']);
});
