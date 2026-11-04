<?php
namespace App\Http\Controllers;

use App\Models\Suppliers; // Correct model namespace
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class rolesController extends Controller
{
    public function index()
    {
        // Fetch all roles from the database using DB facade
        $roles = DB::table('roles')
            ->whereNull('deleted_at')
        ->get();

        $rolesWithUserCount = DB::table('users')
            ->join('roles', 'users.role', '=', 'roles.role_id')
            ->select(
                'roles.role_id',
                'roles.role_name',
                DB::raw('COUNT(users.role) as user_count')
            )
            ->whereNull('users.deleted_at') // Specify the table name
            ->groupBy('roles.role_id', 'roles.role_name')
        ->get();

    

        
        // Return the view with the roles data
        return view('users.roles', compact('roles' , 'rolesWithUserCount')); 
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Return your form view here
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request data
        $request->validate([
            'add_role' => 'required|string|regex:/^[\p{Arabic}A-Za-z0-9]+$/u|max:255',
            'role_desc' => 'required|string|regex:/^[\p{Arabic}A-Za-z0-9]+$/u|max:255',
        ], [
            'add_role.regex' => 'اسم المجموعة يجب أن يحتوي على حروف وأرقام فقط.',
            'role_desc.regex' => 'وصف المجموعة يجب أن يحتوي على حروف وأرقام فقط.',
        ]);

        // Insert the new role into the database
        DB::table('roles')->insert([
            'name' => $request->add_role,
            'description' => $request->role_desc,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Redirect back with a success message
        return redirect()->back()->with('success', 'Role added successfully!');
    }

    

    /**
     * Display the specified resource.
     */
    public function show(suppliers $suppliers)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(suppliers $suppliers)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id) // Accept the role ID as a parameter
    {
        // Validate the request data
        $request->validate([
            'add_role' => 'required|string|regex:/^[\p{Arabic}A-Za-z0-9]+$/u|max:255',
            'role_desc' => 'required|string|regex:/^[\p{Arabic}A-Za-z0-9]+$/u|max:255',
        ], [
            'add_role.regex' => 'اسم المجموعة يجب أن يحتوي على حروف وأرقام فقط.',
            'role_desc.regex' => 'وصف المجموعة يجب أن يحتوي على حروف وأرقام فقط.',
        ]);
        // Update the role using the DB facade
        DB::table('roles')
            ->where('id', $id)
            ->update([
                'name' => $request->add_role,
                'description' => $request->role_desc,
                'updated_at' => now() // Update the timestamp
            ]);
    
        return redirect()->back()->with('success', 'Role updated successfully.');
    }
    
    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id) // Accept the role ID as a parameter
    {
        if (!is_array($id)) {
            $id = [$id];
        }
        // Delete the role using the DB facade
        DB::table('roles')
        ->whereIn('id', $id)
        ->update(['deleted_at' => now()]);
    
        return redirect()->back()->with('success', 'Role deleted successfully.');
    }
}
