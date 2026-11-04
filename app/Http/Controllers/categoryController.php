<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class categoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = DB::table('category')
            ->whereNull('deleted_at')
            ->get();
        return view('products.category', compact('categories'));

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
        // Validate the request data
        $request->validate([
            'category_name' => 'required|regex:/^[\p{L}\d\s\-\(\)]+$/u',  // Allows Arabic, English letters, numbers, spaces, hyphens, and parentheses
            'category_desc' => 'required|regex:/^[\p{L}\d\s\-\(\)]+$/u', // Allows Arabic, English letters, numbers, spaces, hyphens, and parentheses
        ], [
            'category_name.required' => 'اسم الفئة مطلوب.',
            'category_name.regex' => 'اسم الفئة يجب أن يحتوي على حروف عربية أو إنجليزية، أرقام، مسافات، شرطة (-)، أو أقواس () فقط.',
            'category_desc.required' => 'وصف الفئة مطلوب.',
            'category_desc.regex' => 'وصف الفئة يجب أن يحتوي على حروف عربية أو إنجليزية، أرقام، مسافات، شرطة (-)، أو أقواس () فقط.',
        ]);



        // Insert the supplier into the database
        DB::table('category')->insert([
            'category_name' => $request->category_name,
            'category_desc' => $request->category_desc,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'تم إضافة التصنيف بنجاح');
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

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $category_id)
    {
        // Validate the request data
        $request->validate([
            'category_name' => 'required|regex:/^[\p{L}\d\s-]+$/u',  // Allows Arabic, English letters, numbers, spaces, and hyphens
            'category_desc' => 'required|regex:/^[\p{L}\d\s-]+$/u', // Allows Arabic, English letters, numbers, spaces, and hyphens
        ], [
            'category_name.required' => 'اسم الفئة مطلوب.',
            'category_name.regex' => 'اسم الفئة يجب أن يحتوي على حروف عربية أو إنجليزية، أرقام، مسافات، أو (-) فقط.',
            'category_desc.required' => 'وصف الفئة مطلوب.',
            'category_desc.regex' => 'وصف الفئة يجب أن يحتوي على حروف عربية أو إنجليزية، أرقام، مسافات، أو (-) فقط.',
        ]);


        // Update the supplier in the database
        DB::table('category')
            ->where('category_id', $category_id)
            ->update([
                'category_name' => $request->category_name,
                'category_desc' => $request->category_desc,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        return redirect()->back()->with('success', 'تم تعديل بيانات التصنيف بنجاح');
    }

    // Delete a category (soft delete)
    public function destroy($category_id)
    {
        // Check if the category exists
        $category = DB::table('category')->where('category_id', $category_id)->first();

        if (!$category) {
            return redirect()->back()->with('error', 'التصنيف غير موجود.');
        }

        // Soft delete the category
        DB::table('category')
            ->where('category_id', $category_id)
            ->update(['deleted_at' => now()]);

        return redirect()->back()->with('success', 'تم حذف التصنيف بنجاح');
    }

}
