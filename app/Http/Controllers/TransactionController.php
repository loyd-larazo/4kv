<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Laborer;
use App\Models\Item;
use App\Models\Supplier;

class TransactionController extends Controller
{
	public function transactions(Request $request) {
		$page = $request->get('page') ?? 1;
    Paginator::currentPageResolver(function() use ($page) {
      return $page;
    });

		$suppliers = Supplier::get();
		$laborers = Laborer::get();
		$items = Item::select('id', 'sku', 'name', 'cost')->get();

    $transactions = Transaction::with(['items.item', 'items.supplier', 'laborer'])->paginate(20);

		return view('inventory.transactions', [
			'transactions' => $transactions, 
			'laborers' => $laborers, 
			'suppliers' => json_encode($suppliers), 
			'items' => json_encode($items)
		]);
	}

	public function transaction(Request $request) {
		$laborer = $request->get('laborer');
		$remarks = $request->get('remarks');
		$items = json_decode($request->get('items'));
		$code = date("Y").date("m").date("d").uniqid(true);

		$transaction = Transaction::create([
			'transaction_code' => $code,
			'total_quantity' => 0,
			'total_amount' => 0,
			'laborer_id' => $laborer,
			'remarks' => $remarks
		]);

		$totalCost = 0;
		$totalQuantity = 0;
		foreach($items as $item) {
			$totalCost += (float)$item->cost;
			$totalQuantity += (int)$item->quantity;
			$itemTotalCost = (float)$item->cost * (int)$item->quantity;

			TransactionItem::create([
				'transaction_id' => $transaction->id,
				'item_id' => $item->id,
				'supplier_id' => $item->supplier,
				'quantity' => $item->quantity,
				'amount' => $item->cost,
				'total_amount' => $itemTotalCost,
			]);
		}

		$transaction->total_quantity = $totalQuantity;
		$transaction->total_amount = $totalCost;
		$transaction->save();

		return redirect()->back()->with('success', 'Transaction has been added!'); 
	}
}
