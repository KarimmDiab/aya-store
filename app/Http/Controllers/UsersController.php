<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;   
use Illuminate\Support\Facades\Hash;


class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Retrieve all users
        $users = DB::select('SELECT * FROM users WHERE deleted_at IS  NULL');
        $roles = DB::select('SELECT * FROM roles WHERE deleted_at IS  NULL');        
        $nextUserId = DB::table('users')->orderBy('id', 'desc')->limit(1)->value('id') + 1;
        $rolesWithUsers = DB::table('roles')
        ->join('users', 'roles.role_id', '=', 'users.role')
        ->select('*')
        ->whereNull('users.deleted_at')  // Ensure deleted_at is null
        ->get();
    



        // Send the users to the view
        return view('users.all_users', ['users' => $users, 'roles' => $roles , 'nextUserId' => $nextUserId , 'rolesWithUsers'=>$rolesWithUsers] );
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
            'role' => ['required', 'exists:roles,role_id'],
            'status' => ['required', 'in:active,deactive'],
        ], [
            'user_name.required' => 'اسم المستخدم مطلوب.',
            'user_name.string' => 'اسم المستخدم يجب أن يكون نصاً.',
            'user_name.max' => 'اسم المستخدم يجب ألا يتجاوز 255 حرفاً.',
            
            'email.required' => 'البريد الإلكتروني مطلوب.',
            'email.string' => 'البريد الإلكتروني يجب أن يكون نصاً.',
            'email.email' => 'البريد الإلكتروني يجب أن يكون بتنسيق صحيح.',
            'email.max' => 'البريد الإلكتروني يجب ألا يتجاوز 255 حرفاً.',
            'email.unique' => 'البريد الإلكتروني موجود بالفعل.',
            
            'password.required' => 'كلمة المرور مطلوبة.',
            'password.string' => 'كلمة المرور يجب أن تكون نصاً.',
            'password.min' => 'كلمة المرور يجب أن تكون على الأقل 8 أحرف.',
            'password.confirmed' => 'تأكيد كلمة المرور غير متطابق.',
            
            'user_phone.required' => 'رقم الهاتف مطلوب.',
            'user_phone.string' => 'رقم الهاتف يجب أن يكون نصاً.',
            'user_phone.max' => 'رقم الهاتف يجب ألا يتجاوز 15 حرفاً.',
            
            'birth_date.date' => 'تاريخ الميلاد غير صالح.',
            'birth_date.before' => 'تاريخ الميلاد يجب أن يكون قبل اليوم.',
            
            'gender.in' => 'الجنس يجب أن يكون إما "ذكر" أو "أنثى".',
            
            'role.required' => 'الدور مطلوب.',
            'role.exists' => 'الدور المحدد غير موجود.',
            
            'status.required' => 'الحالة مطلوبة.',
            'status.in' => 'الحالة يجب أن تكون إما "نشط" أو "غير نشط".',
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
                'regex:/^(?!.*[<>_@\\\\\/]).*$/', // Disallow certain special characters
                'min:8' // At least 8 characters
            ],
            'email' => 'required|email|unique:users,email',
            'password' => [
                'required',
                'string',
                'min:8',  // At least 8 characters
                'regex:/[a-zA-Z]/',  // At least one letter (uppercase or lowercase)
                'regex:/[0-9]/'  // At least one number
            ],
            'password_confirmation' => 'required|same:password',  // Password confirmation match
            'user_phone' => 'required|regex:/^(01)[0-9]{9}$/',  // Match phone pattern (Egyptian phone number format)
            'birth_date' => 'nullable|date',
            'gender' => 'required|in:male,female',
            'role' => 'required|exists:roles,role_id',
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
    
        return redirect()->route('all_users')->with('success', 'تم تسجيل المستخدم بنجاح');
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
        $validatedData = $request->validate([
            'user_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'user_phone' => 'required|string|max:15',
            'gender' => 'required|in:male,female',
            'role' => 'required|exists:roles,role_id',
            'status' => 'required|in:active,deactive',
        ], [
            'user_name.required' => 'اسم المستخدم مطلوب.',
            'user_name.string' => 'اسم المستخدم يجب أن يكون نصاً.',
            'user_name.max' => 'اسم المستخدم يجب ألا يتجاوز 255 حرفاً.',
            
            'email.required' => 'البريد الإلكتروني مطلوب.',
            'email.email' => 'البريد الإلكتروني يجب أن يكون بتنسيق صحيح.',
            'email.max' => 'البريد الإلكتروني يجب ألا يتجاوز 255 حرفاً.',
            
            
            
            'user_phone.required' => 'رقم الهاتف مطلوب.',
            'user_phone.string' => 'رقم الهاتف يجب أن يكون نصاً.',
            'user_phone.max' => 'رقم الهاتف يجب ألا يتجاوز 15 حرفاً.',
            
            'gender.required' => 'الجنس مطلوب.',
            'gender.in' => 'الجنس يجب أن يكون إما "ذكر" أو "أنثى".',
            
            'role.required' => 'الدور مطلوب.',
            'role.exists' => 'الدور المحدد غير موجود.',
            
            'status.required' => 'الحالة مطلوبة.',
            'status.in' => 'الحالة يجب أن تكون إما "نشط" أو "غير نشط".',
        ]);
        
    
        // Update the user record in the database using the DB facade
        DB::table('users')->where('id', $id)->update([
            'name' => $validatedData['user_name'],
            'email' => $validatedData['email'],
            'mobile_number' => $validatedData['user_phone'],
            'gender' => $validatedData['gender'],
            'role' => $validatedData['role'],
            'status' => $validatedData['status'],
            'updated_at' => now(), // Optional: update the timestamp
        ]);
    
        return redirect()->back()->with('success', 'تم تعديل بيانات المستخدم بنجاح');
    }
    
    

    /**
     * Remove the specified resource from storage.
     */

    public function destroy(string $id)
    {
        if (!is_array($id)) {
            $id = [$id];
        }
        // Attempt to delete the user from the database
        $deleted = DB::table('users')
        ->whereIn('id', $id)
        ->update(['deleted_at' => now()]);;
    
        // Check if the deletion was successful
        if ($deleted) {
            return redirect()->route('all_users')->with('success', 'تم حذف المستخدم بنجاح.');
        } else {
            return redirect()->route('all_users')->with('error', 'هذا المستخدم غير موجود.');
        }
    }
    
}
