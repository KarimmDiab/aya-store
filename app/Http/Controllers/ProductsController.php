<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = DB::table('products')
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->get(['id', 'name', 'category', 'quantity', 'cost_price', 'price', 'image', 'created_at', 'updated_at']);
        $categories = DB::table('category')
            ->whereNull('deleted_at')
            ->get(['category_id', 'category_name']);

        return view('products.products', compact('products' , 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    // Store a new product
    public function store(Request $request)
    {
        $request->validate([
            'product_name' => 'required|regex:/^[\p{Arabic}\p{Latin}0-9\s]+$/u',
            'product_category' => 'required|exists:category,category_id',
            'product_price' => 'required|numeric|min:0',
            'product_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ], [
            'product_name.required' => 'اسم المنتج مطلوب.',
            'product_name.regex' => 'اسم المنتج يجب أن يحتوي على حروف عربية ولاتينية، أرقام، ومسافات فقط.',
            
            'product_category.required' => 'تصنيف المنتج مطلوب.',
            'product_category.exists' => 'تصنيف المنتج المحدد غير صالح.',
            
            'product_price.required' => 'سعر المنتج مطلوب.',
            'product_price.numeric' => 'يجب أن يكون سعر المنتج رقماً.',
            'product_price.min' => 'يجب أن يكون سعر المنتج على الأقل 0.',
        
            'product_image.image' => 'صورة المنتج يجب أن تكون ملف صورة.',
            'product_image.mimes' => 'صورة المنتج يجب أن تكون من نوع: jpeg, png, jpg, gif, svg.',
            'product_image.max' => 'حجم صورة المنتج يجب ألا يتجاوز 2 ميجابايت.'
        ]);
        
    
        // Fetch the first warehouse's id if no warehouse_id is provided
        $warehouseId = $request->warehouse_id ?? DB::table('warehouses')->orderBy('id')->limit(1)->value('id');
    
        // Handle the image upload if there's a file
        if ($request->hasFile('product_image')) {
            // Get the file's extension
            $imageExtension = $request->product_image->extension();
    
            // Create a unique image name
            $imageName = time() . '.' . $imageExtension;
    
            // Move the image to the public directory
            $request->product_image->move(public_path('images/products'), $imageName);
        } else {
            $imageName = 0; // If no image is uploaded, set the name to null
        }
    
        // Insert a new product using DB facade
        DB::table('products')->insert([
            'id' => $request->product_id, // Custom ID
            'name' => $request->product_name,
            'category' => $request->product_category,
            'quantity' => 0 ,
            // Cost and stock are controlled by purchase invoices, not product data entry.
            'cost_price' => 0,
            'price' => $request->product_price,
            'image' => $imageName, // Save the image name
            'warehouse_id' => $warehouseId, // Save the warehouse_id (default to the first warehouse id)
            'created_at' => now(),
            'updated_at' => now()
        ]);
    
        return redirect()->back()->with('success', 'تم إضافة المنتج بنجاح');
    }
    
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

   // Update an existing product
   public function update(Request $request, $id)
   {
       // Validate the request
       $request->validate([
        'product_name' => 'required|regex:/^[\p{Arabic}\p{Latin}0-9\s]+$/u',
        'product_category' => 'required|exists:category,category_id',
        'product_price' => 'required|numeric|min:0',
        'product_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
    ], [
        'product_name.required' => 'اسم المنتج مطلوب.',
        'product_name.regex' => 'اسم المنتج يجب أن يحتوي على حروف عربية ولاتينية، أرقام، ومسافات فقط.',
        
        'product_category.required' => 'تصنيف المنتج مطلوب.',
        'product_category.exists' => 'تصنيف المنتج المحدد غير صالح.',
    
        'product_price.required' => 'سعر المنتج مطلوب.',
        'product_price.numeric' => 'يجب أن يكون سعر المنتج رقماً.',
        'product_price.min' => 'يجب أن يكون سعر المنتج على الأقل 0.',
    
        'product_image.image' => 'صورة المنتج يجب أن تكون ملف صورة.',
        'product_image.mimes' => 'صورة المنتج يجب أن تكون من نوع: jpeg, png, jpg, gif, svg.',
        'product_image.max' => 'حجم صورة المنتج يجب ألا يتجاوز 2 ميجابايت.'
    ]);
    
        $imageName = DB::table('products')->where('id', $id)->value('image');

       // Handle the image upload if there's a file
        if ($request->hasFile('product_image')) {
            // Get the file's extension
            $imageExtension = $request->product_image->extension();
    
            // Create a unique image name
            $imageName = time() . '.' . $imageExtension;
    
            // Move the image to the public directory
            $request->product_image->move(public_path('images/products'), $imageName);
        } else {
            $imageName = $imageName; // If no image is uploaded, set the name to null
        }
   
       // Update the product using raw SQL query
       DB::table('products')->where('id', $id)->update([
           'name' => $request->product_name,
           'category' => $request->product_category,
           'price' => $request->product_price,
           'image' => $imageName, // Save the image name
           'updated_at' => now(), // Update the timestamp
       ]);
   
       return redirect()->back()->with('success', 'تم تعديل المنتج بنجاح');
   }
   
   // Delete a product using raw SQL query
   public function destroy($id)
   {
    if (!is_array($id)) {
        $id = [$id];
    }
       DB::table('products')
       ->whereIn('id', $id)
        ->update(['deleted_at' => now()]);
   
       return redirect()->back()->with('success', 'تم حذف المنتج بنجاح');
   }
}
