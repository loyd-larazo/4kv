<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\Paginator;

use App\Models\Setting;
use App\Models\User;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Item;

use Carbon\Carbon;

class AppController extends Controller
{
  public function loginPage(Request $request) {
    $email = Setting::where('key', 'email')->first();
    $admin = User::where(['status' => 1, 'type' => 'admin'])->first();

    return view("login", [
      'email' => $email->value,
      'admin' => $admin
    ]);
  }

  public function login(Request $request) {
    $username = $request->get('username');
    $password = $request->get('password');

    $email = Setting::where('key', 'email')->first();
    $admin = User::where(['status' => 1, 'type' => 'admin'])->first();

    $user = User::where(DB::raw('BINARY `username`'), $username)
                ->where('status', 1)
                ->first();
    
    if (!$user) {
      return view('/login', [
        'error' => 'Invalid credentials or no user account.', 
        'username' => $username, 
        'email' => $email->value,
        'admin' => $admin
      ]);
    }

    if (!Hash::check($password, $user->password)) {
      return view('/login', [
        'error' => 'Invalid credentials!', 
        'username' => $username,
        'email' => $email->value,
        'admin' => $admin
      ]);
    }

    // Set session
    $settingWarningLimit = Setting::where('key', 'warning_limit')->first();
    $request->session()->put('user', $user);
    $request->session()->put('warning_limit', $settingWarningLimit->value);

    return redirect('/');
  }

  public function logout(Request $request) {
    $request->session()->flush();

    return redirect('/login');
  }

  public function index(Request $request) {
    $page = $request->get('page') ?? 1;
    $reportBy = $request->get('reportBy') ?? 'Daily';
    $topSellingFilter = $request->get('topSelling') ?? 'Daily';
    // $limit = (int)$request->session()->get('warning_limit');
    $limit = 15;

    Paginator::currentPageResolver(function() use ($page) {
      return $page;
    });

    $sales = [];
    if ($reportBy == 'Daily') {
      $last7Days = Carbon::today()->subDays(29);
      $sales = Sale::select(
                      DB::raw('sum(total_amount) as y'), 
                      DB::raw("DATE_FORMAT(created_at,'%m %d, %Y') as label"),
                    )
                  ->where('type', 'sales')
                  ->whereDate('created_at', '>=', $last7Days)
                  ->groupBy('label')
                  ->orderBy('label')
                  ->limit(30)
                  ->get();
    } else if ($reportBy == 'Weekly') {
      $today = Carbon::now();
      $year = $today->year;
      $month = $today->month;
      $weeks = $this->monthToWeeks($year, $month);
      $salesWeek = [];
      foreach ($weeks as $key => $week) {
        $weekCount = $key + 1;
        $salesWeek[] = Sale::select(
                            DB::raw('sum(total_amount) as y'),
                            DB::raw("CONCAT('Week ', $weekCount) as label")
                          )
                          ->where('type', 'sales')
                          ->whereBetween('created_at', [$week[0], $week[1]])
                          ->first();
      }
      $sales = $salesWeek;
    } else if ($reportBy == 'Monthly') {
      $sales = Sale::select(
                      DB::raw('sum(total_amount) as y'), 
                      DB::raw("DATE_FORMAT(created_at,'%m %Y') as label"),
                    )
                    ->where('type', 'sales')
                    ->groupBy('label')
                    ->orderBy('label')
                    ->limit(12)
                    ->get();
    } else if ($reportBy == 'Quarterly') {
      $today = Carbon::now();
      $year = $today->year;
      $months = [
        ['from' => '01', 'to' => '03', 'days' => 31],
        ['from' => '04', 'to' => '06', 'days' => 30],
        ['from' => '07', 'to' => '09', 'days' => 30],
        ['from' => '10', 'to' => '12', 'days' => 31],
      ];

      $salesQuarter = [];
      foreach ($months as $key => $month) {
        $quarter = $key + 1;
        $startDate = $year."-".$month['from']."-01";
        $endDate = $year."-".$month['to']."-".$month['days'];
        $salesQuarter[] = Sale::select(
                                DB::raw('sum(total_amount) as y'),
                                DB::raw("CONCAT('Quarter ', $quarter) as label")
                              )
                              ->where('type', 'sales')
                              ->whereBetween('created_at', [$startDate, $endDate])
                              ->first();
      }

      $sales = $salesQuarter;
    } else if ($reportBy == 'Yearly') {
      $sales = Sale::select(
                      DB::raw('sum(total_amount) as y'), 
                      DB::raw("DATE_FORMAT(created_at, '%Y') as label"),
                    )
                    ->where('type', 'sales')
                    ->groupBy('label')
                    ->orderBy('label')
                    ->limit(12)
                    ->get();
    }

    $lowStockItems = Item::where('stock', '<=', $limit)->paginate(20);
    $topSellingItems = $this->pullTopSellingItems($topSellingFilter);

    return view('home', [
      'reportBy' => $reportBy,
      'sales' => json_encode($sales),
      'lowStocks' => $lowStockItems,
      'topSelling' => $topSellingItems,
      'topSellingFilter' => $topSellingFilter
    ]);
  }

  public function settings(Request $request) {
    $type = $request->get('type');
    $user = $request->session()->get('user');
    $warningLimit = $request->session()->get('warning_limit');

    if ($type == 'email') {
      $email = Setting::where('key', 'email')->first();
      return response()->json(['data' => $email]);
    }

    return view('settings', [
      'username' => $user, 
      'warning_limit' => $warningLimit
    ]);
  }

  public function updateSettings(Request $request) {
    $username = $request->get('username');
    $password = $request->get('password');
    $warningLimit = $request->get('warning-limit');
    $email = $request->get('email');

    if (isset($email)) {
      Setting::updateOrCreate(
        ['key' => 'email'],
        ['value' => $email]
      );

      return redirect()->back()->with('success', "Admin's email has been updated!"); 
    }

    if (isset($username)) {
      Setting::where('key', 'username')
            ->update(['value' => $username]);

      $request->session()->put('user', $username);
    }
    
    if (isset($password)) {
      Setting::where('key', 'password')
            ->update(['value' => app('hash')->make($password)]);
    }

    if (isset($warningLimit)) {
      Setting::where('key', 'warning_limit')
            ->update(['value' => $warningLimit]);
      $request->session()->put('warning_limit', $warningLimit);
    }

    return redirect()->back()->with('success', 'Settings has been updated!'); 
  }

  private function monthToWeeks($y, $m)
  {
      $weeks = [];
      $month = $m;
      $first_date = date("{$y}-{$m}-01");
  
      do {
          $last_date = date("Y-m-d", strtotime($first_date. " +6 days"));
          $month = date("m", strtotime($last_date));
  
          if ($month != $m) {
              $last_date = date("Y-m-t", mktime(0, 0, 0, $m, 1, $y)); 
  
              if ($first_date > $last_date) {
                  break;
              }
           }  
  
           $weeks[] = [$first_date, $last_date];
  
           $first_date = date("Y-m-d", strtotime($last_date. " +1 days"));
  
      } while($month == intval($m));
  
      return $weeks;    
  
  }
  
  private function pullTopSellingItems($topSellingFilter) {
    $topSelling = 100;

    switch($topSellingFilter) {
      case('Daily'):
        $today = Carbon::today()->toDateString();
        $topItems = SaleItem::select( 'item_id',
                              DB::raw('sum(quantity) as sold') )
                            ->with('item')
                            ->whereDate('created_at', '=', $today)
                            ->havingRaw("sold >= $topSelling")
                            ->groupBy('item_id')
                            ->orderBy('sold', 'DESC')
                            ->limit(10)
                            ->get();
        break;
      case('Weekly'):
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();
        $topItems = SaleItem::select( 'item_id',
                              DB::raw('sum(quantity) as sold') )
                            ->with('item')
                            ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
                            ->havingRaw("sold >= $topSelling")
                            ->groupBy('item_id')
                            ->orderBy('sold', 'DESC')
                            ->limit(10)
                            ->get();
        break;
      case('Monthly'):
        $month = Carbon::now()->month;
        $topItems = SaleItem::select( 'item_id',
                              DB::raw('sum(quantity) as sold') )
                            ->with('item')
                            ->whereMonth('created_at', '=', $month)
                            ->havingRaw("sold >= $topSelling")
                            ->groupBy('item_id')
                            ->orderBy('sold', 'DESC')
                            ->limit(10)
                            ->get();
        break;
      case('Quarterly'):
        $start_of_quarter = Carbon::now()->startOfQuarter();
        $end_of_today = Carbon::now()->endOfDay();
        $topItems = SaleItem::select( 'item_id',
                              DB::raw('sum(quantity) as sold') )
                            ->with('item')
                            ->whereBetween('created_at', [$start_of_quarter, $end_of_today])
                            ->havingRaw("sold >= $topSelling")
                            ->groupBy('item_id')
                            ->orderBy('sold', 'DESC')
                            ->limit(10)
                            ->get();
        break;
      case('Yearly'):
        $year = Carbon::now()->year;
        $topItems = SaleItem::select( 'item_id',
                              DB::raw('sum(quantity) as sold') )
                            ->with('item')
                            ->whereYear('created_at', '=', $year)
                            ->havingRaw("sold >= $topSelling")
                            ->groupBy('item_id')
                            ->orderBy('sold', 'DESC')
                            ->limit(10)
                            ->get();
        break;
      default:
        $topItems = [];
    }
    return $topItems;
  }
}