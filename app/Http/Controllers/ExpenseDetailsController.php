<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;   
use Maatwebsite\Excel\Facades\Excel;

class ExpenseDetailsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $expenses = DB::table('expenses_items')
        ->whereNull('deleted_at')  // Check if deleted_at is NULL
        ->get();
        return view('expenses.expenses_details', compact('expenses'));
    }

    public function showDetails()
    {
        $expensesDetails = DB::table('expenses_details')
        ->join('expenses_items', 'expenses_details.expenses_item_id', '=', 'expenses_items.expenses_item_id')
        ->whereNull('expenses_details.deleted_at')
        ->select('expenses_items.expenses_name', 'expenses_details.*')
        ->get();
        $expenses = DB::table('expenses_items')
        ->whereNull('deleted_at')  // Check if deleted_at is NULL
        ->get();
        return view('expenses.all_expenses', compact('expensesDetails' , 'expenses'));
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


        return back()->with('success', 'تم إضافة بند المصروفات بنجاح');
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
        $expensesDetails = $request->input('expenses_details');
        
        // Validate the incoming data with custom error messages
        $request->validate([
            'expenses_details' => 'required|array',
            'expenses_details.*.category' => 'required|exists:expenses_items,expenses_item_id',
            'expenses_details.*.date' => 'required|date',
            'expenses_details.*.cost' => 'required|numeric|min:0',
            'expenses_details.*.note' => 'nullable|string|max:255',
            'expenses_details.*.file' => 'nullable|mimes:jpg,jpeg,png,pdf|max:2048', // Validate file
        ], [
            // Custom error messages
            'expenses_details.required' => 'لا توجد بيانات للمصاريف. يرجى إضافة تفاصيل المصاريف.',
            'expenses_details.array' => 'تفاصيل المصاريف يجب أن تكون مصفوفة.',
            
            'expenses_details.*.category.required' => 'يرجى اختيار بند المصاريف.',
            'expenses_details.*.category.exists' => 'بند المصاريف المحدد غير موجود في النظام.',
            
            'expenses_details.*.date.required' => 'يرجى إدخال تاريخ المصاريف.',
            'expenses_details.*.date.date' => 'تاريخ المصاريف يجب أن يكون تاريخًا صحيحًا.',
            
            'expenses_details.*.cost.required' => 'يرجى إدخال مبلغ المصاريف.',
            'expenses_details.*.cost.numeric' => 'مبلغ المصاريف يجب أن يكون رقماً.',
            'expenses_details.*.cost.min' => 'مبلغ المصاريف يجب أن يكون أكبر من أو يساوي 0.',
            
            'expenses_details.*.note.string' => 'الملحوظات يجب أن تكون نصًا.',
            'expenses_details.*.note.max' => 'الملحوظات يجب ألا تتجاوز 255 حرفًا.',
            
            'expenses_details.*.file.mimes' => 'يجب أن يكون الملف من نوع (jpg, jpeg, png, pdf).',
            'expenses_details.*.file.max' => 'حجم الملف يجب ألا يتجاوز 2 ميجابايت.',
        ]);
        
        // Insert each expense into the database
        foreach ($expensesDetails as $key => $expense) {
            // Handle file upload for each expense
            $fileName = null;
            if ($request->hasFile('expenses_details.' . $key . '.file')) {
                $file = $request->file('expenses_details.' . $key . '.file');
                $fileExtension = $file->extension();
                // Generate a unique file name by including the key (index) in the filename
                $fileName = time() . '_' . $key . '.' . $fileExtension;
                $file->move(public_path('images/expenses_pic'), $fileName); // Store the file in 'images/expenses_pic'
            }
    
            DB::table('expenses_details')->insert([
                'expenses_item_id' => $expense['category'],
                'expenses_date' => $expense['date'],
                'expenses_cost' => $expense['cost'],
                'expenses_note' => $expense['note'] ?? null,
                'expenses_file' => $fileName, // Store the file name in the database
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        return redirect()->back()->with('success', 'تم تسجيل المصاريف بنجاح');
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
        $request->validate([
            'id' => 'required|exists:expenses_items,expenses_item_id',
            'date' => 'required|date',
            'cost' => 'required|numeric|min:0|regex:/^\d+(\.\d{1,2})?$/', // Accepts numbers including float with two decimal points
            'note' => 'nullable|regex:/^[A-Za-z0-9\sأ-ي\-]+$/|max:255', // Allow hyphen (-) in addition to Arabic letters, English letters, numbers, and spaces
        ], [
            'id.required' => 'الرجاء اختيار اسم البند.',
            'id.exists' => 'البند المختار غير موجود.',
            'date.required' => 'الرجاء اختيار التاريخ.',
            'date.date' => 'الرجاء إدخال تاريخ صحيح.',
            'cost.required' => 'الرجاء إدخال التكلفة.',
            'cost.numeric' => 'يجب أن تكون التكلفة رقماً.',
            'cost.min' => 'التكلفة يجب أن تكون قيمة موجبة.',
            'cost.regex' => 'يجب أن تكون التكلفة رقماً عشرياً صالحاً (مثل 100.50).', // Error message for invalid float format
            'note.regex' => 'الملاحظات يجب أن تحتوي على أحرف إنجليزية، أرقام، مسافات، شرطة (-) أو أحرف عربية فقط.',
            'note.max' => 'يجب ألا يتجاوز النص 255 حرفًا.',
        ]);
        
        
    
        // Update the database
        DB::table('expenses_details')
            ->where('expenses_details_id', $id)
            ->update([
                'expenses_item_id' => $request->id,
                'expenses_date' => $request->date,
                'expenses_cost' => $request->cost,
                'expenses_note' => $request->note,
                'updated_at' => now(),
            ]);
    
        return back()->with('success', 'تم تحديث تفاصيل المصروفات بنجاح.');
    }
    

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // Soft delete the expense by setting deleted_at to the current timestamp
        DB::table('expenses_details')
            ->where('expenses_details_id', $id)
            ->update(['deleted_at' => now()]);
    
        return back()->with('success', 'تم حذف المصروف بنجاح.');
    }
}
