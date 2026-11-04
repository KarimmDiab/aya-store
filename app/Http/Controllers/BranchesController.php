<?php

namespace App\Http\Controllers;

use App\Models\branches;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;



class BranchesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $branches = DB::table('branches')->get();
    
        return view('warehouses&branches.branches', compact('branches'));
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
        $validatedData = $request->validate([
            'branche_name' => [
                'required',
                'regex:/^[\p{L}\p{N}\s]+$/u' // Allows letters (including Arabic), numbers, and spaces
            ],
            'branche_location' => [
                'required',
                'regex:/^[\p{L}\p{N}\s]+$/u' // Allows letters (including Arabic), numbers, and spaces
            ],
        ], [
            'branche_name.required' => 'The branch name is required.',
            'branche_name.regex' => 'The branch name can only contain letters, numbers, and spaces.',
            'branche_location.required' => 'The branch location is required.',
            'branche_location.regex' => 'The branch location can only contain letters, numbers, and spaces.',
        ]);
    
        // Insert branch data into the database
        DB::table('branches')->insert([
            'name' => $validatedData['branche_name'],
            'location' => $validatedData['branche_location'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    
        return redirect()->back()->with('success', 'تم أضافة الفرع بنجاح');
    }
    
    
    
    
    /**
     * Display the specified resource.
     */
    public function show(branches $branches)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(branches $branches)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Custom validation for branch name and location
        $validator = Validator::make($request->all(), [
            'branche_name' => 'required',
            'branche_location' => 'required',
        ]);
    
        $validator->after(function ($validator) use ($request) {
            // Regex to allow letters (including Arabic), numbers, and spaces
            $regex = '/^[A-Za-z0-9\s\x{0600}-\x{06FF}]+$/u';
    
            // Validate branch name
            if (!preg_match($regex, $request->branche_name)) {
                $validator->errors()->add('branche_name', 'برجاء ادخال حروف، أرقام، أو مسافات فقط');
            }
    
            // Validate branch location
            if (!preg_match($regex, $request->branche_location)) {
                $validator->errors()->add('branche_location', 'برجاء ادخال حروف، أرقام، أو مسافات فقط');
            }
        });
    
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
    
        // Update the branch in the database using DB facade
        DB::table('branches')->where('id', $id)->update([
            'name' => $request->branche_name,
            'location' => $request->branche_location,
            'updated_at' => now(), // Update the timestamp
        ]);
    
        return redirect()->back()->with('success', 'تم تحديث الفرع بنجاح.'); // Success message in Arabic
    }
    

public function destroy($id)
{
    // Attempt to delete the branch from the database using DB facade
    $deleted = DB::table('branches')->where('id', $id)->delete();

    if ($deleted) {
        return redirect()->back()->with('success', 'تم حذف الفرع بنجاح.'); // Success message in Arabic
    } else {
        return redirect()->back()->with('error', 'حدث خطأ أثناء حذف الفرع.'); // Error message in Arabic
    }
}

}
