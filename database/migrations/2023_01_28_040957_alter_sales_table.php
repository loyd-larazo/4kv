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
    Schema::table('sales', function (Blueprint $table) {
			$table->enum('type', ['sales', 'return'])->default('sales')->after('change_amount');
		});

    Schema::table('sale_items', function (Blueprint $table) {
			$table->enum('type', ['sales', 'return'])->default('sales')->after('total_amount');
		});
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::table('sales', function (Blueprint $table) {
			$table->dropColumn('type');
		});

    Schema::table('sale_items', function (Blueprint $table) {
			$table->dropColumn('type');
		});
  }
};
