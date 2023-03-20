<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\Category;
use App\Models\Item;
use App\Models\Supplier;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\DamageItem;
use App\Models\DailySale;
use App\Models\Transaction;

use Carbon\Carbon;

class ReportController extends Controller
{
  private $columns;
  private $dbCols;
  public function __construct() {
    $this->columns = [
      'topSelling' => [
        1 => 'sku',
        2 => 'item',
        3 => 'sold'
      ],
      'soldItems' => [
        1 => 'sku',
        2 => 'item',
        3 => 'price',
        4 => 'sold',
        5 => 'total_price'
      ],
      'inventory' => [
        1 => 'sku',
        2 => 'item',
        3 => 'cost',
        4 => 'price',
        5 => 'description',
        6 => 'category',
        7 => 'sold_by_length',
        8 => 'sold_by_weight',
        9 => 'stock'
      ],
      'dailySales' => [
        1 => 'date',
        2 => 'opening_cashier_firstname',
        3 => 'opening_cashier_lastname',
        4 => 'closing_cashier_firstname',
        5 => 'closing_cashier_lastname',
        6 => 'sales_count',
        7 => 'sales_amount',
        8 => 'opening_amount',
        9 => 'closing_amount',
        10 => 'discrepancy'
      ],
      'damageItems' => [
        1 => 'sku',
        2 => 'item',
        3 => 'price',
        4 => 'quantity',
        5 => 'total_price'
      ],
      'lowStock' => [
        1 => 'sku',
        2 => 'item',
        3 => 'stock'
      ],
      'transaction' => [
        1 => 'transaction_code',
        2 => 'total_quantity',
        3 => 'total_cost',
        4 => 'stock_man',
        5 => 'remarks',
        6 => 'date'
      ],
      'sales' => [
        1 => 'reference',
        2 => 'cashier_firstname',
        3 => 'cashier_lastname',
        4 => 'total_quantity',
        5 => 'total_discount',
        6 => 'total_amount',
        7 => 'date'
      ]
    ];

    $this->dbCols = [
      'topSelling' => [
        'sku' => 'item.sku',
        'item' => 'item.name',
        'sold' => 'sold' 
      ],
      'soldItems' => [
        'sku' => 'item.sku',
        'item' => 'item.name',
        'price' => 'item.price',
        'sold' => 'sold',
        'total_price' => 'total_price'
      ],
      'inventory' => [
        'sku' => 'sku',
        'item' => 'name',
        'cost' => 'cost',
        'price' => 'price',
        'description' => 'description',
        'category' => 'category.name',
        'sold_by_length' => 'sold_by_length',
        'sold_by_weight' => 'sold_by_weight',
        'stock' => 'stock',
      ],
      'dailySales' => [
        'date' => 'created_at',
        'opening_cashier_firstname' => 'opening_user.firstname',
        'opening_cashier_lastname' => 'opening_user.lastname',
        'closing_cashier_firstname' => 'closing_user.firstname',
        'closing_cashier_lastname' => 'closing_user.lastname',
        'sales_count' => 'sales_count',
        'sales_amount' => 'sales_amount',
        'opening_amount' => 'opening_amount',
        'closing_amount' => 'closing_amount',
        'discrepancy' => 'difference_amount',
      ],
      'damageItems' => [
        'sku' => 'item.sku',
        'item' => 'item.name',
        'price' => 'item.price',
        'quantity' => 'quantity',
        'total_price' => 'total_price',
      ],
      'lowStock' => [
        'sku' => 'sku',
        'item' => 'name',
        'stock' => 'stock',
      ],
      'transaction' => [
        'transaction_code' => 'transaction_code',
        'total_quantity' => 'total_quantity',
        'total_cost' => 'total_amount',
        'stock_man' => 'stock_man',
        'remarks' => 'remarks',
        'date' => 'created_at'
      ],
      'sales' => [
        'reference' => 'reference',
        'cashier_firstname' => 'user.firstname',
        'cashier_lastname' => 'user.lastname',
        'total_quantity' => 'total_quantity',
        'total_discount' => 'total_discount',
        'total_amount' => 'total_amount',
        'date' => 'created_at'
      ]
    ];
  }

  public function index(Request $request) {
    return view('reports', [
      'columns' => $this->columns,
      'dbCols' => $this->dbCols
    ]);
  }

  public function loadData(Request $request) {
    $rptType = $request->get('rptType');
    $sDate = $request->get('sDate');
    $eDate = $request->get('eDate');

    $data = $this->getData($rptType, $sDate, $eDate);
    return response()->json([
      'data' => $data['data'],
      'grandTotal' => $data['grandTotal']
    ]);
  }

  private function getData($rptType, $sDate, $eDate) {
    $sDate = Carbon::parse($sDate)->startOfDay()->subHours(8)->format('Y-m-d H:i:s');
    $eDate = Carbon::parse($eDate)->endOfDay()->subHours(8)->format('Y-m-d H:i:s');
    $grandTotal = null;

    switch($rptType) {
      case('topSelling'):
        $topSelling = 100;
        $data = SaleItem::select( 'item_id', DB::raw('sum(quantity) as sold') )
                        ->with('item')
                        ->whereBetween('created_at', [$sDate, $eDate])
                        ->havingRaw("sold >= $topSelling")
                        ->groupBy('item_id')
                        ->orderBy('sold', 'DESC')
                        ->get();
        break;
      case('soldItems'):
        $data = SaleItem::select( 'item_id', 
                                  DB::raw('sum(quantity) as sold'), 
                                  DB::raw('sum(total_amount) as total_price') )
                        ->with('item')
                        ->whereBetween('created_at', [$sDate, $eDate])
                        ->groupBy('item_id')
                        ->orderBy('sold', 'DESC')
                        ->get();
        if (count($data)) $grandTotal = [
          'total_price' => $data->sum('total_price'),
          'price' => $data->sum('item.price')
        ];
        break;
      case('inventory'):
        $data = Item::with('category')
                    ->where('status', 1)
                    ->whereBetween('created_at', [$sDate, $eDate])
                    ->orderBy('name', 'ASC')
                    ->get();
        if (count($data)) $grandTotal = ['cost' => $data->sum('cost'), 'price' => $data->sum('price')];
        break;
      case('dailySales'):
        $data = DailySale::with(['openingUser', 'closingUser'])
                        ->whereBetween('created_at', [$sDate, $eDate])
                        ->whereNotNull('closing_amount')
                        ->orderBy('created_at', 'desc')
                        ->get();
        if (count($data)) $grandTotal = [
          'sales_amount' => $data->sum('sales_amount'),
          'opening_amount' => $data->sum('opening_amount'),
          'closing_amount' => $data->sum('closing_amount'),
          'difference_amount' => $data->sum('difference_amount')
        ];
        break;
      case('damageItems'):
        $data = DamageItem::select( 'item_id', 
                                    DB::raw('sum(quantity) as quantity'), 
                                    DB::raw('sum(total_amount) as total_price') )
                          ->with('item')
                          ->whereBetween('created_at', [$sDate, $eDate])
                          ->groupBy('item_id')
                          ->orderBy('quantity', 'DESC')
                          ->get();
        if (count($data)) $grandTotal = [
          'total_price' => $data->sum('total_price'),
          'price' => $data->sum('item.price')
        ];
        break;
      case('lowStock'):
        $data = Item::where('stock', '<=', 15)
                    ->whereBetween('created_at', [$sDate, $eDate])
                    ->orderBy('stock', 'DESC')
                    ->get();
        break;
      case('transaction'):
        $data = Transaction::whereBetween('created_at', [$sDate, $eDate])
                          ->orderBy('created_at', 'DESC')
                          ->get();
        if (count($data)) $grandTotal = [
          'total_amount' => $data->sum('total_amount')
        ];
        break;
      case('sales'):
        $data = Sale::where('type', 'sales')
                    ->with('user')
                    ->whereBetween('created_at', [$sDate, $eDate])
                    ->orderBy('created_at', 'DESC')
                    ->get();
        if (count($data)) $grandTotal = [
          'total_discount' => $data->sum('total_discount'),
          'total_amount' => $data->sum('total_amount')
        ];
        break;
      default:
        $data = [];
        $grandTotal = null;
    }
    return ['data' => $data, 'grandTotal' => $grandTotal];
  }

  public function print(Request $request, $type) {
    $sDate = $request->get('sDate');
    $eDate = $request->get('eDate');
    $indexes = $request->get('items');
    $indexes = explode(',',$indexes);
    $cols = array();

    $data = $this->getData($type, $sDate, $eDate);
    $params = [
      'data' => $data['data'],
      'cols' => $indexes,
      'grandTotal' => $data['grandTotal']
    ];

    switch($type) {
      case('topSelling'):
        return view('report.top_selling_rpt', $params);
        break;
      case('soldItems'):
        return view('report.sold_items_rpt', $params);
        break;
      case('inventory'):
        return view('report.inventory_rpt', $params);
        break;
      case('dailySales'):
        return view('report.daily_sales_rpt', $params);
        break;
      case('damageItems'):
        return view('report.damage_items_rpt', $params);
        break;
      case('lowStock'):
        return view('report.low_stock_rpt', $params);
        break;
      case('transaction'):
        return view('report.transaction_rpt', $params);
        break;
      case('sales'):
        return view('report.sales_rpt', $params);
        break;
      default:
        return redirect()->back()->with('error', 'No report type selected.'); 
        break;
    }
  }
}
