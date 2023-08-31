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
use App\Http\Middleware\CheckSessionTimeout;
use Illuminate\Support\Facades\Config;

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


$prefix = config('prefix.prefix');
Route::prefix($prefix)->group(function () {

//  ログインページ
Route::get('login',[LoginController::class,'loginGet'])->name('loginGet');
// ログインページにポストで情報を投げた時
Route::post('login',[LoginController::class,'LoginPost'])->name('loginPost');
// javascriptが無効の場合
Route::get('/jserror',[ErrorController::class,'jserrorGet'])->name('jserrorGet');

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

Route::get('/torihikisaki',[TopController::class,'torihikisearch'])->name('torihikisearch');

// 新規登録ページ
Route::get('/regist',[RegistController::class,'registGet'])->name('registGet');
// 新規登録ページにポストで情報を投げた時
Route::post('/regist',[RegistController::class,'registPost'])->name('registPost');

Route::get('/objectURL',[RegistController::class,'registURl'])->name('registURl');


// 変更ページ
Route::get('/edit/{path}',[EditController::class,'editGet'])->name('editGet');
// 変更ページにポストで情報を投げた時
Route::post('/edit/{path}',[EditController::class,'editPost'])->name('editPost');

// 削除
Route::get('/delete/{path}',[EditController::class,'deleteGet'])->name('deleteGet');

// numの数だけダミーデータを登録するとき
Route::get('/test/{num}',[TestController::class,'testGet'])->name('testGet');

// 管理者ページトップ（ユーザー一覧）
Route::get('/admin',[AdminController::class,'adminGet'])->name('adminGet');

// ユーザー変更画面
Route::get('/admin/edit/{id}',[AdminController::class,'admineditGet'])->name('admineditGet');

// ユーザー登録画面
Route::get('/admin/regist',[AdminController::class,'adminregistGet'])->name('adminregistGet');

// ユーザー登録ページにポストで情報を投げた時
Route::post('/admin/regist',[AdminController::class,'adminregistPost'])->name('adminregistPost');

// ユーザー変更ページにポストで情報を投げた時
Route::put('/admin/edit/{id}',[AdminController::class,'admineditPut'])->name('admineditPut');

// 変更ページにポストで情報を投げた時
Route::delete('/admin/delete/{id}',[AdminController::class,'adminDelete'])->name('adminDelete');

// パスワードリセットをポストで情報を投げた時
Route::post('/admin/reset/{id}',[AdminController::class,'adminresetPost'])->name('adminresetPost');

//管理ユーザーを変更しても大丈夫か確認するとき
Route::get('/admincheck/{id}',[AdminController::class,'admincheck'])->name('admincheck');

//書類管理ページ
Route::get('/admin/document',[AdminController::class,'admindocumentGet'])->name('admindocumentGet');

//書類を削除しても大丈夫か確認するとき
Route::get('/admin/documentcheck/{id}',[AdminController::class,'documentcheck'])->name('documentcheck');

// 書類管理ページに変更をポストするとき
Route::post('/admin/document',[AdminController::class,'admindocumentPost'])->name('admindocumentPost');

Route::get('/error/{code}',[ErrorController::class,'errorGet'])->name('errorGet');

Route::get('/usercheck',[TopController::class,'usercheck'])->name('usercheck');
});

});