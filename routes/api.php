<?php

use App\Http\Controllers\ApiClientAuthController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\SessionIdentifierController;
use App\Http\Controllers\NotificationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Carbon\Carbon;
use App\Http\Controllers\Api\RawController as Raw;
use App\Models\SessionIdentifier;
use Illuminate\Support\Str;

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

// Auth routes
Route::controller(ApiClientAuthController::class)->prefix('auth')->group(function () {
    Route::post('/login', 'clientLogin');
    Route::post('/register', 'clientRegister');
    Route::post('forgot-password', 'resetPassword');
    Route::post('/change-password', 'changePassword');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/get-user', function (Request $request) {
            $user = auth('sanctum')->user();

            if (!$user) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }
            
            // Check for existing notification identifier
            $identifier = SessionIdentifier::where('user_id', $user->id)->first();
            
            if (!$identifier) {
                // Create a temporary identifier
                $tempIdentifier = Str::uuid()->toString();
                SessionIdentifier::create([
                    'user_id' => $user->id,
                    'session_id' => $tempIdentifier,
                ]);
                $identifier = SessionIdentifier::where('user_id', $user->id)->first();
            }
            
            $user->dob = Carbon::parse($user->dob)->format('d-m-Y');
            return response()->json([
                'user' => $user,
                'notificationId' => $identifier->session_id,
            ]);
        });
        Route::get('/get-user-data', function () {
            $user = auth('sanctum')->user();
            $user->dob = Carbon::parse($user->dob)->format('d-m-Y');
            return response()->json([
                'user' => $user,
            ]);
        });
        Route::patch('/update-user', 'updateUser');
    });
});

// Order routes
Route::controller(OrderController::class)->prefix('orders')->group(function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/get-my-orders', function () {
            $orders = auth('sanctum')->user()->orders;
            $orders->each(function ($order) {
                $order->created = Carbon::parse($order->created_at)->format('d.m.Y H:i');
                $order->status_text = \App\Models\Order::GET_STATUS[$order->status];
            });
            return response()->json($orders);
        });
        Route::get('/get-my-coins', function () {
            return response()->json(auth('sanctum')->user()->coins);
        });
    });
    Route::post('create', 'createOrder');
    Route::post('update', 'updateOrder');
});

// Session Identifier routes
Route::controller(SessionIdentifierController::class)->group(function () {
    Route::post('/session-identifiers', 'create');
    Route::post('/session-identifiers/public', 'createPublicSession');
    Route::post('/session-identifiers/assign-user', 'assignUserId');
    Route::post('/session-identifiers/update-user-id', 'updateUserId')->middleware('auth:sanctum');
});

// Notification routes
Route::controller(NotificationController::class)->group(function () {
    Route::get('/notifications', 'index');
    Route::patch('/notifications/{notification}/read', 'markAsRead');
    Route::post('/notifications/broadcast', 'createForAllUsers');
});

// Raw routes
Route::controller(Raw::class)->group(function () {
    Route::get('/get-routes', 'getLinks');
    Route::get('/get-company', 'getCompany');
    Route::get('/get-shedule', 'getShedule');
});

// Personal notification route
Route::post('/personal-notification', function(Request $request) {
    
    $request->validate([
        'message' => 'required|string',
    ]);

    $message = $request->input('message');

    Notification::sendNotification($message);

    return response()->json([
        'new_message' => true,
    ]);
});