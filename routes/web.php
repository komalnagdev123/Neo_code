<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NeoController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/',[NeoController::class, 'dateRange']);
Route::post('/',[NeoController::class, 'getApiData'])->name('fetch-neo-stats');
