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
use App\Models\ReturnTransaction;
use App\Models\DiscardItem;

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
                ->get();
    $categories = Category::where('status', 1)->orderBy('name', 'asc')->get();

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

	public function returnTransactions(Request $request) {
		$page = $request->get('page') ?? 1;
		$search = $request->get('search');

    Paginator::currentPageResolver(function() use ($page) {
      return $page;
    });

		$transactions = Transaction::with(['items.item', 'items.supplier'])->whereHas('items')->get();

		$returnTransactions = ReturnTransaction::with(['user', 'transaction', 'transactionItems.item', 'transactionItems.supplier'])
																					->whereHas('transactionItems', function ($query) {
																						$query->whereNotNull('return_transaction_id');
																					})
																					->when($search, function ($query) use ($search) {
																						$query->whereHas('transaction', function($query) use ($search) {
																							$query->where('transaction_code', 'like', "%$search%");
																						});
																					})
																					->orderBy('created_at', 'DESC')
																					->paginate(20);

    return view('inventory.return_transaction', [
			'search' => $search,
			'returnTransactions' => $returnTransactions,
			'transactions' => json_encode($transactions)
		]);
	}

	public function returnTransaction(Request $request) {
		try {
			$user = $request->get('user');
			$transId = $request->get('transId');
			$items = $request->get('items');
			$items = json_decode($items);
			$totalReturnQty = (int)$request->get('totalReturnQty');
			$totalReturnAmount = (double)$request->get('totalReturnAmount');

			if (count($items) > 0) {
				$returnTransaction = ReturnTransaction::create([
					'user_id' => $user->id,
					'transaction_id' => $transId,
					'quantity' => $totalReturnQty,
					'total_amount' => $totalReturnAmount,
					'status' => 'pending'
				]);
	
				foreach($items as $item) {
					$transItem = TransactionItem::where('id', $item->transItemId)->first();
					$transItem->return_transaction_id = $returnTransaction->id;
					$transItem->return_quantity = $item->returnQty;
					$transItem->return_total_amount = $item->total_amount;
					$transItem->save();

					$itemModel = Item::where('id', $transItem->item_id)->first();
					$itemModel->stock = $itemModel->stock - $item->returnQty;
					$itemModel->save();
				}
				return redirect()->back()->with('success', 'Returned Purchased Items!');	
			} else {
				return redirect()->back()->with('success', 'No Items to Return.');	
			}
		} catch (\Exception $e) {
			return redirect()->back()->with('error', 'Something went wrong!');
		}
	}

	public function returnTransactionStatus(Request $request, $returnTransactionId) {
		try {
			$status = $request->get('status');
			ReturnTransaction::where('id', $returnTransactionId)->update(['status' => $status]);

			if ($status === 'discard') {
				$user = $request->get('user');
				$transItems = json_decode($request->get('transItems'));

				if (count($transItems) > 0) {
					foreach($transItems as $transItem) {
						DiscardItem::create([
							'user_id' => $user->id,
							'item_id' => $transItem->item_id,
							'supplier_id' => $transItem->supplier_id,
							'quantity' => $transItem->return_quantity,
							'amount' => $transItem->amount,
							'total_amount' => $transItem->total_amount
						]);
					}
					return redirect()->back()->with('success', 'Discarded items!');	
				}
				return redirect()->back()->with('success', 'No items to be discard.');	
			}
			return redirect()->back()->with('success', 'Picked up!');
		} catch (\Exception $e) {
			return redirect()->back()->with('error', 'Something went wrong!');
		}
	}
}
