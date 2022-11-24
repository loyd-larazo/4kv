<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Hash;
use Illuminate\Pagination\Paginator;

use App\Models\Setting;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Item;

use DB;

class AppController extends Controller
{
  public function loginPage(Request $request) {
    return view("login");
  }

  public function login(Request $request) {
    $username = $request->get('username');
    $password = $request->get('password');

    $settingUsername = Setting::where('key', 'username')->first();
    $settingPassword = Setting::where('key', 'password')->first();

    if (!$settingPassword || !$settingUsername) {
      return view('/login', ['error' => 'Database is not initialized!']);
    }

    if ($username != $settingUsername->value || !Hash::check($password, $settingPassword->value)) {
      return view('/login', ['error' => 'Invalid credentials!', 'username' => $username]);
    }

    // Set session
    $settingWarningLimit = Setting::where('key', 'warning_limit')->first();
    $request->session()->put('user', $settingUsername->value);
    $request->session()->put('warning_limit', $settingWarningLimit->value);

    return redirect('/');
  }

  public function logout(Request $request) {
    $request->session()->flush();

    return redirect('/login');
  }

  public function index(Request $request) {
    return view('home');
  }

  public function settings(Request $request) {
    $user = $request->session()->get('user');
    $warningLimit = $request->session()->get('warning_limit');

    return view('settings', ['username' => $user, 'warning_limit' => $warningLimit]);
  }

  public function updateSettings(Request $request) {
    $username = $request->get('username');
    $password = $request->get('password');
    $warningLimit = $request->get('warning-limit');

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
  
}
