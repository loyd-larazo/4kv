<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

use App\Models\Category;
use App\Models\Item;
use App\Models\Supplier;

use DNS1D;

class ItemController extends Controller
{
  public function items(Request $request) {
    $page = $request->get('page') ?? 1;
    $categories = Category::get();

    Paginator::currentPageResolver(function() use ($page) {
      return $page;
    });

    $items = Item::with('category')->paginate(20);

		return view('inventory.items', ['items' => $items, 'categories' => $categories]);
	}

  public function updateOrCreateItem(Request $request) {
		$id = $request->get('id');
		$name = $request->get('name');
		$cost = $request->get('cost');
		$price = $request->get('price');
		$description = $request->get('description');
		$category = $request->get('category');
		$sold_by_weight = $request->get('sold_by_weight');
		$stock = $request->get('stock');
		$status = $request->get('status');
		
		$data = [
			'name' => $name,
      'cost' => $cost,
      'price' => $price,
      'description' => $description,
      'category_id' => $category,
      'sold_by_weight' => $sold_by_weight,
      'status' => $status,
		];

		if (isset($id)) {
			Item::where('id', $id)
						->update($data);
		} else {
      $data['stock'] = $stock;
      $data['sku'] = uniqid(true);
			Item::create($data);
		}

		return redirect()->back()->with('success', 'Item has been saved!'); 
	}

  public function generateBarcode(Request $request, $sku) {
    // echo DNS1D::getBarcodeSVG($sku, 'C39', 1, 70);
    return view('inventory.barcode', ['sku' => $sku]);
  }

  public function categories(Request $request) {
    $page = $request->get('page') ?? 1;
    Paginator::currentPageResolver(function() use ($page) {
      return $page;
    });

		$categories = Category::paginate(20);

		return view('inventory.categories', ['categories' => $categories]);
	}

  public function updateOrCreateCategory(Request $request) {
		$id = $request->get('id');
		$name = $request->get('name');
		
		$data = [
			'name' => $name,
		];

		if (isset($id)) {
			Category::where('id', $id)
						->update($data);
		} else {
			Category::create($data);
		}

		return redirect()->back()->with('success', 'Category has been saved!'); 
	}
	
	public function destroyCategory(Request $request, $categoryId) {
    Item::where('category_id', $categoryId)
        ->update(['category_id' => null]);

		Category::where('id', $categoryId)->delete();

		return redirect()->back()->with('success', 'Category has been deleted!'); 
	}

  public function suppliers(Request $request) {
    $page = $request->get('page') ?? 1;
    Paginator::currentPageResolver(function() use ($page) {
      return $page;
    });

		$suppliers = Supplier::paginate(20);

		return view('inventory.suppliers', ['suppliers' => $suppliers]);
	}

  public function updateOrCreateSupplier(Request $request) {
		$id = $request->get('id');
		$name = $request->get('name');
		$contact_person = $request->get('contact_person');
		$contact_number = $request->get('contact_number');
		$address = $request->get('address');
		
		$data = [
			'name' => $name,
      'contact_person' => $contact_person,
      'contact_number' => $contact_number,
      'address' => $address,
		];

		if (isset($id)) {
			Supplier::where('id', $id)
						->update($data);
		} else {
			Supplier::create($data);
		}

		return redirect()->back()->with('success', 'Supplier has been saved!'); 
	}
}
