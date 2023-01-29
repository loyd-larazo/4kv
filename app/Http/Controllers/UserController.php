<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Hash;

use App\Models\User;

class UserController extends Controller
{
	public function index(Request $request) {
		$page = $request->get('page') ?? 1;
    $search = $request->get('search');
    Paginator::currentPageResolver(function() use ($page) {
      return $page;
    });

		$users = User::when($search, function($query) use ($search) {
                          $query->where('username', 'like', "%$search%")
                                ->orWhere('firstname', 'like', "%$search%")
                                ->orWhere('lastname', 'like', "%$search%")
                                ->orWhere('gender', 'like', "%$search%")
                                ->orWhere('address', 'like', "%$search%")
                                ->orWhere('contact_number', 'like', "%$search%");
                        })
                        ->orderBy('status', 'desc')
                        ->paginate(20);

		return view('inventory.users', ['users' => $users, 'search' => $search]);
	}

	public function updateOrCreate(Request $request) {
		$id = $request->get('id');
		$changePassword = $request->get('change_password');

		$username = $request->get('username');
		$password = $request->get('password');
		$type = $request->get('type');
		$firstname = $request->get('firstname');
		$lastname = $request->get('lastname');
		$gender = $request->get('gender');
		$birthdate = $request->get('birthdate');
		$contact_number = $request->get('contact_number');
		$address = $request->get('address');
		$salary = $request->get('salary');
		$status = $request->get('status');

    $data = [
			'username' => $username,
			'type' => $type,
			'firstname' => $firstname,
			'lastname' => $lastname,
			'gender' => $gender,
			'birthdate' => $birthdate,
			'contact_number' => $contact_number,
			'address' => $address,
			'salary' => $salary,
			'status' => $status,
		];
    
    if (isset($id)) {
      $user = User::where('id', $id)->first();
      if ($user) {
        if ($changePassword) {
          $password = $request->get('new_password');
          $user->password = app('hash')->make($password);
          $user->save();
          return redirect()->back()->with('success', 'Password has been saved!'); 
        } else {
          User::where('id', $id)
              ->update($data);
        }
      }
    } else {
			$data['password'] = app('hash')->make($password);
      User::create($data);
    }
    
		return redirect()->back()->with('success', 'User has been saved!'); 
	}
	
	public function destroy(Request $request, $laborerId) {
		User::where('id', $laborerId)->delete();

		return redirect()->back()->with('success', 'Laborer has been deleted!'); 
	}
}
