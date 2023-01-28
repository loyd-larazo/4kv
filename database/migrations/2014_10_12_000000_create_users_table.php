<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
// EuricaDesu@01Secret

return new class extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {

    Schema::create('users', function (Blueprint $table) {
      $table->id();
      $table->string('username');
      $table->string('password');
      $table->enum('type', ['admin', 'cashier', 'stock man']);
      $table->string('firstname');
      $table->string('lastname')->nullable();
      $table->enum('gender', ['male', 'female']);
      $table->date('birthdate')->nullable();
      $table->text('address')->nullable();
      $table->string('contact_number')->nullable();
      $table->float('salary')->unsigned()->nullable();
      $table->tinyInteger('status')->default(1)->unsigned();
      $table->timestamps();
    });

    Schema::create('settings', function (Blueprint $table) {
      $table->id();
      $table->string('key');
      $table->string('value');
      $table->timestamps();
    });

    Schema::create('categories', function (Blueprint $table) {
      $table->id();
      $table->string('name');
      $table->tinyInteger('status')->default(1)->unsigned();
      $table->timestamps();
    });

    Schema::create('items', function (Blueprint $table) {
      $table->id();
      $table->string('sku');
      $table->string('name');
      $table->float('cost')->unsigned();
      $table->float('price')->unsigned();
      $table->text('description')->nullable();
      $table->bigInteger('category_id')->unsigned()->nullable();
      $table->tinyInteger('sold_by_weight')->default('0');
      $table->tinyInteger('sold_by_length')->default('0');
      $table->float('stock')->unsigned()->default(0);
      $table->tinyInteger('status')->default(1)->unsigned();
      $table->timestamps();

      $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
    });

    Schema::create('suppliers', function (Blueprint $table) {
      $table->id();
      $table->string('name');
      $table->string('contact_person')->nullable();
      $table->string('contact_number')->nullable();
      $table->text('address');
      $table->tinyInteger('status')->default(1)->unsigned();
      $table->timestamps();
    });

    Schema::create('transactions', function (Blueprint $table) {
      $table->id();
      $table->string('transaction_code');
      $table->bigInteger('user_id')->unsigned();
      $table->string('stock_man');
      $table->bigInteger('total_quantity')->unsigned();
      $table->float('total_amount')->unsigned();
      $table->text('remarks')->nullable();
      $table->timestamps();

      $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
    });

    Schema::create('transaction_items', function (Blueprint $table) {
      $table->id();
      $table->bigInteger('transaction_id')->unsigned();
      $table->bigInteger('item_id')->unsigned();
      $table->bigInteger('supplier_id')->unsigned();
      $table->bigInteger('quantity')->unsigned();
      $table->float('amount')->unsigned();
      $table->float('total_amount')->unsigned();
      $table->timestamps();

      $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
      $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('cascade');
      $table->foreign('transaction_id')->references('id')->on('transactions')->onDelete('cascade');
    });

    Schema::create('daily_sales', function (Blueprint $table) {
      $table->id();
      $table->bigInteger('opening_user_id')->unsigned();
      $table->bigInteger('closing_user_id')->unsigned()->nullable();
      $table->bigInteger('sales_count')->unsigned()->default(0);
      $table->float('sales_amount')->unsigned()->default(0);
      $table->float('opening_amount')->unsigned();
      $table->float('closing_amount')->unsigned()->nullable();
      $table->float('difference_amount')->nullable();
      $table->timestamps();
      
      $table->foreign('opening_user_id')->references('id')->on('users')->onDelete('cascade');
      $table->foreign('closing_user_id')->references('id')->on('users')->onDelete('cascade');
    });

    Schema::create('sales', function (Blueprint $table) {
      $table->id();
      $table->bigInteger('user_id')->unsigned();
      $table->bigInteger('daily_sale_id')->unsigned();
      $table->string('reference');
      $table->float('total_quantity')->unsigned();
      $table->float('total_amount')->unsigned();
      $table->float('paid_amount')->unsigned();
      $table->float('change_amount')->unsigned();
      $table->timestamps();

      $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
      $table->foreign('daily_sale_id')->references('id')->on('daily_sales')->onDelete('cascade');
    });

    Schema::create('sale_items', function (Blueprint $table) {
      $table->id();
      $table->bigInteger('sale_id')->unsigned();
      $table->bigInteger('item_id')->unsigned();
      $table->float('quantity')->unsigned();
      $table->float('amount')->unsigned();
      $table->float('total_amount')->unsigned();
      $table->timestamps();

      $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
      $table->foreign('sale_id')->references('id')->on('sales')->onDelete('cascade');
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('sale_items');
    Schema::dropIfExists('sales');
    Schema::dropIfExists('daily_sales');
    Schema::dropIfExists('transaction_items');
    Schema::dropIfExists('transactions');
    Schema::dropIfExists('suppliers');
    Schema::dropIfExists('items');
    Schema::dropIfExists('categories');
    Schema::dropIfExists('settings');
    Schema::dropIfExists('users');
  }
};
