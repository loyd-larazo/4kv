<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

use App\Models\Category;
use App\Models\Item;
use App\Models\Supplier;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\DailySale;
use Carbon\Carbon;

use DNS1D;

class ItemController extends Controller
{
  public function items(Request $request) {
    $search = $request->get('search');
    $status = $request->get('status');
    $page = $request->get('page') ?? 1;
		$status = $status == null ? 1 : $status;
		$isZeroStock = 0;

		if ($status < 0) {
			$isZeroStock = 1; 
			$status = null;
		}

    $categories = Category::where('status', '1')
													->get();

    Paginator::currentPageResolver(function() use ($page) {
      return $page;
    });

    $items = Item::with('category')
								->when($search, function($query) use ($search) {
									$query->where('sku', 'like', "%$search%")
												->orWhere('name', 'like', "%$search%");
								})
                ->when(isset($status), function($query) use ($status) {
									$query->where('status', $status);
								})
                ->when(isset($isZeroStock), function($query) use ($isZeroStock, $status) {
                  if ($isZeroStock) {
                    $query->where('stock', 0);
                  } else if (!$isZeroStock && $status > 0) {
                    $query->where('stock', '>', 0);
                  }
                })
								->orderBy('created_at', 'DESC')
								->paginate(20);

		return view('inventory.items', [
			'items' => $items, 
			'categories' => $categories, 
			'search' => $search,
			'status' => $status,
			'isZeroStock' => $isZeroStock,
		]);
	}

  public function updateOrCreateItem(Request $request) {
		$id = $request->get('id');
		$name = $request->get('name');
		$cost = $request->get('cost');
		$price = $request->get('price');
		$description = $request->get('description');
		$category = $request->get('category');
		$sold_by = $request->get('sold_by');
		$stock = $request->get('stock') ?? 0;
		$status = $request->get('status');
		$isAjax = $request->get('isAjax');
		
		$data = [
			'name' => $name,
      'cost' => $cost,
      'price' => $price,
      'description' => $description,
      'category_id' => $category,
      'sold_by_weight' => $sold_by == 'weight' ? 1 : 0,
      'sold_by_length' => $sold_by == 'length' ? 1 : 0,
      'status' => $status,
		];

		if (isset($id)) {
			Item::where('id', $id)
						->update($data);
		} else {
      $data['stock'] = $stock;
      $data['sku'] = uniqid(true);
			Item::create($data);
		}

		if ($isAjax) {
			$items = Item::select('id', 'sku', 'name', 'price', 'cost', 'stock', 'category_id', 'sold_by_weight', 'sold_by_length')
										->where('status', 1)
										->get();
			return response()->json(['data' => $items]);
		}
		return redirect()->back()->with('success', 'Item has been saved!'); 
	}

  public function generateBarcode(Request $request, $sku) {
    // echo DNS1D::getBarcodeSVG($sku, 'C39', 1, 70);
    return view('inventory.barcode', ['sku' => $sku]);
  }

  public function categories(Request $request) {
		$search = $request->get('search');
    $status = $request->get('status') == null ? 1 : $request->get('status');
    $page = $request->get('page') ?? 1;
    Paginator::currentPageResolver(function() use ($page) {
      return $page;
    });

		$categories = Category::when($search, function($query) use ($search) {
														$query->where('name', 'like', "%$search%");
													})
													->where('status', $status)
													->orderBy('created_at', 'DESC')
													->paginate(20);

		return view('inventory.categories', [
      'categories' => $categories, 
      'search' => $search,
      'status' => $status,
    ]);
	}

  public function updateOrCreateCategory(Request $request) {
		$id = $request->get('id');
		$name = $request->get('name');
		$status = $request->get('status');
		
		$data = [
			'name' => $name,
			'status' => $status,
		];

		if (isset($id)) {
			Category::where('id', $id)
						->update($data);
		} else {
			Category::create($data);
		}

		return redirect()->back()->with('success', 'Category has been saved!'); 
	}
	
	public function destroyCategory(Request $request, $categoryId) {
    Item::where('category_id', $categoryId)
        ->update(['category_id' => null]);

		Category::where('id', $categoryId)->delete();

		return redirect()->back()->with('success', 'Category has been deleted!'); 
	}

  public function suppliers(Request $request) {
		$search = $request->get('search');
    $page = $request->get('page') ?? 1;
    $status = $request->get('status') == null ? 1 : $request->get('status');
    Paginator::currentPageResolver(function() use ($page) {
      return $page;
    });

		$suppliers = Supplier::when($search, function($query) use ($search) {
														$query->where(function($query) use ($search) {
															$query->where('name', 'like', "%$search%")
																		->orWhere('contact_person', 'like', "%$search%")
																		->orWhere('contact_number', 'like', "%$search%")
																		->orWhere('address', 'like', "%$search%");
														});
													})
													->where('status', $status)
													->orderBy('created_at', 'DESC')
													->paginate(20);

		return view('inventory.suppliers', [
      'suppliers' => $suppliers, 
      'search' => $search, 
      'status' => $status
    ]);
	}

  public function updateOrCreateSupplier(Request $request) {
		$id = $request->get('id');
		$name = $request->get('name');
		$contact_person = $request->get('contact_person');
		$contact_number = $request->get('contact_number');
		$address = $request->get('address');
		$status = $request->get('status');
		
		$data = [
			'name' => $name,
      'contact_person' => $contact_person,
      'contact_number' => $contact_number,
      'address' => $address,
      'status' => $status,
		];

		if (isset($id)) {
			Supplier::where('id', $id)
						->update($data);
		} else {
			Supplier::create($data);
		}

		return redirect()->back()->with('success', 'Supplier has been saved!'); 
	}

	public function validateProductName(Request $request, $categoryId) {
		$name = $request->get('name');
		$sku = $request->get('sku');

		$item = Item::where('name', $name)
								->where('category_id', $categoryId)
								->when($sku, function($query) use ($sku) {
									$query->whereNot('sku', $sku);
								})
								->first();

		return response()->json(['data' => $item ? true : false]);
	}

	public function returnItems(Request $request) {
		$page = $request->get('page') ?? 1;
		$search = $request->get('search');

		$today = Carbon::today();
    $dailySale = DailySale::whereDate('created_at', $today)->first();

		Paginator::currentPageResolver(function() use ($page) {
      return $page;
    });

		$returns = Sale::when($search, function($query) use ($search) {
										$query->where('reference', $search);
									})
									->orWhereHas('items', function($query) use ($search) {
										$query->whereHas('item', function($query) use ($search) {
											if ($search) {
												$query->where('name', 'like', "%$search%");
											}
										});
									})
									->where('type', 'return')
									->with('items.item', 'user')
									->orderBy('created_at', 'desc')
									->paginate(20);

		$sales = Sale::where('type', 'sales')
								->with(['items.item'])
								->get();

		return view('pos.return_items', [
			'returns' => $returns,
			'sales' => json_encode($sales),
			'dailySale' => $dailySale
    ]);
	}

	public function saveReturnItems(Request $request, $salesId) {
		$items = json_decode($request->get('items'));
		$totalQty = $request->get('totalQty');
		$totalAmount = $request->get('totalAmount');
		$user = $request->get('user');

		$today = Carbon::today();
    $dailySale = DailySale::whereDate('created_at', $today)->first();

		$sales = Sale::where('id', $salesId)->first();
		$returnSale = Sale::create([
			'user_id' => $user->id,
			'daily_sale_id' => $dailySale->id,
			'reference' => $sales->reference,
			'total_quantity' => $totalQty,
			'total_amount' => $totalAmount,
			'paid_amount' => 0,
			'change_amount' => 0,
			'type' => 'return'
		]);

		foreach ($items as $item) {
			SaleItem::create([
				'sale_id' => $returnSale->id,
				'item_id' => $item->item_id,
				'quantity' => $item->quantity,
				'amount' => $item->amount,
				'total_amount' => $item->total_amount,
				'type' => 'return'
			]);
		}

		return redirect()->back()->with('success', 'Items has been returned!'); 
	}
}
