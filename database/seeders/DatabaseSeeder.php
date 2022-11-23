<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Setting;
use App\Models\Category;
use App\Models\Item;

class DatabaseSeeder extends Seeder
{
  /**
   * Seed the application's database.
   *
   * @return void
   */
  public function run()
  {
    Setting::updateOrCreate([
      'key' => 'username',
    ], [
      'value' => 'admin'
    ]);

    Setting::updateOrCreate([
      'key' => 'password',
    ], [
      'value' => app('hash')->make('secret@123')
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
          'stock' => $item['Stock'] ? (int)$item['Stock'] >= 0 ? (int)$item['Stock'] : 0 : 0,
          'status' => $item['Status'] && $item['Status'] == "Y" ? 1 : 0,
        ]);

        echo $sku."\n";
      }
    }
  }
}
