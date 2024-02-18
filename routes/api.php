<?php
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\EducationController;
use App\Http\Controllers\Api\OrderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

 Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

//product routes    
    Route::post('/user/addproducts', [ProductController::class,'createProduct']);
    Route::post('/user/editproduct/{id}', [ProductController::class,'updateProduct']);
    Route::delete('/user/deleteproduct/{id}', [ProductController::class,'deleteProduct']);
    Route::get('/user/getallproducts', [ProductController::class, 'getAllProducts']);
    Route::get('/user/getproducts/{category}', [ProductController::class,'getProductsByCategory']);
//user routes
    Route::post('/user/createuser', [UserController::class, 'createUser']);
    Route::post('/user/updateuser/{id}', [UserController::class, 'updateUser']);
    Route::get('/user/getallusers', [UserController::class, 'getAllUsers']);
    Route::delete('/user/deleteuser/{id}', [UserController::class, 'deleteUser']);
    Route::get('/user/searchbyname/{query}', [UserController::class,'searchUserByName']);
    Route::get('/user/searchbyid/{id}', [UserController::class,'searchUserById']);
    Route::get('/user/search-product', [UserController::class,'searchProduct']);
    Route::get('/user/products/{productId}', [UserController::class,'viewProduct']);
    Route::get('/user/getuserinfo/{id}', [UserController::class, 'getUserInformation']);
    
    //order routes
    Route::post('/user/addorders', [OrderController::class,'createOrder']);
    Route::get('/user/getmyorders', [OrderController::class,'getUserOrders']);
    Route::get('/user/getpharorders', [OrderController::class,'getPharOrders']);
    Route::post('/user/updateorders/{orderId}', [OrderController::class,'updateOrderStatus']);

});
//auth routes
Route::post('/auth/register', [UserController::class, 'registerUser']);
Route::post('/auth/login', [UserController::class, 'loginUser']);
Route::post('/auth/logout', [UserController::class, 'logoutUser']);

