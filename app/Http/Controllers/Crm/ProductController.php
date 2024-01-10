<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;

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

        $product = new Product([
            'product_category_id' => $request->category_id,
            'name' => $request->name,
            'consist' => $request->consist,
            'weigth' => $request->weight,
            'price' => $request->price,
            'visible' => false
        ]);

        // tags 
        foreach ($request->tags as $tag) {
            $product[$tag] = 1;
        }

        if (!$product->save()) return back()->withErrors(['error' => 'Не удалось сохранить товар']);
        
        if ($request->file('images')){
            foreach ($request->file('images') as $e){
                $uniq = Str::random(10);
                $name = time() .'-' . $product->id . '.' . $e->getClientOriginalExtension();
                Image::make($e)->resize(1024,1024, fn($item)=> $item->aspectRatio())->save( public_path('assets/products/original-') . $name , 95);
                Image::make($e)->resize(512,512, fn($item)=> $item->aspectRatio())->save(public_path('assets/products/thumb-medium-') . $name , 95);
                Image::make($e)->resize(128,128, fn($item)=> $item->aspectRatio())->save(public_path('assets/products/thumb-small-') . $name , 100);
                
                $product->images()->createMany([
                    ['uniq'=> $uniq, 'type' => 'original', 'path' => '/assets/products/original-' .$name],
                    ['uniq'=> $uniq, 'type' => 'thumb-medium', 'path' => '/assets/products/thumb-medium-' . $name],
                    ['uniq'=> $uniq, 'type' => 'thumb-small', 'path' => '/assets/products/thumb-small-' . $name],
                ]);
            }
        }
            
        return back()->with('success', 'Товар успешно создан');
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
     * Store new category
     */
    public function storeCategory(Request $request){
        $category = new ProductCategory();
        $category->uri = Str::random(10);
        $category->name = $request->name;
        $category->visible = false;
        if (!$category->save()){
            return back()->withErrors(['error' => 'Не удалось создать новую категорию']);
        }

        return back()->with('success', 'Новая категория создана');
    }
    public function categoryVisibility(string $id){
        $category = ProductCategory::findOrFail($id);
        $category->visible = !$category->visible;
        $category->update();

        return back()->with('success', 'Категория обновлена');
    }

    /**
     *  Destroy category instanse with cascade products
     */
    public function destroyCategory(string $id){
        ProductCategory::findOrFail($id)->delete();
        return back()->with('success', 'Категория успешно удалена');
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
        $product = Product::findOrFail($id);
        $product->update($request->all());

        return back()->with('success', 'Данные обновлены');
    }
    public function productVisibility(string $id){
        $product = Product::findOrFail($id);
        $product->visible = !$product->visible;
        $product->update();
        return back()->with('success', 'Статус товара обновлен');
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
        $product = Product::findOrFail($id);
        
        if (!$product->images->isEmpty()){
            foreach ($product->images as $e) {
                if (File::exists(public_path($e->path))){
                    File::delete(public_path($e->path));
                }
                $e->delete();
            }
        }
        $product->delete();
        return back()->with('success', 'Товар удален из категории');
    }
}
