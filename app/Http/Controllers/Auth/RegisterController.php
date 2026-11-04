<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;   
use Illuminate\Support\Facades\Hash;


class RegisterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Retrieve all users
        $users = DB::select('SELECT * FROM users');
        $roles = DB::select('SELECT * FROM roles');
        $nextUserId = DB::table('users')->orderBy('id', 'desc')->limit(1)->value('id') + 1;

        // Send the users to the view
        return view('users.all_users', ['users' => $users, 'roles' => $roles , 'nextUserId' => $nextUserId]);
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'user_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'], // This checks for password confirmation
            'user_phone' => ['required', 'string', 'max:15'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', 'in:male,female'], 
            'role' => ['required', 'exists:roles,id'], 
            'status' => ['required', 'in:active,deactive'], 
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
        // Log the incoming request data
        \Log::info('Incoming request data: ', $request->all());
    
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'user_name' => [
                'required',
                'string',
                'max:255',
                'regex:/^(?!.*[<>_@\\\/]).*$/', // Disallow certain special characters
                'min:8' // At least 8 characters
            ],
            'email' => 'required|email|unique:users,email',
            'password' => [
                'required',
                'string',
                'min:8',  // At least 8 characters
                'regex:/[a-z]/',  // At least one lowercase letter
                'regex:/[0-9]/'  // At least one number
            ],
            'password_confirmation' => 'required|same:password',  // Password confirmation match
            'user_phone' => 'required|regex:/^(01)[0-9]{9}$/',  // Match phone pattern (Egyptian phone number format)
            'birth_date' => 'nullable|date',
            'gender' => 'required|in:male,female',
            'role' => 'required|exists:roles,id',
            'status' => 'required|in:active,deactive',
        ]);
    
        // Check if validation fails
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
    
        // Insert the user into the database
        DB::table('users')->insert([
            'name' => $request->user_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'mobile_number' => $request->user_phone,
            'birth_date' => $request->birth_date,
            'gender' => $request->gender,
            'role' => $request->role,
            'status' => $request->status,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    
        // Log the successful creation
        \Log::info('User created successfully.');
    
        return redirect()->route('all_users')->with('success', 'User created successfully.');
    }
    
    
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {

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
        // Validate the incoming request data
        $validatedData = $request->validate([
            'user_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:6|confirmed', // Ensure you validate the confirmation
            'user_phone' => 'required|string|max:15', // Adjust max length as needed
            'gender' => 'required|in:male,female',
            'role' => 'required|exists:roles,id', // Ensure role exists in roles table
            'status' => 'required|in:active,deactive',
        ]);
    
        // Update the user record in the database using the DB facade
        DB::table('users')->where('id', $id)->update([
            'name' => $validatedData['user_name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'mobile_number' => $validatedData['user_phone'],
            'gender' => $validatedData['gender'],
            'role' => $validatedData['role'],
            'status' => $validatedData['status'],
            'updated_at' => now(), // Optional: update the timestamp
        ]);
    
        return redirect()->back()->with('success', 'User updated successfully!');
    }
    
    

    /**
     * Remove the specified resource from storage.
     */

    public function destroy(string $id)
    {
        // Attempt to delete the user from the database
        $deleted = DB::table('users')->where('id', $id)->delete();
    
        // Check if the deletion was successful
        if ($deleted) {
            return redirect()->route('all_users')->with('success', 'User deleted successfully.');
        } else {
            return redirect()->route('all_users')->with('error', 'User not found or could not be deleted.');
        }
    }
    
}
