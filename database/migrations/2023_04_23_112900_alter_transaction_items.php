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
        Schema::table('return_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('return_transactions', 'transaction_item_id')) {
                $table->dropForeign('return_transactions_transaction_item_id_foreign');
                $table->dropColumn('transaction_item_id');
            }

            if (Schema::hasColumn('return_transactions', 'amount')) {
                $table->dropColumn('amount');
            }
        });

        Schema::table('transaction_items', function (Blueprint $table) {
            $table->bigInteger('return_transaction_id')->unsigned()->nullable()->after('total_amount');
            $table->float('return_quantity')->unsigned()->default(0)->after('return_transaction_id');
            $table->decimal('return_total_amount', 18, 2)->unsigned()->default(0)->after('return_quantity');

            $table->foreign('return_transaction_id')->references('id')->on('return_transactions')->onDelete('cascade');
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
            $table->dropForeign('transaction_items_return_transaction_id_foreign');
            $table->dropColumn('return_transaction_id');
        });
    }
};
