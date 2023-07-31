<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TopController;
use App\Http\Controllers\RegistController;
use App\Http\Controllers\EditController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ErrorController;

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
Route::get('/search',[TopController::class,'search'])->name('searchPost');

Route::get('/download/{id}',[TopController::class,'download'])->name('download');

Route::get('/detail/{id}',[TopController::class,'detail'])->name('detail');

Route::get('/history/{id}',[TopController::class,'history'])->name('history');

Route::get('/img/{id}',[TopController::class,'imgget'])->name('imgget');

Route::get('/usersetting',[TopController::class,'usersettingGet'])->name('usersettingGet');

Route::post('/usersetting',[TopController::class,'usersettingPost'])->name('usersettingPost');


// 新規登録ページ
Route::get('/regist',[RegistController::class,'registGet'])->name('registGet');
// 新規登録ページにポストで情報を投げた時
Route::post('/regist',[RegistController::class,'registPost'])->name('registPost');

// 変更ページ
Route::get('/edit/{path}',[EditController::class,'editGet'])->name('editGet');
// 変更ページにポストで情報を投げた時
Route::post('/edit/{path}',[EditController::class,'editPost'])->name('editPost');

// 変更ページにポストで情報を投げた時
Route::get('/delete/{path}',[EditController::class,'deleteGet'])->name('deleteGet');

// 変更ページにポストで情報を投げた時
Route::get('/test/{num}',[TestController::class,'testGet'])->name('testGet');

// 変更ページにポストで情報を投げた時
Route::get('/admin',[AdminController::class,'adminGet'])->name('adminGet');

// 変更ページにポストで情報を投げた時
Route::get('/admin/edit/{id}',[AdminController::class,'admineditGet'])->name('admineditGet');

// 変更ページにポストで情報を投げた時
Route::get('/admin/regist',[AdminController::class,'adminregistGet'])->name('adminregistGet');

// 変更ページにポストで情報を投げた時
Route::post('/admin/regist',[AdminController::class,'adminregistPost'])->name('adminregistPost');

// 変更ページにポストで情報を投げた時
Route::put('/admin/edit/{id}',[AdminController::class,'admineditPut'])->name('admineditPut');

// 変更ページにポストで情報を投げた時
Route::delete('/admin/delete/{id}',[AdminController::class,'adminDelete'])->name('adminDelete');

// 変更ページにポストで情報を投げた時
Route::post('/admin/reset/{id}',[AdminController::class,'adminresetPost'])->name('adminresetPost');

Route::get('error/{code}',[ErrorController::class,'errorGet'])->name('errorGet');
});

