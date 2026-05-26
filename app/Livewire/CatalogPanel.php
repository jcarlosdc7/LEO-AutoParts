<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;

class CatalogPanel extends Component
{	
	use WithPagination;
	
	public function render()
	{
		$products = Product::paginate(10, pageName: 'pageCatalog');
		return view('livewire.lwCatalog.catalog-panel', compact('products'));
	}
}
