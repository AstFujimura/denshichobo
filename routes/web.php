<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TopController;
use App\Http\Controllers\RegistController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LogoutController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//  ログインページ
Route::get('login',[LoginController::class,'loginGet'])->name('loginGet');
// ログインページにポストで情報を投げた時
Route::post('login',[LoginController::class,'LoginPost'])->name('loginPost');

Route::group(['middleware' => 'auth'], function () {
// ログアウト時（すぐにリダイレクトされる）
Route::get('logout',[LogoutController::class,'Logout'])->name('logout');


// トップページ
Route::get('/',[TopController::class,'index'])->name('topGet');
// トップページ
Route::get('/search',[TopController::class,'search'])->name('searchGet');

Route::get('/download/{id}',[TopController::class,'download'])->name('download');


// 新規登録ページ
Route::get('regist',[RegistController::class,'registGet'])->name('registGet');
// 新規登録ページにポストで情報を投げた時
Route::post('regist',[RegistController::class,'registPost'])->name('registPost');



});