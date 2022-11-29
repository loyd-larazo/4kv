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
		$search = $request->get('search');
    Paginator::currentPageResolver(function() use ($page) {
      return $page;
    });

		$suppliers = Supplier::where('status', 1)->get();
		$laborers = Laborer::get();
		$items = Item::select('id', 'sku', 'name', 'cost')->where('status', 1)->get();

    $transactions = Transaction::with(['items.item', 'items.supplier'])
																->when($search, function($query) use ($search) {
																	$query->where('transaction_code', 'like', "%$search%")
																				->orWhere('laborer', 'like', "%$search%");
																})
                                ->orderBy('created_at', 'desc')
                                ->paginate(20);

		return view('inventory.transactions', [
			'transactions' => $transactions, 
			'laborers' => $laborers, 
			'suppliers' => json_encode($suppliers), 
			'items' => json_encode($items),
			'search' => $search
		]);
	}

	public function transaction(Request $request) {
		$laborer = $request->get('laborer');
		$remarks = $request->get('remarks');
		$items = json_decode($request->get('items'));
		$code = strtoupper(date("Y").date("m").date("d").uniqid(true));
		$laborerModel = Laborer::where('id', $laborer)->first();

		$transaction = Transaction::create([
			'transaction_code' => $code,
			'total_quantity' => 0,
			'total_amount' => 0,
			'laborer_id' => $laborer,
			'laborer' => $laborerModel->firstname." ".$laborerModel->lastname,
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

      $itemModel = Item::where('id', $item->id)->first();
      $itemModel->stock = (int)$itemModel->stock + (int)$item->quantity;
      $itemModel->save();
		}

		$transaction->total_quantity = $totalQuantity;
		$transaction->total_amount = $totalCost;
		$transaction->save();

		return redirect()->back()->with('success', 'Transaction has been added!'); 
	}
}
