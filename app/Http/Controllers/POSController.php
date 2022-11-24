<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

use App\Models\Item;
use App\Models\Sale;
use App\Models\SaleItem;

class POSController extends Controller
{
  public function sell(Request $request) {
    $isSuccess = $request->get('success') ? $request->get('success') : null;

    $items = Item::select('id', 'sku', 'name', 'price', 'stock')->get();

    return view('pos.sell', [
      'items' => json_encode($items),
      'success' => $isSuccess
    ]);
  }

  public function sales(Request $request) {
    $page = $request->get('page') ?? 1;
    $date = $request->get('date') ? $request->get('date') : date('Y-m-d');
    Paginator::currentPageResolver(function() use ($page) {
      return $page;
    });

    $sales = Sale::when($date, function($query) use ($date) {
                    $query->whereDate('created_at', $date);
                  })
                  ->with('items.item')
                  ->orderBy('created_at', 'desc')->paginate(20);

    return view('pos.sales', [
      'sales' => $sales,
      'date' => $date
    ]);
  }

  public function saveSales(Request $request) {
    $items = json_decode($request->get('items'));
    $totalQuantity = $request->get('totalQuantity');
    $totalPrice = $request->get('totalPrice');
    
		$code = strtoupper("S".date("Y").date("m").date("d").uniqid(true));

    $sale = Sale::create([
      'reference' => $code,
      'total_quantity' => $totalQuantity,
      'total_amount' => $totalPrice
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

    return redirect('/sell?success='.$sale->id); 
  }

  public function printSale(Request $request, $saleId) {
    $sale = Sale::where('id', $saleId)->with('items.item')->first();

    return view('pos.receipt', ['sale' => $sale]);
  }
}
