<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TopController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CompanySearchController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\EditController;
use App\Http\Controllers\RegistController;
use App\Http\Controllers\DeleteController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\DriveTestController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\GraphController;
use App\Http\Controllers\DistanceController;
use App\Http\Controllers\CarController;
use App\Http\Controllers\ExcelController;

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

// // 管理者ページ
// // Route::get('adminchoice',[AdminController::class,'adminchoice'])->name('adminchoiceGet');
// Route::get('adminpage',[AdminController::class,'adminpage'])->name('adminpageGet');
// // Route::get('admintop',[AdminController::class,'admintop'])->name('admintopGet');
// Route::get('adminregist',[AdminController::class,'adminregist'])->name('adminregistGet');
// Route::get('admin/{id?}',[AdminController::class,'admindetail'])->name('admindetailGet');

// // 管理者ページ

// Route::post('admin',[AdminController::class,'usercreate'])->name('adminPost');

// Route::delete('admin/{id?}',[AdminController::class,'userdelete'])->name('adminDelete');

// Route::get('admin/edit/{id?}',[AdminController::class,'usereditGet'])->name('admineditGet');
// Route::put('admin/edit/{id?}',[AdminController::class,'useredit'])->name('adminPut');





// トップページ
Route::get('/',[TopController::class,'index'])->name('topGet');
// トップページ


// 走行距離
Route::get('/distance',[DistanceController::class,'index'])->name('distanceGet');

// 車両情報
Route::get('/car',[CarController::class,'index'])->name('carGet');


// // 会社一覧ページ
// Route::get('/company',[CompanyController::class,'index'])->name('companyGet');
// // 会社詳細ページ
// Route::get('/company/{id?}',[CompanyController::class,'detail'])->name('companydetailGet');

// // 会社登録ページ  ※却下


// // 会社登録ページ
// Route::post('/companyregist',[CompanyController::class,'companycreate'])->name('companyregistPost');
// // 会社編集ページ
// Route::get('company/edit/{id?}',[CompanyController::class,'companyeditGet'])->name('companyeditGet');
// // 会社編集ページ
// Route::put('company/edit/{id?}',[CompanyController::class,'companyeditPut'])->name('companyeditPut');
// // 会社削除ページ
// Route::delete('company/{id?}',[CompanyController::class,'companydelete'])->name('companydelete');
// // トップページから検索やソートボタンを押した時
// Route::get('companysearch',[CompanySearchController::class,'search'])->name('companysearch');


// // 情報詳細ページ
// Route::get('top/{id?}',[TopController::class,'detail'])->name('detail');
// //ハイパーリンクの設定
// Route::post('urlsearch',[TopController::class,'urlsearch'])->name('urlsearch');
// 新規登録ページ
Route::get('regist',[RegistController::class,'registGet'])->name('registGet');
// 新規登録ページにポストで情報を投げた時
Route::post('regist',[RegistController::class,'registPost'])->name('registPost');

// // 編集ページ
// Route::get('edit/{id?}',[EditController::class,'editGet'])->name('editGet');
// // 編集ページにポストで情報を投げた時
// Route::post('edit',[EditController::class,'editPost'])->name('editPost');

// // トップページから検索やソートボタンを押した時
// Route::get('search',[SearchController::class,'search'])->name('search');
// //　詳細ページから削除ボタンをを押したとき（すぐにTopにリダイレクトされる）
// Route::post('delete',[DeleteController::class,'delete'])->name('delete');

Route::get('test',[TestController::class,'test'])->name('test');
Route::post('test',[TestController::class,'testpost'])->name('testpost');
Route::get('test2',[DriveTestController::class,'test2'])->name('test2');
Route::post('test2',[DriveTestController::class,'testpost2'])->name('testpost2');


Route::get('graph',[GraphController::class,'graph'])->name('graph');


Route::get('excel/{year}/{month}',[ExcelController::class,'excel'])->name('excel');




});