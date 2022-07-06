<?php

use App\Http\Controllers\PaymentController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Stripe\Charge;
use Stripe\Dispute;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/callback', function (Request $request) {


    //    $response = Http::asForm()->post('https://localhost:8000/oauth/token', [
    //        'grant_type' => 'password',
    //        'client_id' => 2,
    //        'client_secret' => 'c6SI88CTwfCZMyjYP7VOBmMuJMmleHe2wWVAkQrO',
    //        'username' => 'TestPerson',
    //        'password' => 'admin',
    //        'scope' => '',
    //    ]);

    //    return $response->json();

    $user = User::find(1);

    // Creating a token without scopes...
    $token = $user->createToken('MyToke')->accessToken;

    return json_encode([
        'token' => $token
    ]);
});

Route::middleware('auth:api')->group(function () {

    Route::apiResource('payment', PaymentController::class);
});


