<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

use App\Models\Laborer;

class LaborerController extends Controller
{
	public function index(Request $request) {
		$page = $request->get('page') ?? 1;
    Paginator::currentPageResolver(function() use ($page) {
      return $page;
    });

		$laborers = Laborer::paginate(20);

		return view('inventory.laborers', ['laborers' => $laborers]);
	}

	public function updateOrCreate(Request $request) {
		$id = $request->get('id');
		$firstname = $request->get('firstname');
		$lastname = $request->get('lastname');
		$gender = $request->get('gender');
		$birthdate = $request->get('birthdate');
		$contact_number = $request->get('contact_number');
		$address = $request->get('address');
		$salary = $request->get('salary');
		$position = $request->get('position');

		$data = [
			'firstname' => $firstname,
			'lastname' => $lastname,
			'gender' => $gender,
			'birthdate' => $birthdate,
			'contact_number' => $contact_number,
			'address' => $address,
			'salary' => $salary,
			'position' => $position,
		];

		if (isset($id)) {
			Laborer::where('id', $id)
						->update($data);
		} else {
			Laborer::create($data);
		}

		return redirect()->back()->with('success', 'Laborer has been saved!'); 
	}
	
	public function destroy(Request $request, $laborerId) {
		Laborer::where('id', $laborerId)->delete();

		return redirect()->back()->with('success', 'Laborer has been deleted!'); 
	}
}
