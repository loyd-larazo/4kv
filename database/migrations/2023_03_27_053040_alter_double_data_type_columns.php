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
    Schema::table('daily_sales', function (Blueprint $table) {
      $table->decimal('sales_amount', 18, 2)->unsigned()->default(0)->change();
      $table->decimal('opening_amount', 18, 2)->unsigned()->change();
      $table->decimal('closing_amount', 18, 2)->unsigned()->nullable()->change();
      $table->decimal('difference_amount', 18, 2)->nullable()->change();
    });

    Schema::table('damage_items', function (Blueprint $table) {
      $table->decimal('amount', 18, 2)->unsigned()->change();
      $table->decimal('total_amount', 18, 2)->unsigned()->change();
    });

    Schema::table('sales', function (Blueprint $table) {
      $table->decimal('subtotal_amount', 18, 2)->unsigned()->default(0)->change();
      $table->decimal('total_amount', 18, 2)->unsigned()->change();
      $table->decimal('paid_amount', 18, 2)->unsigned()->change();
      $table->decimal('change_amount', 18, 2)->unsigned()->change();
      $table->decimal('total_discount', 18, 2)->unsigned()->change();
    });

    Schema::table('sale_items', function (Blueprint $table) {
      $table->decimal('amount', 18, 2)->unsigned()->change();
      $table->decimal('total_amount', 18, 2)->unsigned()->change();
    });

    Schema::table('transactions', function (Blueprint $table) {
      $table->decimal('total_amount', 18, 2)->unsigned()->change();
    });

    Schema::table('transaction_items', function (Blueprint $table) {
      $table->decimal('amount', 18, 2)->unsigned()->change();
      $table->decimal('total_amount', 18, 2)->unsigned()->change();
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::table('transaction_items', function (Blueprint $table) {
      $table->double('amount', 8, 2)->unsigned()->change();
      $table->double('total_amount', 8, 2)->unsigned()->change();
    });

    Schema::table('transactions', function (Blueprint $table) {
      $table->double('total_amount', 8, 2)->unsigned()->change();
    });

    Schema::table('sale_items', function (Blueprint $table) {
      $table->double('amount', 8, 2)->unsigned()->change();
      $table->double('total_amount', 8, 2)->unsigned()->change();
    });

    Schema::table('sales', function (Blueprint $table) {
      $table->double('subtotal_amount', 8, 2)->unsigned()->default(0)->change();
      $table->double('total_amount', 8, 2)->unsigned()->change();
      $table->double('paid_amount', 8, 2)->unsigned()->change();
      $table->double('change_amount', 8, 2)->unsigned()->change();
      $table->double('total_discount', 8, 2)->unsigned()->change();
    });

    Schema::table('damage_items', function (Blueprint $table) {
      $table->double('amount', 8, 2)->unsigned()->change();
      $table->double('total_amount', 8, 2)->unsigned()->change();
    });

    Schema::table('daily_sales', function (Blueprint $table) {
      $table->double('sales_amount', 8, 2)->unsigned()->default(0)->change();
      $table->double('opening_amount', 8, 2)->unsigned()->change();
      $table->double('closing_amount', 8, 2)->unsigned()->nullable()->change();
      $table->double('difference_amount', 8, 2)->nullable()->change();
    });
  }
};
