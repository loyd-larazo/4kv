<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use App\Models\Category;
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

    $transactions = Transaction::with(['items.supplier', 'items.item'])
																->when($search, function($query) use ($search) {
																	$query->where('transaction_code', 'like', "%$search%")
																				->orWhere('stock_man', 'like', "%$search%")
                                        ->orWhere('remarks', 'like', "%$search%");
																})
																->orWhereHas('items', function($query) use ($search) {
																	$query->whereHas('item', function($query) use ($search) {
																		if ($search) {
																			$query->where('name', 'like', "%$search%");
																		}
																	});
																})
                                ->orderBy('created_at', 'desc')
                                ->paginate(20);

		return view('inventory.transactions', [
			'transactions' => $transactions,
			'search' => $search
		]);
	}

  public function addTransactionPage(Request $request) {
    $user = $request->get('user');
    $suppliers = Supplier::where('status', 1)->get();
		$items = Item::select('id', 'sku', 'name', 'price', 'cost', 'stock', 'category_id', 'sold_by_weight', 'sold_by_length')
                ->where('status', 1)
                ->where('stock', '>', 0)
                ->get();
    $categories = Category::orderBy('name', 'asc')->get();

    return view('inventory.add_transaction', [
      'user' => $user,
			'suppliers' => json_encode($suppliers), 
			'items' => json_encode($items),
			'categories' => json_encode($categories),
		]);
  }

	public function transaction(Request $request) {
		$user = $request->get('user');
		$remarks = $request->get('remarks');
		$items = json_decode($request->get('items'));
		$code = strtoupper(date("Y").date("m").date("d").uniqid(true));

		$transaction = Transaction::create([
			'transaction_code' => $code,
      'user_id' => $user->id,
      'stock_man' => $user->firstname . " " . $user->lastname,
			'total_quantity' => 0,
			'total_amount' => 0,
			'remarks' => $remarks
		]);

		$totalCost = 0;
		$totalQuantity = 0;
		foreach($items as $item) {
      if ($item->sold_by_weight || $item->sold_by_length) {
        $totalQuantity += (float)$item->quantity;
        $itemTotalCost = (float)$item->cost * (float)$item->quantity;
      } else {
        $totalQuantity += (int)$item->quantity;
        $itemTotalCost = (float)$item->cost * (int)$item->quantity;
      }

      $totalCost += $itemTotalCost;

			TransactionItem::create([
				'transaction_id' => $transaction->id,
				'item_id' => $item->id,
				'supplier_id' => $item->supplier_id,
				'quantity' => $item->quantity,
				'amount' => $item->cost,
				'total_amount' => $itemTotalCost,
			]);

      $itemModel = Item::where('id', $item->id)->first();
      if ($itemModel->sold_by_weight || $itemModel->sold_by_length) {
        $itemModel->stock = (float)$itemModel->stock + (float)$item->quantity;
      } else {
        $itemModel->stock = (int)$itemModel->stock + (int)$item->quantity;
      }
      
      $itemModel->save();
		}

		$transaction->total_quantity = $totalQuantity;
		$transaction->total_amount = $totalCost;
		$transaction->save();

		return redirect()->back()->with('success', 'Transaction has been added!'); 
	}
}
