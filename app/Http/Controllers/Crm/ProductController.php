<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('crm.products.index',[
            'categories' => ProductCategory::all()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource products category model
     */
    public function showCategory(string $id)
    {
        return view('crm.products.category',[
            'category' => ProductCategory::findOrFail($id),
            'products' => ProductCategory::find($id)->products()->paginate(15)
        ]);
    }

    /**
     * Display the specified resource products model.
     */
    public function show(string $id){
        $product = Product::findOrFail($id);
        
        return view('crm.products.show-product',[
            'product' => $product,
        ]);
    }
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {

        $product = Product::findOrFail($id);
        return view('crm.products.edit', [
            'product' => $product
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }
    public function updateTag(string $id, string $tag){
       $product = Product::findOrFail($id);
       $product[$tag] = !$product[$tag];
       $product->update();
       return back()->with('success', 'Тэг продукта обновлен');
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        return $id;
    }
}
