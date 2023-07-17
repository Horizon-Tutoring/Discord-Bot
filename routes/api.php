<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Discord\Discord;


Route::get('/usersdw', function () {
    $discord = app(Discord::class);

    // Retrieve list of users
    $users = $discord->guilds->first()->members;

    // Return JSON response
    return response()->json($users);
});

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
