<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Hash;

use App\Models\Setting;

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

  public function index(Request $request) {
    return view('switch');
  }
}
