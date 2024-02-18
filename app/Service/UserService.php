<?php

namespace App\Service;

use App\Models\User;
use App\Models\UserDetails;
use App\Models\Education;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserService
{
    /**
     * Create User
     * @param array $data
     * @return User 
     */
    public function registerUser(array $data)
    {
        try {
            $validateUser = Validator::make($data, [
                'first_name' => 'required',
                'last_name' => 'required',
                'password' => 'required',
                'role' => 'required',
            ]);
    
            if ($validateUser->fails()) {
                return [
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ];
            }
    
            $imagePath = null;
            if (isset($data['image'])) {
                $image = $data['image'];
                $imageName = Str::random(20) . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('images', $imageName, 'public');
            }
    
            $userData = [
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'password' => Hash::make($data['password']),
                'role' => $data['role'],
                'image' => $imagePath,
            ];
    
            if (isset($data['email'])) {
                $validateUserEmail = Validator::make($data, [
                    'email' => 'required|email|unique:users,email',
                ]);
    
                if ($validateUserEmail->fails()) {
                    return [
                        'status' => false,
                        'message' => 'validation error',
                        'errors' => $validateUserEmail->errors()
                    ];
                }
    
                $userData['email'] = $data['email'];
            } elseif (isset($data['phone_num'])) {
                $validateUserPhone = Validator::make($data, [
                    'phone_num' => 'required|unique:users',
                ]);
    
                if ($validateUserPhone->fails()) {
                    return [
                        'status' => false,
                        'message' => 'validation error',
                        'errors' => $validateUserPhone->errors()
                    ];
                }
    
                $userData['phone_num'] = $data['phone_num'];
            } else {
                return [
                    'status' => false,
                    'message' => 'Either email or phone number is required'
                ];
            }
    
            $user = User::create($userData);
    
            return [
                'status' => true,
                'message' => 'User Registered Successfully',
                'token' => $user->createToken("API TOKEN")->plainTextToken
            ];
        } catch (\Throwable $th) {
            return [
                'status' => false,
                'message' => $th->getMessage()
            ];
        }
    }
}