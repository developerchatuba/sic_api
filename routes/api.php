<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\GrupoController;
use App\Http\Controllers\Api\Relatorios\ComissaoVendedorController;
use App\Http\Controllers\API\UserController;
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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group([

    'middleware' => 'api'

], function ($router) {

    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/me', [AuthController::class, 'me']);

    

});

Route::middleware('api')->group(function() {
    Route::resource('usuarios', UserController::class);
    Route::resource('grupos', GrupoController::class);

    Route::post('/relComissaoVendedor', [ComissaoVendedorController::class, 'relatorioComissaoVendedor']);
});

/* Route::post('/login', function(Request $request){
    $credentials = $request->only(['usuario', 'password']);

    if(!$token = auth()->attempt($credentials)){
        abort(401, 'Unauthorized');
    }

    return response()->json([
        'data' => [
            'token'=> $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]
        ]);
}); */

