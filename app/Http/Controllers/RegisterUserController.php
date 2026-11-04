<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;   
use Illuminate\Support\Facades\Hash;


class RegisterUserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('auth.signup');
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
        // Server-side validation
        $validated = $request->validate([
            'user_name' => 'required|string|max:255',
            'user_email' => 'required|email|unique:users,email',
            'user_phone' => 'required|digits_between:10,15',
            'user_birthdate' => 'required|date',
            'user_gender' => 'required|in:male,female',
            'password' => 'required|min:8|confirmed',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Max size: 2MB
        ]);
    
        // Handle profile picture upload
        $profilePicturePath = null;

        if ($request->hasFile('profile_picture')) {
            $file = $request->file('profile_picture');
            $fileName = uniqid() . '.' . $file->extension(); // Generate a unique filename
            $file->move(public_path('images/profile_pic'), $fileName); // Move the file to the specified directory
            $profilePicturePath =  $fileName; // Save the relative path
        }
        
    
        // Save to the database
        DB::table('users')->insert([
            'name' => $validated['user_name'],
            'email' => $validated['user_email'],
            'mobile_number' => $validated['user_phone'],
            'birth_date' => $validated['user_birthdate'],
            'gender' => $validated['user_gender'],
            'password' => Hash::make($validated['password']),
            'profile_picture' => $profilePicturePath,
            'role' => 8,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => null,
        ]);
    
        return back()->with('success', 'مرحبا بك! تم إنشاء الحساب بنجاح.');
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
