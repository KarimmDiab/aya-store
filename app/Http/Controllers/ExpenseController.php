<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;   


class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $expenses = DB::table('expenses_items')
        ->whereNull('deleted_at')  // Check if deleted_at is NULL
        ->get();
        
        $expensesDetails = DB::table('expenses_details')->get();


        return view('expenses.add_expenses', compact('expenses', 'expensesDetails'));

    }



    public function storeExpensesItem(Request $request)
{
    // Server-side validation
    $request->validate([
        'expenses_name' => 'required|regex:/^[A-Za-z0-9\s\p{Arabic}]+$/u|max:255',
    ], [
        'expenses_name.regex' => 'الرجاء إدخال حروف عربية أو إنجليزية أو أرقام أو مسافات فقط.',
    ]);

    // Insert into database using DB facade
    DB::table('expenses_items')->insert([
        'expenses_name' => $request->expenses_name,
        'created_at' => now(),
    ]);

    return redirect()->route('expenses')->with('success', 'تم إضافة بند المصروفات بنجاح');
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
    public function update(Request $request, $id)
    {
        // Validate the request data
        $request->validate([
            'name' => 'required|string|max:255',
        ]);
    
        // Update the expense item in the database
        DB::table('expenses_items')
            ->where('expenses_item_id', $id)
            ->update([
                'expenses_name' => $request->name,
                'updated_at' => now(),
            ]);
    
        // Redirect with success message
        return redirect()->route('expenses')->with('success', 'تم تحديث بند المصروفات بنجاح');
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // Check if the record exists before deleting
        $expenseItem = DB::table('expenses_items')->where('expenses_item_id', $id)->first();
    
        if (!$expenseItem) {
            return redirect()->route('expenses')->with('error', 'لم يتم العثور على بند المصروفات');
        }
    
        // Soft delete the expense item by updating the `deleted_at` column
        DB::table('expenses_items')
            ->where('expenses_item_id', $id)
            ->update(['deleted_at' => now()]);
    
        return redirect()->route('expenses')->with('success', 'تم حذف بند المصروفات بنجاح');
    }
}
