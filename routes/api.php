<?php

use App\Http\Controllers\Api\AgencyController;
use App\Http\Controllers\Api\AmenityController;
use App\Http\Controllers\Api\AreaController;
use App\Http\Controllers\Api\CityController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\CountryController;
use App\Http\Controllers\Api\PropertyController;
use App\Http\Controllers\Api\PropertyFloorPlanController;
use App\Http\Controllers\Api\PropertyInquiryController;
use App\Http\Controllers\Api\PropertyMediaController;
use App\Http\Controllers\Api\PropertyStatusController;
use App\Http\Controllers\Api\PropertyTypeController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\ScheduleController;
use App\Http\Controllers\Api\StateController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CompareController;
use App\Http\Controllers\Api\FeatureController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\SubscriptionPlanController;
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

Route::prefix('v1')->group(function () {
    // Public routes
    Route::post('contact', [ContactController::class, 'store']);
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{category}', [CategoryController::class, 'show']);

    // Location routes
    Route::apiResource('countries', CountryController::class);
    Route::apiResource('countries.states', StateController::class);
    Route::apiResource('states.cities', CityController::class);
    Route::apiResource('cities.areas', AreaController::class);

    // Property related public routes
    Route::get('properties', [PropertyController::class, 'index']);
    Route::get('properties/{property}', [PropertyController::class, 'show']);
    Route::get('property-types', [PropertyTypeController::class, 'index']);
    Route::get('property-statuses', [PropertyStatusController::class, 'index']);
    Route::get('amenities', [AmenityController::class, 'index']);
    Route::get('agents', [UserController::class, 'agents']);
    Route::get('features', [FeatureController::class, 'index']);

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        // User routes
        Route::apiResource('users', UserController::class);
        Route::get('users/{user}/properties', [UserController::class, 'properties']);
        Route::get('users/{user}/favorites', [UserController::class, 'userFavorites']);

        // Agency routes
        Route::apiResource('agencies', AgencyController::class);
        Route::get('agencies/{agency}/agents', [AgencyController::class, 'agents']);

        // Property management routes
        Route::post('properties', [PropertyController::class, 'store']);
        Route::post('properties/{property}', [PropertyController::class, 'update']);
        Route::delete('properties/{property}', [PropertyController::class, 'destroy']);

        // Property interactions
        Route::post('properties/{property}/favorite', [PropertyController::class, 'toggleFavorite']);
        Route::post('properties/{property}/compare', [PropertyController::class, 'toggleCompare']);

        // Property media routes
        Route::get('properties/{property}/media', [PropertyMediaController::class, 'index']);
        Route::post('properties/{property}/media', [PropertyMediaController::class, 'store']);
        Route::put('properties/{property}/media/{media}', [PropertyMediaController::class, 'update']);
        Route::delete('properties/{property}/media/{media}', [PropertyMediaController::class, 'destroy']);
        Route::post('properties/{property}/media/reorder', [PropertyMediaController::class, 'reorder']);

        // Property floor plans
        Route::apiResource('properties.floor-plans', PropertyFloorPlanController::class);
        Route::post('properties/{property}/floor-plans/{floor_plan}', [PropertyFloorPlanController::class, 'store']);


        // Reviews
        Route::apiResource('properties.reviews', ReviewController::class);
        Route::patch(
            'properties/{property}/reviews/{review}/status',
            [ReviewController::class, 'updateStatus']
        );

        // Property inquiries
        Route::apiResource('properties.inquiries', PropertyInquiryController::class);
        Route::patch(
            'properties/{property}/inquiries/{inquiry}/status',
            [PropertyInquiryController::class, 'updateStatus']
        );

        // Schedules
        Route::apiResource('properties.schedules', ScheduleController::class);
        Route::patch(
            'properties/{property}/schedules/{schedule}/status',
            [ScheduleController::class, 'updateStatus']
        );

        // Subscription routes
        Route::get('subscription-plans', [SubscriptionPlanController::class, 'index']);
        Route::get('my/subscriptions', [SubscriptionController::class, 'index']);
        Route::post('subscriptions', [SubscriptionController::class, 'store']);
        Route::get('subscriptions/{subscription}', [SubscriptionController::class, 'show']);
        Route::post('subscriptions/{subscription}/cancel', [SubscriptionController::class, 'cancel']);

        // Admin only routes
        Route::middleware('can:admin')->group(function () {
            Route::apiResource('property-types', PropertyTypeController::class)
                ->except(['index']);
            Route::apiResource('property-statuses', PropertyStatusController::class)
                ->except(['index']);
            Route::apiResource('amenities', AmenityController::class)->except(['index']);

            // Contact management
            Route::get('contacts', [ContactController::class, 'index']);
            Route::get('contacts/{contact}', [ContactController::class, 'show']);
            Route::patch('contacts/{contact}/status', [ContactController::class, 'updateStatus']);
            Route::delete('contacts/{contact}', [ContactController::class, 'destroy']);

            // Admin subscription plan management
            Route::prefix('subscription-plans')->group(function () {
                Route::get('/', [SubscriptionPlanController::class, 'index']);
                Route::post('/', [SubscriptionPlanController::class, 'store']);
                Route::get('/{subscriptionPlan}', [SubscriptionPlanController::class, 'show']);
                Route::put('/{subscriptionPlan}', [SubscriptionPlanController::class, 'update']);
                Route::delete('/{subscriptionPlan}', [SubscriptionPlanController::class, 'destroy']);
            });
        });

        // Categories
        Route::post('/categories', [CategoryController::class, 'store']);
        Route::put('/categories/{category}', [CategoryController::class, 'update']);
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

        // Property Inquiries
        Route::apiResource('property-inquiries', PropertyInquiryController::class);

        // Compares
        Route::apiResource('compares', CompareController::class);

        // Contacts
        Route::apiResource('contacts', ContactController::class);

        // Features
        Route::apiResource('features', FeatureController::class);

        // User favorites
        Route::get('my/favorites', [UserController::class, 'myFavorites']);
        Route::post('my/favorites/{property}', [UserController::class, 'addFavorite']);
        Route::delete('my/favorites/{property}', [UserController::class, 'removeFavorite']);

        // User compares
        Route::get('my/compares', [UserController::class, 'myCompares']);
        Route::post('my/compares/{property}', [UserController::class, 'addCompare']);
        Route::delete('my/compares/{property}', [UserController::class, 'removeCompare']);

        // Compare routes
        Route::get('my/compared-properties', [UserController::class, 'getComparedProperties']);
    });
    require __DIR__ . '/auth.php';
});
