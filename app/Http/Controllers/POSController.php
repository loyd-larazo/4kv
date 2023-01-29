<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

use App\Models\Item;
use App\Models\Category;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\DailySale;

use Carbon\Carbon;

class POSController extends Controller
{
  public function cashier(Request $request) {
    $isSuccess = $request->get('success') ? $request->get('success') : null;
    $user = $request->get('user');

    $today = Carbon::today();
    $dailySale = DailySale::whereDate('created_at', $today)->first();
    if (!$dailySale || ($dailySale && $dailySale->closing_amount)) {
      return view('pos.open_cashier', [
        'user' => $user,
        'isClosed' => $dailySale && $dailySale->closing_amount ? true : false
      ]);
    }

    $items = Item::select('id', 'sku', 'name', 'price', 'stock', 'category_id', 'sold_by_weight', 'sold_by_length')
                ->where('status', 1)
                ->where('stock', '>', 0)
                ->get();
    $categories = Category::orderBy('name', 'asc')->get();

    return view('pos.cashier', [
      'categories' => json_encode($categories),
      'items' => json_encode($items),
      'success' => $isSuccess,
      'dailySale' => $dailySale
    ]);
  }

  public function openCashier(Request $request) {
    $user = $request->get('user');
    $amount = $request->get('amount');

    DailySale::create([
      'opening_user_id' => $user->id,
      'opening_amount' => $amount
    ]);

    return redirect('/cashier');
  }

  public function closeCashier(Request $request) {
    $user = $request->get('user');
    $closingAmount = $request->get('closingAmount');

    $today = Carbon::today();
    $dailySale = DailySale::with(['sales'])->whereDate('created_at', $today)->first();

    if ($dailySale) {
      $totalDailyAmount = 0;
      foreach ($dailySale->sales as $sale) {
        $totalDailyAmount = $totalDailyAmount + $sale->total_amount;
      }

      $dailySale->closing_user_id = $user->id;
      $dailySale->closing_amount = $closingAmount;
      $dailySale->sales_count = $dailySale->sales->count();
      $dailySale->sales_amount = $totalDailyAmount;
      $dailySale->difference_amount = $closingAmount - ($totalDailyAmount + $dailySale->opening_amount);
      $dailySale->save();
    }

    return redirect('/cashier');
  }

  public function sales(Request $request) {
    $page = $request->get('page') ?? 1;
    $search = $request->get('search');
    $date = $request->get('date') ? $request->get('date') : ($search ? null : date('Y-m-d'));
    Paginator::currentPageResolver(function() use ($page) {
      return $page;
    });

    $sales = Sale::where('type', 'sales')
                  ->where(function($query) use ($date, $search) {
                    $query->when($date, function($query) use ($date) {
                      $query->whereDate('created_at', $date);
                    })
                    ->when($search, function($query) use ($search) {
                      $query->where('reference', $search);
                    })
                    ->orWhereHas('items', function($query) use ($search) {
                      $query->whereHas('item', function($query) use ($search) {
                        if ($search) {
                          $query->where('name', 'like', "%$search%");
                        }
                      });
                    });
                  })
                  ->with('items.item', 'user')
                  ->orderBy('created_at', 'desc')->paginate(20);

    return view('pos.sales', [
      'sales' => $sales,
      'date' => $date,
      'search' => $search
    ]);
  }

  public function saveSales(Request $request) {
    $user = $request->get('user');
    $items = json_decode($request->get('items'));
    $totalQuantity = $request->get('totalQuantity');
    $totalPrice = $request->get('totalPrice');
    $amount = $request->get('amount');
    
		$code = strtoupper("S".date("Y").date("m").date("d").uniqid(true));
    $today = Carbon::today();
    $dailySale = DailySale::whereDate('created_at', $today)->first();

    $sale = Sale::create([
      'user_id' => $user->id,
      'daily_sale_id' => $dailySale->id,
      'reference' => $code,
      'total_quantity' => $totalQuantity,
      'total_amount' => $totalPrice,
      'paid_amount' => (float)$amount,
      'change_amount' => (float)$amount - (float)$totalPrice
    ]);

    foreach($items as $item) {
      SaleItem::create([
        'sale_id' => $sale->id,
        'item_id' => $item->id,
        'quantity' => $item->quantity,
        'amount' => $item->price,
        'total_amount' => (float)$item->quantity * (float)$item->price
      ]);

      $itemModel = Item::where('id', $item->id)->first();
      $itemModel->stock = $itemModel->stock - $item->quantity;
      $itemModel->save();
    }

    return redirect('/cashier?success='.$sale->id); 
  }

  public function printSale(Request $request, $saleId) {
    $sale = Sale::where('id', $saleId)->with('items.item')->first();
    $taxPercent = 12 / 100;
    $vat = $sale->total_amount * $taxPercent;

    return view('pos.receipt', ['sale' => $sale, 'vat' => $vat]);
  }

  public function dailySales(Request $request) {
    $page = $request->get('page') ?? 1;
    Paginator::currentPageResolver(function() use ($page) {
      return $page;
    });

    $dailySales = DailySale::with(['openingUser', 'closingUser'])
                          ->whereNotNull('closing_amount')
                          ->orderBy('created_at', 'desc')
                          ->paginate(20);

    return view('pos.daily_sales', ['dailySales' => $dailySales]);
  }
}
