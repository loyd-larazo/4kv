<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('settings', function (Blueprint $table) {
      $table->id();
      $table->string('key');
      $table->string('value');
      $table->timestamps();
    });

    Schema::create('categories', function (Blueprint $table) {
      $table->id();
      $table->string('name');
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
      $table->tinyInteger('sold_by_weight')->default('1');
      $table->bigInteger('stock')->unsigned()->default(0);
      $table->tinyInteger('status')->default(1)->unsigned();
      $table->timestamps();

      $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
    });

    Schema::create('laborers', function (Blueprint $table) {
      $table->id();
      $table->string('firstname');
      $table->string('lastname');
      $table->text('picture')->nullable();
      $table->enum('gender', ['male', 'female']);
      $table->date('birthdate');
      $table->text('address')->nullable();
      $table->string('contact_number')->nullable();
      $table->float('salary')->unsigned();
      $table->string('position');
      $table->timestamps();
    });

    Schema::create('suppliers', function (Blueprint $table) {
      $table->id();
      $table->string('name');
      $table->string('contact_person')->nullable();
      $table->string('contact_number')->nullable();
      $table->text('address');
      $table->timestamps();
    });

    Schema::create('transactions', function (Blueprint $table) {
      $table->id();
      $table->string('transaction_code');
      $table->bigInteger('total_quantity')->unsigned();
      $table->float('total_amount')->unsigned();
      $table->bigInteger('laborer_id')->unsigned();
      $table->text('remarks')->nullable();
      $table->timestamps();

      $table->foreign('laborer_id')->references('id')->on('laborers')->onDelete('cascade');
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

    Schema::create('sales', function (Blueprint $table) {
      $table->id();
      $table->string('reference');
      $table->bigInteger('total_quantity')->unsigned();
      $table->float('total_amount')->unsigned();
      $table->timestamps();
    });

    Schema::create('sale_items', function (Blueprint $table) {
      $table->id();
      $table->bigInteger('sale_id')->unsigned();
      $table->bigInteger('item_id')->unsigned();
      $table->bigInteger('quantity')->unsigned();
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
    Schema::dropIfExists('transaction_items');
    Schema::dropIfExists('transactions');
    Schema::dropIfExists('suppliers');
    Schema::dropIfExists('laborers');
    Schema::dropIfExists('items');
    Schema::dropIfExists('categories');
    Schema::dropIfExists('settings');
  }
};
