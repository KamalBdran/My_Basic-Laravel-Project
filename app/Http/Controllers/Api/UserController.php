<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\UserDetails;
use App\Models\Education;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Create User
     * @param Request $request
     * @return User 
     */
    public function registerUser(Request $request)
    {
        try {
            $validateUser = Validator::make($request->all(), [
                'first_name' => 'required',
                'last_name' => 'required',
                'password' => 'required',
                'role' => 'required',
            ]);
    
            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }
    
            $imagePath = null;
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = Str::random(20) . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('images', $imageName, 'public');
            }
    
            $userData = [
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'image' => $imagePath,
            ];
    
            if ($request->has('email')) {
                $validateUserEmail = Validator::make($request->all(), [
                    'email' => 'required|email|unique:users,email',
                ]);
    
                if ($validateUserEmail->fails()) {
                    return response()->json([
                        'status' => false,
                        'message' => 'validation error',
                        'errors' => $validateUserEmail->errors()
                    ], 401);
                }
    
                $userData['email'] = $request->email;
            } elseif ($request->has('phone_num')) {
                $validateUserPhone = Validator::make($request->all(), [
                    'phone_num' => 'required|unique:users',
                ]);
    
                if ($validateUserPhone->fails()) {
                    return response()->json([
                        'status' => false,
                        'message' => 'validation error',
                        'errors' => $validateUserPhone->errors()
                    ], 401);
                }
    
                $userData['phone_num'] = $request->phone_num;
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Either email or phone number is required'
                ], 400);
            }
    
            $user = User::create($userData);
    
            return response()->json([
                'status' => true,
                'message' => 'User Registerd Successfully',
                'token' => $user->createToken("API TOKEN")->plainTextToken
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    } 
    public function loginUser(Request $request)
    {
        try {
            $validateUser = Validator::make($request->all(), [
                'password' => 'required',
            ]);
    
            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }
    
            $credentials = $request->only('password');
            $credentialValue = $request->input('credential');
    
            $user = User::where(function ($query) use ($credentialValue) {
                $query->where('email', $credentialValue)
                    ->orWhere('phone_num', $credentialValue);
            })->first();
    
            if (!$user || !Hash::check($credentials['password'], $user->password)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid login credentials'
                ], 401);
            }
    
            return response()->json([
                'status' => true,
                'message' => 'User Logged in Successfully',
                'token' => $user->createToken("API TOKEN")->plainTextToken
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function getUserInformation($id)
    {
        try {
            $user = User::find($id);
    
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found',
                ], 404);
            }
    
            return response()->json([
                'status' => true,
                'data' => $user,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

public function searchUserById($id)
{
    $user = User::find($id);

    if (!$user) {
        return response()->json([
            'status' => false,
            'message' => 'User not found',
        ], 404);
    }

    return response()->json([
        'status' => true,
        'data' => $user,
    ], 200);
}
public function searchProduct(Request $request)
{
    $validator = Validator::make($request->all(), [
        'search' => 'required',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => 'Validation error',
            'errors' => $validator->errors(),
        ], 400);
    }

    $search = $request->search;
    $products = Product::where('t_name', 'like', "%{$search}%")
        ->orWhere('category', 'like', "%{$search}%")
        ->get();

    return response()->json([
        'status' => true,
        'message' => 'Products retrieved successfully',
        'data' => $products,
    ], 200);
}
       

    public function viewProduct($productId)
{
    $product = Product::find($productId);

    if (!$product) {
        return response()->json([
            'status' => false,
            'message' => 'Product not found',
        ], 404);
    }

    return response()->json([
        'status' => true,
        'data' => $product,
    ], 200);
}
   
public function createUser(Request $request)
{
    try {
        if (Auth::user()->role !== 'Owner') {
            return response()->json([
                'status' => false,
                'message' => 'Only the Owner can create users',
            ], 403);
        }

        $user = new User();
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->phone_num = $request->phone_num;
        $user->role = $request->role;

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = Str::random(20) . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('images', $imageName, 'public');
            $user->image = $imagePath;
        }

        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'User Created Successfully',
            'data' => $user
        ], 200);
    } catch (\Throwable $th) {
        return response()->json([
            'status' => false,
            'message' => $th->getMessage()
        ], 500);
    }
}

public function updateUser(Request $request, $id)
{
    try {
        if (Auth::user()->role !== 'Owner') {
            return response()->json([
                'status' => false,
                'message' => 'Only the Owner can update users',
            ], 403);
        }

        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found',
            ], 404);
        }

        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->phone_num = $request->phone_num;
        $user->role = $request->role;

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = Str::random(20) . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('images', $imageName, 'public');
            $user->image = $imagePath;
        }

        $user->save();
        return response()->json([
            'status' => true,
            'message' => 'User Updated Successfully',
            'data' => $user
        ], 200);
    } catch (\Throwable $th) {
        return response()->json([
            'status' => false,
            'message' => $th->getMessage()
        ], 500);
    }
}

public function getAllUsers()
{
    try {
        if (Auth::user()->role !== 'Owner') {
            return response()->json([
                'status' => false,
                'message' => 'Only the Owner can get all users',
            ], 403);
        }

        $users = User::all();

        return response()->json([
            'status' => true,
            'message' => 'Users Retrieved Successfully',
            'data' => $users
        ], 200);
    }
     catch (\Throwable $th) {
        return response()->json([
            'status' => false,
            'message' => $th->getMessage()
        ], 500);
    }
}

public function deleteUser($id)
{
    try {
        if (Auth::user()->role !== 'Owner') {
            return response()->json([
                'status' => false,
                'message' => 'Only the Owner can delete users',
            ], 403);
        }

        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found',
            ], 404);
        }

        $user->delete();

        return response()->json([
            'status' => true,
            'message' => 'User Deleted Successfully',
        ], 200);

    } 
    catch (\Throwable $th) {
        return response()->json([
            'status' => false,
            'message' => $th->getMessage()
        ], 500);
    }
}
}
