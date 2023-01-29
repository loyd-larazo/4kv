<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Setting;
use App\Models\Category;
use App\Models\Item;
use App\Models\User;
use App\Models\DailySale;
use App\Models\Sale;
use App\Models\SaleItem;

class DatabaseSeeder extends Seeder
{
  /**
   * Seed the application's database.
   *
   * @return void
   */
  public function run()
  {
    $withRandomSales = false;
    // Create Initial users
    User::firstOrCreate([
      'username' => 'admin'
    ], [
      'password' => app('hash')->make('secret@123'), 
      'type' => 'admin', 
      'firstname' => 'Admin', 
      'gender' => 'male', 
      'status' => 1
    ]);

    $cashier = User::firstOrCreate([
      'username' => 'cashier'
    ], [
      'password' => app('hash')->make('secret@123'), 
      'type' => 'cashier', 
      'firstname' => 'Cashier', 
      'gender' => 'male', 
      'status' => 1
    ]);

    User::firstOrCreate([
      'username' => 'stockman'
    ], [
      'password' => app('hash')->make('secret@123'), 
      'type' => 'stock man', 
      'firstname' => 'Stock', 
      'lastname' => 'Man', 
      'gender' => 'male', 
      'status' => 1
    ]);

    Setting::updateOrCreate([
      'key' => 'warning_limit',
    ], [
      'value' => '10'
    ]);


    $path = public_path() . "/data/seed.json";
    $items = json_decode(file_get_contents($path), true); 
    if ($items && count($items)) {
      foreach ($items as $item) {
        if (!$item['Name'] && !$item['Handle']) {
          continue;
        }

        $categoryId = null;
        if ($item['Category']) {
          $category = Category::firstOrCreate(['name' => $item['Category']]);
          $categoryId = $category->id;
        }
        
        $sku = $item['SKU'] ? $item['SKU'] : uniqid(true);
        Item::updateOrCreate([
          'sku' => $sku
        ], [
          'name' => $item['Name'] ? $item['Name'] : $item['Handle'],
          'cost' => $item['Cost'] ? (float)$item['Cost'] : 0,
          'price' => $item['Price'] ? (float)$item['Price'] : 0,
          'description' => $item['Description'],
          'category_id' => $categoryId,
          'sold_by_weight' => $item['Sold by weight'] && $item['Sold by weight'] == "Y" ? 1 : 0,
          'stock' => $item['Stock'] ? ((int)$item['Stock'] >= 0 ? (int)$item['Stock'] : rand(10, 100)) : rand(10, 100),
          'status' => $item['Status'] && $item['Status'] == "Y" ? 1 : 0,
        ]);

        echo $sku."\n";
      }
    }

    // Seed sales
    if ($withRandomSales) {
      for ($s = 0; $s <= 50; $s++) {
        $salesDate = $this->randomDate("2018-01-01", date('y-m-d'));

        $openingAmount = rand(500,5000);
        $dailySale = DailySale::create([
          'opening_user_id' => $cashier->id,
          'closing_user_id' => $cashier->id,
          'sales_count' => 1,
          'opening_amount' => $openingAmount,
          'created_at' => $salesDate,
          'updated_at' => $salesDate,
        ]);
        
        $numItems = rand(1, 10);
        $sales = Sale::create([
          'user_id' => $cashier->id,
          'daily_sale_id' => $dailySale->id,
          'reference' => strtoupper("S".date("Y").date("m").date("d").uniqid(true)),
          'total_quantity' => 0,
          'total_amount' => 0,
          'paid_amount' => 0,
          'change_amount' => 0,
          'created_at' => $salesDate,
          'updated_at' => $salesDate,
        ]);

        $totalQty = 0;
        $totalAmount = 0;
        for ($i = 0; $i <= $numItems; $i++) {
          $item = Item::where('id', rand(1, count($items)))->first();
          if ($item) {
            $randQty = rand(1, $item->stock);
            $amountTot = $item->price * $randQty;
            $totalQty = $totalQty + $randQty;
            $totalAmount = $totalAmount + $amountTot;

            SaleItem::create([
              'sale_id' => $sales->id,
              'item_id' => $item->id,
              'quantity' => $randQty,
              'amount' => $item->price,
              'total_amount' => $amountTot,
              'created_at' => $salesDate,
              'updated_at' => $salesDate,
            ]);
          }
        }

        $sales->total_quantity = $totalQty;
        $sales->total_amount = $totalAmount;
        $sales->paid_amount = $totalAmount;
        $sales->save();
        
        $dailySale->sales_amount = $totalAmount;
        $dailySale->closing_amount = $openingAmount + $totalAmount;
        $dailySale->difference_amount = 0;
        $dailySale->save();
      }
    }
  }

  private function randomDate($start_date, $end_date) {
    // Convert to timetamps
    $min = strtotime($start_date);
    $max = strtotime($end_date);

    // Generate random number using above bounds
    $val = rand($min, $max);

    // Convert back to desired date format
    return date('Y-m-d H:i:s', $val);
  }
}
