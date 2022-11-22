<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Setting;

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
  }
}
