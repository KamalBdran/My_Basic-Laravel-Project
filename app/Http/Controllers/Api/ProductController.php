<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function createProduct(Request $request)
    {
        $validator = Validator::make($request->all(),
         [
            't_name' => 'required',
            's_name' => 'required',
            'category' => 'required',
            'company_name' => 'required',
            'quantity' => 'required|numeric',
            'price' => 'required|numeric',
            'exp_date' => 'required|date',
            'image'=> 'required'
        ]);
        $imagePath = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = Str::random(20) . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('images', $imageName, 'public');
        }
        if ($validator->fails())
        {
            return response()->json(
            [
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 400);
        }
    
        $user = Auth::user();
    
        if ($user->role != 'Owner') 
        {
            return response()->json(
            [
                'status' => false,
                'message' => 'Unauthorized access',
            ], 401);
        }
    
        $product = Product::create
        (
            [
            't_name' => $request->t_name,
            's_name' => $request->s_name,
            'category' => $request->category,
            'company_name' => $request->company_name,
            'quantity' => $request->quantity,
            'price' => $request->price,
            'exp_date' => $request->exp_date, 
            'image' => $imagePath    
            ]
        );
    
        return response()->json([
            'status' => true,
            'message' => 'Product created successfully',
            'data' => $product,
        ], 201);
    }
    public function getProductsByCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category' => 'required',
        ]);
        if ($validator->fails()) 
        {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 400);
        }
        $category = $request->category;
        $user = Auth::user();
        if ($user->role !== 'Phar') 
        {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized access',
            ], 401);
        }
        $products = Product::where('category', $category)->get();
        return response()->json(
        [
            'status' => true,
            'message' => 'Products retrieved successfully',
            'data' => $products,
        ], 200);
    }
    
     public function updateProduct(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            't_name' => 'required',
            's_name' => 'required',
            'category' => 'required',
            'company_name' => 'required',
            'quantity' => 'required|numeric',
            'price' => 'required|numeric',
            'exp_date' => 'required|date',
            'image'=> 'required',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 400);
        }
    
        $user = Auth::user();
    
        if ($user->role !== 'Owner') {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized access',
            ], 401);
        }
    
        $product = Product::find($id);
    
        if (!$product) {
            return response()->json([
                'status' => false,
                'message' => 'Product not found',
            ], 404);
        }
    
        $imagePath = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = Str::random(20) . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('images', $imageName, 'public');
        }
    
        $product->t_name = $request->t_name;
        $product->s_name = $request->s_name;
        $product->category = $request->category;
        $product->company_name = $request->company_name;
        $product->quantity = $request->quantity;
        $product->price = $request->price;
        $product->exp_date = $request->exp_date;
        $product->image = $imagePath;
    
        $product->save();
    
        return response()->json([
            'status' => true,
            'message' => 'Product updated successfully',
            'data' => $product,
        ], 200);
    }
    public function getAllProducts()
    {
        try {
            if (Auth::user()->role !== 'Owner') {
                return response()->json([
                    'status' => false,
                    'message' => 'Only the Owner can get all products',
                ], 403);
            }
    
            $products = Product::all();
    
            return response()->json([
                'status' => true,
                'message' => 'Products Retrieved Successfully',
                'data' => $products
            ], 200);
        }
         catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    public function deleteProduct($id)
{
    $user = Auth::user();

    if ($user->role !== 'Owner') {
        return response()->json([
            'status' => false,
            'message' => 'Unauthorized access',
        ], 401);
    }

    $product = Product::find($id);

    if (!$product) {
        return response()->json([
            'status' => false,
            'message' => 'Product not found',
        ], 404);
    }

    $product->delete();

    return response()->json([
        'status' => true,
        'message' => 'Product deleted successfully',
    ], 200);
}
}