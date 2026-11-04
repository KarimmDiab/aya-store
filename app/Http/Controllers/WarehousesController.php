<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Contracts\Validation\Rule;

class WarehousesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $warehouses = DB::table('warehouses')
            ->whereNull('deleted_at')
            ->get();
        return view('warehouses&branches.Warehouses', compact('warehouses'));
    }





    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    // Store a new warehouse
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'regex:/^[\p{L}\d\s-]+$/u'], // Allows letters (Arabic & English), numbers, spaces, and hyphens
            'warehouse_address' => ['required', 'regex:/^[\p{L}\d\s-]+$/u'], // Allows letters (Arabic & English), numbers, spaces, and hyphens
        ], [
            'name.required' => 'اسم المخزن مطلوب.',
            'name.regex' => 'اسم المخزن يجب أن يحتوي على حروف عربية أو إنجليزية، أرقام، مسافات، أو (-) فقط.',

            'warehouse_address.required' => 'عنوان المخزن مطلوب.',
            'warehouse_address.regex' => 'عنوان المخزن يجب أن يحتوي على حروف عربية أو إنجليزية، أرقام، مسافات، أو (-) فقط.',
        ]);

        // Get the input values
        $name = $request->input('name');
        $warehouse_address = $request->input('warehouse_address');



        // Insert the warehouse into the database using DB facade
        DB::table('warehouses')->insert([
            'name' => $name,
            'warehouse_address' => $warehouse_address,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'تم تسجيل المخزن بنجاح');
    }

    // Update an existing warehouse
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => ['required', 'regex:/^[\p{L}\d\s-]+$/u'], // Allows letters (Arabic & English), numbers, spaces, and hyphens
            'warehouse_address' => ['required', 'regex:/^[\p{L}\d\s-]+$/u'], // Allows letters (Arabic & English), numbers, spaces, and hyphens
        ], [
            'name.required' => 'اسم المخزن مطلوب.',
            'name.regex' => 'اسم المخزن يجب أن يحتوي على حروف عربية أو إنجليزية، أرقام، مسافات، أو (-) فقط.',

            'warehouse_address.required' => 'عنوان المخزن مطلوب.',
            'warehouse_address.regex' => 'عنوان المخزن يجب أن يحتوي على حروف عربية أو إنجليزية، أرقام، مسافات، أو (-) فقط.',
        ]);


        // Get the input values
        $name = $request->input('name');
        $warehouse_address = $request->input('warehouse_address');

        // Update the warehouse in the database using DB facade
        DB::table('warehouses')
            ->where('id', $id)
            ->update([
                'name' => $name,
                'warehouse_address' => $warehouse_address,
                'updated_at' => now(),
            ]);

        return redirect()->back()->with('success', 'تم تعديل بيانات المخزن بنجاح');
    }
    /**
     * Display the specified resource.
     */
    public function show (Request $warehouses)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit (Request $warehouses)
    {
        //
    }

    // Delete a warehouse
    public function destroy($id)
    {
        // Ensure $id is an array if it's not already
        if (!is_array($id)) {
            $id = [$id];
        }
        // Soft delete the warehouses by updating the `deleted_at` column
        DB::table('warehouses')
            ->whereIn('id', $id)
            ->update(['deleted_at' => now()]);


        return redirect()->back()->with('success', 'تم حذف المخزن بنجاح');
    }
}
