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
    Schema::table('categories', function (Blueprint $table) {
      $table->tinyInteger('status')->default(1)->unsigned();
    });

    Schema::table('suppliers', function (Blueprint $table) {
      $table->tinyInteger('status')->default(1)->unsigned();
    });

    Schema::table('laborers', function (Blueprint $table) {
      $table->tinyInteger('status')->default(1)->unsigned();
    });

    Schema::table('transactions', function (Blueprint $table) {
      $table->string('laborer');
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::table('categories', function (Blueprint $table) {
      $table->dropColumn('status');
    });

    Schema::table('suppliers', function (Blueprint $table) {
      $table->dropColumn('status');
    });

    Schema::table('laborers', function (Blueprint $table) {
      $table->dropColumn('status');
    });

    Schema::table('transactions', function (Blueprint $table) {
      $table->dropColumn('laborer');
    });
  }
};
