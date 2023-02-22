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
            $table->float('subtotal_amount')->unsigned()->default(0)->after('total_quantity');
            $table->float('discount')->unsigned()->default(0);
            $table->float('total_discount')->unsigned()->default(0);
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
            $table->dropColumn('subtotal_amount');
            $table->dropColumn('discount');
            $table->dropColumn('total_discount');
        });
    }
};
