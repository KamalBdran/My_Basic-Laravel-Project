<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\order;
use App\Models\Product;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function createOrder(Request $request)
   
    {
        if (!Auth::user() || Auth::user()->role !== 'Phar') 
        {
            return response()->json
            (
            [
                'status' => false,
                'message' => 'You are not authorized to create orders.',
            ], 401);   
        }
   
        $validator = Validator::make($request->all(),
        [
            't_name' => 'required|string',
            'quantity' => 'required|integer|min:1',
            'product_id' => 'required|exists:products,id',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 400);
        }
    
        $product = Product::find($request->product_id);
        
        if (!$product) {
            return response()->json([
                'status' => false,
                'message' => 'Product not found.',
            ], 404);
        }
        
        if ($product->quantity < $request->quantity) {
            return response()->json(
            [
                'status' => false,
                'message' => 'Insufficient product quantity.',
            ], 400);
        }
        
        $order = Order::create(
        [
            't_name' => $request->t_name,
            'quantity' => $request->quantity,
            'product_id' => $request->product_id,
            'user_id' => Auth::id(),
        ]);
    
        $product->quantity -= $request->quantity;
        $product->save();

    
        return response()->json([
            'status' => true,
            'message' => 'Order created successfully',
            'data' => $order,
        ], 200);

    }
    public function getUserOrders()
    {
       
        if (!Auth::check() || Auth::user()->role !== 'Phar') {
            return response()->json([
                'status' => false,
                'message' => 'You are not authorized to view orders.',
            ], 401);
        }
        
        $userOrders = Order::where('user_id', Auth::id())->get();
        
        return response()->json([
            'status' => true,
            'message' => 'my orders retrieved successfully',
            'data' => $userOrders,
        ], 200);
    }
    public function getPharOrders()
{
    
    if (!Auth::check() || Auth::user()->role !== 'Owner') {
        return response()->json([
            'status' => false,
            'message' => 'You are not authorized to view orders.',
        ], 401);
    }
    
    
    $pharOrders = Order::query()->get();
    
    return response()->json([
        'status' => true,
        'message' => ' phar orders retrieved successfully',
        'data' => $pharOrders,
    ], 200);
}

public function updateOrderStatus(Request $request, $orderId)
{
    if (!Auth::check() || Auth::user()->role !== 'Owner') {
        return response()->json([
            'status' => false,
            'message' => 'You are not authorized to update order status.',
        ], 401);
    }
    
    $validator = Validator::make($request->all(), [
        'status' => 'required|in:preparing,sended,deliverd',
        'payment' => 'required|in:unpaid,paid',
    ]);
    
    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => 'Validation error',
            'errors' => $validator->errors(),
        ], 400);
    }
    $order = Order::find($orderId);
    
    if (!$order) {
        return response()->json([
            'status' => false,
            'message' => 'Order not found.',
        ], 404);
    }
    
    $order->status = $request->status;
    $order->payment = $request->payment;
    $order->save();
    
    return response()->json([
        'status' => true,
        'message' => 'Order status updated successfully',
        'data' => $order,
    ], 200);


    if ($order->status == 'sended')
    {
        $product = Product::find($order->product_id);
        
        if ($product) 
        {
            $product->quantity -= $order->quantity;
            $product->save();
        }
    }
}
}